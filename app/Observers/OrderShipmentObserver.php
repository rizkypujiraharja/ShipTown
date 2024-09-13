<?php

namespace App\Observers;

use App\Events\OrderShipment\OrderShipmentCreatedEvent;
use App\Models\OrderProductShipment;
use App\Models\OrderShipment;

class OrderShipmentObserver
{
    /**
     * Handle the order shipment "created" event.
     */
    public function created(OrderShipment $orderShipment): void
    {
        OrderProductShipment::where(['order_id' => $orderShipment->order_id])
            ->whereNull('order_shipment_id')
            ->update(['order_shipment_id' => $orderShipment->getKey()]);

        OrderShipmentCreatedEvent::dispatch($orderShipment);
    }
}
