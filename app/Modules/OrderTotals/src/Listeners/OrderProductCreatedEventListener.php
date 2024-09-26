<?php

namespace App\Modules\OrderTotals\src\Listeners;

use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Modules\OrderTotals\src\Services\OrderTotalsService;

class OrderProductCreatedEventListener
{
    public function handle(OrderProductCreatedEvent $event): void
    {
        OrderTotalsService::updateTotals($event->orderProduct->order_id);
    }
}
