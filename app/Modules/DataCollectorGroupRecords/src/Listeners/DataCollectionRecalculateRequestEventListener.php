<?php

namespace App\Modules\DataCollectorGroupRecords\src\Listeners;

use App\Events\DataCollection\DataCollectionRecalculateRequestEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorGroupRecords\src\Jobs\GroupRecordsJob;

class DataCollectionRecalculateRequestEventListener
{
    public function handle(DataCollectionRecalculateRequestEvent $event): void
    {
        if ($event->dataCollection->type !== DataCollectionTransaction::class) {
            return;
        }

        GroupRecordsJob::dispatchSync($event->dataCollection);
    }
}