<?php

namespace App\Observers;

use App\Events\OrderStatus\OrderStatusUpdatedEvent;
use App\Models\OrderStatus;

class OrderStatusObserver
{
    public function updated(OrderStatus $orderStatus): void
    {
        OrderStatusUpdatedEvent::dispatch($orderStatus);
    }
}
