<?php

namespace App\Modules\OrderTotals\src\Listeners;

use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Modules\OrderTotals\src\Services\OrderTotalsService;

class OrderProductUpdatedEventListener
{
    public function handle(OrderProductUpdatedEvent $event): void
    {
        OrderTotalsService::updateTotals($event->orderProduct->order_id);
    }
}
