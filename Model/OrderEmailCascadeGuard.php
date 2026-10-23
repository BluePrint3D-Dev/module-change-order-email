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
namespace BluePrint3D\ChangeOrderEmail\Model;

/**
 * Request-scoped switch that stops Magento copying a customer's new email onto
 * all of their orders while this module saves the customer.
 *
 * Saving a customer whose email changed fires customer_save_after_data_object,
 * and core's UpgradeOrderCustomerEmailObserver then rewrites customer_email on
 * every order that customer placed - with no history comment. This module edits
 * one order at a time, so it holds this switch on around its own customer save.
 */
class OrderEmailCascadeGuard
{
    /**
     * @var bool
     */
    private bool $active = false;

    /**
     * Run $callback with the cascade switched off
     *
     * @param callable $callback
     * @return mixed
     */
    public function withoutCascade(callable $callback)
    {
        $this->active = true;
        try {
            return $callback();
        } finally {
            $this->active = false;
        }
    }

    /**
     * Whether the cascade is currently switched off
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}
