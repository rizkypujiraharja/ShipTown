<?php

namespace App\Observers;

use App\Events\Order\OrderUpdatedEvent;
use App\Models\Order;
use App\Models\OrderStatus;

class OrderObserver
{
    public function creating(Order $order)
    {
        if ($order->status_code != '') {
            OrderStatus::query()->firstOrCreate(['code' => $order->status_code], ['name' => $order->status_code]);
        }
    }

    public function saving(Order $order)
    {
        if ($order->isAttributeChanged('is_active')) {
            $order->order_closed_at = $order->is_active ? null : now();
        }
    }

    public function updating(Order $order)
    {
        if ($order->isAttributeChanged('status_code')) {
            OrderStatus::query()->firstOrCreate(['code' => $order->status_code], ['name' => $order->status_code]);
        }
    }

    public function created(Order $order): void
    {
        // we will not dispatch CreatedEvent here
        // please use OrderService method
        // CreatedEvent should be dispatched
        // after created OrderProducts etc

        // OrderCreatedEvent::dispatch();
    }

    public function updated(Order $order): void
    {
        OrderUpdatedEvent::dispatch($order);
    }
}
