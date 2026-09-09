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

class ConfirmMove extends Action
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
     * Confirm moving the order to the customer that already owns the pending new email address.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $orderId = (int)$this->getRequest()->getParam('order_id');

        if (!$orderId) {
            return $result->setData(['success' => false, 'message' => __('Missing order_id.')]);
        }

        $adminUser = $this->_auth->getUser() ? (string)$this->_auth->getUser()->getUserName() : 'admin';

        try {
            return $result->setData($this->updater->confirmMove($orderId, $adminUser));
        } catch (\Throwable $e) {
            return $result->setData(['success' => false, 'message' => __('Error: %1', $e->getMessage())]);
        }
    }
}
