<?php

namespace App\Modules\OrderTotals\src\Listeners;

use App\Events\Order\OrderCreatedEvent;
use App\Modules\OrderTotals\src\Services\OrderTotalsService;

class OrderCreatedEventListener
{
    public function handle(OrderCreatedEvent $event): void
    {
        OrderTotalsService::updateTotals($event->order->id);
    }
}
