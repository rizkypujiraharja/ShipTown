<?php

namespace App\Modules\Webhooks\src\Listeners;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Models\Inventory;
use App\Modules\Webhooks\src\Jobs\PublishInventoryWebhooksJob;
use App\Modules\Webhooks\src\Models\PendingWebhook;

class InventoryUpdatedEventListener
{
    public function handle(InventoryUpdatedEvent $event)
    {
        PendingWebhook::query()->firstOrCreate([
            'model_class' => Inventory::class,
            'model_id' => $event->inventory->getKey(),
        ]);

        PublishInventoryWebhooksJob::dispatchAfterResponse();
    }
}