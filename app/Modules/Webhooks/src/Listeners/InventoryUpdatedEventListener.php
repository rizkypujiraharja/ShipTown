<?php

namespace App\Modules\Webhooks\src\Listeners;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Models\Inventory;
use App\Modules\Webhooks\src\Models\PendingWebhook;

class InventoryUpdatedEventListener
{
    public function handle(InventoryUpdatedEvent $event): void
    {
        if ($event->inventory->recount_required) {
            return;
        }

        PendingWebhook::query()->firstOrCreate([
            'model_class' => Inventory::class,
            'model_id' => $event->inventory->getKey(),
            'reserved_at' => null,
            'published_at' => null,
        ]);
    }
}
