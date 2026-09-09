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

class OrderStatusGuard
{
    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * Restore the order's original state/status if Magento's own save-time state
     * handler (Magento\Sales\Model\ResourceModel\Order\Handler\State) changed them
     * as a side effect of saving the order for the email change.
     *
     * Writes directly to the sales_order and sales_order_grid tables rather than
     * re-saving the order entity, so the state handler is not triggered again.
     *
     * @param int $orderId
     * @param string $originalState
     * @param string $originalStatus
     * @param string $currentState
     * @param string $currentStatus
     * @return void
     */
    public function restoreIfChanged(
        int $orderId,
        string $originalState,
        string $originalStatus,
        string $currentState,
        string $currentStatus
    ): void {
        if ($originalState === $currentState && $originalStatus === $currentStatus) {
            return;
        }

        $connection = $this->resource->getConnection();

        $connection->update(
            $this->resource->getTableName('sales_order'),
            ['state' => $originalState, 'status' => $originalStatus],
            ['entity_id = ?' => $orderId]
        );

        $connection->update(
            $this->resource->getTableName('sales_order_grid'),
            ['status' => $originalStatus],
            ['entity_id = ?' => $orderId]
        );
    }
}
