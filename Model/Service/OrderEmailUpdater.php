<?php
/**
 * Copyright (c) 2026 BluePrint3D Ltd. All rights reserved.
 *
 * This software is provided free of charge for personal or commercial use.
 * Resale, redistribution, or sublicensing of this source code, modified or
 * unmodified, for direct financial gain is strictly prohibited.
 *
 * @author    BluePrint3D Ltd <support@blueprint3d.dev>
 * @copyright 2026 BluePrint3D Ltd (Company No. 13473806)
 * @license   Custom Proprietary EULA (See LICENSE.txt)
 */
declare(strict_types=1);

namespace BluePrint3D\ChangeOrderEmail\Model\Service;

use BluePrint3D\ChangeOrderEmail\Model\ResourceModel\OrderGridUpdater;
use BluePrint3D\ChangeOrderEmail\Model\ResourceModel\OrderStatusGuard;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderEmailUpdater
{
    private const SESSION_KEY = 'change_order_email_pending'; // keyed by orderId

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param SessionManagerInterface $session
     * @param OrderGridUpdater $orderGridUpdater
     * @param OrderStatusGuard $orderStatusGuard
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly CustomerCollectionFactory $customerCollectionFactory,
        private readonly SessionManagerInterface $session,
        private readonly OrderGridUpdater $orderGridUpdater,
        private readonly OrderStatusGuard $orderStatusGuard
    ) {
    }

    /**
     * Attempt to change the order's customer email, updating the linked customer where safe.
     *
     * @param int $orderId
     * @param string $newEmail
     * @param string $adminUser
     * @return array
     */
    public function attemptChange(int $orderId, string $newEmail, string $adminUser): array
    {
        $order = $this->orderRepository->get($orderId);
        $oldEmail = (string)$order->getCustomerEmail();
        $customerId = (int)$order->getCustomerId();
        $originalState = (string)$order->getState();
        $originalStatus = (string)$order->getStatus();

        if (strcasecmp($oldEmail, $newEmail) === 0) {
            return ['success' => false, 'message' => __('New email is the same as the current email.')];
        }

        // Guest order: update order only
        if ($customerId === 0) {
            $this->applyOrderEmail(
                $order,
                $newEmail,
                $adminUser,
                $oldEmail,
                null,
                null,
                $originalState,
                $originalStatus
            );
            return ['success' => true];
        }

        $currentCustomer = $this->customerRepository->getById($customerId);
        $targetCustomerId = $this->findCustomerIdByEmail($newEmail);

        // If unused, or belongs to same customer -> update customer + order
        if ($targetCustomerId === null || $targetCustomerId === (int)$currentCustomer->getId()) {
            $currentCustomer->setEmail($newEmail);
            $this->customerRepository->save($currentCustomer);

            $this->applyOrderEmail(
                $order,
                $newEmail,
                $adminUser,
                $oldEmail,
                $customerId,
                $customerId,
                $originalState,
                $originalStatus
            );
            return ['success' => true];
        }

        // Belongs to a different customer -> require confirm
        $pending = (array)$this->session->getData(self::SESSION_KEY);
        $pending[$orderId] = [
            'new_email' => $newEmail,
            'target_customer_id' => $targetCustomerId,
            'old_email' => $oldEmail,
            'old_customer_id' => $customerId
        ];
        $this->session->setData(self::SESSION_KEY, $pending);

        return [
            'success' => false,
            'needs_confirm' => true,
            'message' => __(
                'The email %1 already belongs to customer ID %2. '
                . 'Confirm it is the same customer to move this order, or abort.',
                $newEmail,
                (string)$targetCustomerId
            )
        ];
    }

    /**
     * Apply a previously confirmed email change, moving the order to the target customer.
     *
     * @param int $orderId
     * @param string $adminUser
     * @return array
     */
    public function confirmMove(int $orderId, string $adminUser): array
    {
        $pending = (array)$this->session->getData(self::SESSION_KEY);
        if (empty($pending[$orderId])) {
            return ['success' => false, 'message' => __('No pending email change found for this order.')];
        }

        $data = $pending[$orderId];
        unset($pending[$orderId]);
        $this->session->setData(self::SESSION_KEY, $pending);

        $order = $this->orderRepository->get($orderId);
        $originalState = (string)$order->getState();
        $originalStatus = (string)$order->getStatus();

        $newEmail = (string)$data['new_email'];
        $oldEmail = (string)$data['old_email'];
        $oldCustomerId = (int)$data['old_customer_id'];
        $targetCustomerId = (int)$data['target_customer_id'];

        $targetCustomer = $this->customerRepository->getById($targetCustomerId);

        $order->setCustomerId($targetCustomerId);
        $order->setCustomerIsGuest(false);
        $order->setCustomerEmail($newEmail);

        // align names (recommended)
        $order->setCustomerFirstname($targetCustomer->getFirstname());
        $order->setCustomerLastname($targetCustomer->getLastname());

        $comment = __(
            'Customer email changed from %1 to %2 by %3. Order moved from customer_id %4 to %5.',
            $oldEmail,
            $newEmail,
            $adminUser,
            (string)$oldCustomerId,
            (string)$targetCustomerId
        );
        $order->addCommentToStatusHistory((string)$comment, $order->getStatus());

        $this->orderRepository->save($order);
        $this->orderGridUpdater->updateEmail($orderId, $newEmail);
        $this->orderStatusGuard->restoreIfChanged(
            $orderId,
            $originalState,
            $originalStatus,
            (string)$order->getState(),
            (string)$order->getStatus()
        );

        return ['success' => true];
    }

    /**
     * Set the order's email, log a status history comment, and sync the sales grid.
     *
     * @param OrderInterface $order
     * @param string $newEmail
     * @param string $adminUser
     * @param string $oldEmail
     * @param int|null $oldCustomerId
     * @param int|null $newCustomerId
     * @param string $originalState
     * @param string $originalStatus
     * @return void
     */
    private function applyOrderEmail(
        OrderInterface $order,
        string $newEmail,
        string $adminUser,
        string $oldEmail,
        ?int $oldCustomerId,
        ?int $newCustomerId,
        string $originalState,
        string $originalStatus
    ): void {
        $order->setCustomerEmail($newEmail);

        if ($oldCustomerId !== null && $newCustomerId !== null && $oldCustomerId !== $newCustomerId) {
            $comment = __(
                'Customer email changed from %1 to %2 by %3. Order moved from customer_id %4 to %5.',
                $oldEmail,
                $newEmail,
                $adminUser,
                (string)$oldCustomerId,
                (string)$newCustomerId
            );
        } else {
            $comment = __(
                'Customer email changed from %1 to %2 by %3.',
                $oldEmail,
                $newEmail,
                $adminUser
            );
        }

        $order->addCommentToStatusHistory((string)$comment, $order->getStatus());

        $this->orderRepository->save($order);
        $orderId = (int)$order->getEntityId();
        $this->orderGridUpdater->updateEmail($orderId, $newEmail);
        $this->orderStatusGuard->restoreIfChanged(
            $orderId,
            $originalState,
            $originalStatus,
            (string)$order->getState(),
            (string)$order->getStatus()
        );
    }

    /**
     * Look up the customer ID currently using the given email address, if any.
     *
     * @param string $email
     * @return int|null
     */
    private function findCustomerIdByEmail(string $email): ?int
    {
        $collection = $this->customerCollectionFactory->create();
        $collection->addFieldToFilter('email', $email);
        $collection->setPageSize(1);

        $customer = $collection->getFirstItem();
        $id = (int)$customer->getId();

        return $id > 0 ? $id : null;
    }
}
