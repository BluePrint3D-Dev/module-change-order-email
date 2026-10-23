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
namespace BluePrint3D\ChangeOrderEmail\Plugin;

use BluePrint3D\ChangeOrderEmail\Model\OrderEmailCascadeGuard;
use Magento\Customer\Observer\UpgradeOrderCustomerEmailObserver;
use Magento\Framework\Event\Observer;

/**
 * Skip core's copy-new-email-to-all-orders step during this module's own
 * customer save, so only the order being edited changes. Any other customer
 * email change (customer account page, admin customer edit) behaves as normal.
 */
class SkipOrderEmailCascade
{
    /**
     * @param OrderEmailCascadeGuard $guard
     */
    public function __construct(
        private readonly OrderEmailCascadeGuard $guard
    ) {
    }

    /**
     * Do nothing while OrderEmailCascadeGuard is active
     *
     * @param UpgradeOrderCustomerEmailObserver $subject
     * @param callable $proceed
     * @param Observer $observer
     * @return void
     */
    public function aroundExecute(
        UpgradeOrderCustomerEmailObserver $subject,
        callable $proceed,
        Observer $observer
    ): void {
        if ($this->guard->isActive()) {
            return;
        }
        $proceed($observer);
    }
}
