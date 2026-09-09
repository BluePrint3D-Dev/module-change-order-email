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

namespace BluePrint3D\ChangeOrderEmail\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class OrderGridUpdater
{
    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * Sync the customer email on the sales_order_grid row for the given order.
     *
     * @param int $orderId
     * @param string $email
     * @return void
     */
    public function updateEmail(int $orderId, string $email): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('sales_order_grid');

        $connection->update(
            $table,
            ['customer_email' => $email],
            ['entity_id = ?' => $orderId]
        );
    }
}
