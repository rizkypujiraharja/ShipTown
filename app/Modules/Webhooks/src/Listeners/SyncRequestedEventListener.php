<?php

namespace App\Modules\Webhooks\src\Listeners;

use App\Events\SyncRequestedEvent;
use App\Modules\Webhooks\src\Jobs\PublishInventoryMovementWebhooksJob;

class SyncRequestedEventListener
{
    public function handle(SyncRequestedEvent $event)
    {
        PublishInventoryMovementWebhooksJob::dispatch();
    }
}