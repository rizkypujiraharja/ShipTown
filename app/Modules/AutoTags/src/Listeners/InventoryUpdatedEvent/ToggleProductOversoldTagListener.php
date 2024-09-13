<?php

namespace App\Modules\AutoTags\src\Listeners\InventoryUpdatedEvent;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Modules\AutoTags\src\Jobs\ToggleOversoldTagJob;

class ToggleProductOversoldTagListener
{
    /**
     * Handle the event.
     */
    public function handle(InventoryUpdatedEvent $event): void
    {
        ToggleOversoldTagJob::dispatch($event->inventory->product_id)
            ->delay(60);
    }
}
