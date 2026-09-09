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

namespace BluePrint3D\ChangeOrderEmail\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class AddEditOrderEmailButton
{
    /**
     * Add an "Edit Order Email" button to the admin order view toolbar.
     *
     * @param OrderView $subject
     * @return void
     */
    public function beforeSetLayout(OrderView $subject)
    {
        $subject->addButton('change_order_email_edit_button', [
            'label' => __('Edit Order Email'),
            'class' => 'action-secondary',
            'onclick' => "require(['jquery'], function($){ $(document).trigger('changeOrderEmail:open'); });"
        ]);
    }
}
