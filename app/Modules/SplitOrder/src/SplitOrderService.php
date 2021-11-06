<?php

namespace App\Modules\SplitOrder\src;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Warehouse;

/**
 *
 */
class SplitOrderService
{
    /**
     * @var Warehouse
     */
    private Warehouse $warehouse;

    /**
     * @var Order|null
     */
    private ?Order $newOrder = null;

    /**
     * @var string|null
     */
    private ?string $newOrderStatus;

    /**
     * @var Order
     */
    private Order $originalOrder;

    public function split(Order $order, Warehouse $warehouse, string $newOrderStatus)
    {
        $this->originalOrder = $order;
        $this->newOrderStatus = $newOrderStatus;
        $this->warehouse = $warehouse;

        self::extractFulfillableProducts();
    }

    /**
     * @return Order|null
     */
    private function extractFulfillableProducts(): ?Order
    {
        $this->originalOrder->orderProducts
            ->filter(function (OrderProduct $orderProductOriginal) {
                return $orderProductOriginal->quantity_to_ship > 0;
            })
            ->each(function (OrderProduct $orderProductOriginal) use (&$orderProductsToExtract) {
                /** @var Inventory $inventory */
                $inventory = $orderProductOriginal->product->inventory()
                    ->where(['warehouse_id' => $this->warehouse->getKey()])
                    ->first();

                $quantityToExtract = min($inventory->quantity_available, $orderProductOriginal->quantity_to_ship);

                if ($quantityToExtract <= 0.00) {
                    return true; // return true to continue loop
                }

                $inventory->increment('quantity_reserved', $quantityToExtract);

                $orderProductNew = $orderProductOriginal->replicate();
                $orderProductNew->order_id = $this->getOrderOrCreate()->getKey();
                $orderProductNew->quantity_ordered = $quantityToExtract;
                $orderProductNew->quantity_split = 0;
                $orderProductNew->quantity_picked = 0;
                $orderProductNew->quantity_skipped_picking = 0;
                $orderProductNew->save();

                $orderProductOriginal->increment('quantity_split', $quantityToExtract);

                return true;
            });

        if ($this->newOrder) {
            $this->originalOrder->is_editing = false;
            $this->originalOrder->save();

            $this->newOrder->is_editing = false;
            $this->newOrder->save();
            return $this->newOrder;
        }

        return null;
    }

    /**
     * @return Order
     */
    private function getOrderOrCreate(): Order
    {
        if ($this->newOrder) {
            return $this->newOrder;
        }

        $this->originalOrder->is_editing = true;
        $this->originalOrder->save();

        $this->newOrder = $this->originalOrder->replicate();
        $this->newOrder->status_code = $this->newOrderStatus;
        $this->newOrder->is_editing = true;
        $this->newOrder->order_number .= '-PARTIAL-' . $this->warehouse->code;
        $this->newOrder->save();

        return $this->newOrder;
    }
}