<?php

namespace App\Observers;

use App\Events\OrderProduct\OrderProductShipmentCreatedEvent;
use App\Models\OrderProductShipment;

class OrderProductShipmentObserver
{
    /**
     * Handle the order product "created" event.
     */
    public function created(OrderProductShipment $orderProductShipment): void
    {
        OrderProductShipmentCreatedEvent::dispatch($orderProductShipment);

        $this->logActivitiesAbout($orderProductShipment);
    }

    private function logActivitiesAbout(OrderProductShipment $orderProductShipment): void
    {
        //  log activity on order
        if ($orderProductShipment->order) {
            activity()->on($orderProductShipment->order)
                ->causedBy($orderProductShipment->user)
                ->withProperties([
                    'sku' => $orderProductShipment->sku_shipped,
                    'quantity' => $orderProductShipment->quantity_shipped,
                    'warehouse_code' => data_get($orderProductShipment, 'warehouse.code'),
                ])
                ->log('shipped');
        }

        // log activity on product
        if ($orderProductShipment->product) {
            activity()->on($orderProductShipment->product)
                ->causedBy($orderProductShipment->user)
                ->withProperties([
                    'sku' => $orderProductShipment->sku_shipped,
                    'order_number' => $orderProductShipment->order->order_number,
                    'quantity' => $orderProductShipment->quantity_shipped,
                    'warehouse_code' => data_get($orderProductShipment, 'warehouse.code'),
                ])
                ->log('shipped');
        }
    }
}
