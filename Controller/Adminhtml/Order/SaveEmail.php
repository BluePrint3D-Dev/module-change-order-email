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

namespace BluePrint3D\ChangeOrderEmail\Controller\Adminhtml\Order;

use BluePrint3D\ChangeOrderEmail\Model\Service\OrderEmailUpdater;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

class SaveEmail extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param OrderEmailUpdater $updater
     */
    public function __construct(
        Action\Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly OrderEmailUpdater $updater
    ) {
        parent::__construct($context);
    }

    /**
     * Save a new email address on the order, updating the linked customer where applicable.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        $orderId = (int)$this->getRequest()->getParam('order_id');
        $newEmail = (string)$this->getRequest()->getParam('new_email');
        $confirmEmail = (string)$this->getRequest()->getParam('confirm_email');

        if (!$orderId) {
            return $result->setData(['success' => false, 'message' => __('Missing order_id.')]);
        }

        if (!$newEmail || $newEmail !== $confirmEmail || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return $result->setData([
                'success' => false,
                'message' => __('Please enter a valid email and confirm it.')
            ]);
        }

        $adminUser = $this->_auth->getUser() ? (string)$this->_auth->getUser()->getUserName() : 'admin';

        try {
            return $result->setData($this->updater->attemptChange($orderId, $newEmail, $adminUser));
        } catch (\Throwable $e) {
            return $result->setData(['success' => false, 'message' => __('Error: %1', $e->getMessage())]);
        }
    }
}
