<?php

namespace App\Observers;

use App\Events\DataCollection\DataCollectionCreatedEvent;
use App\Events\DataCollection\DataCollectionDeletedEvent;
use App\Events\DataCollection\DataCollectionUpdatedEvent;
use App\Models\DataCollection;

class DataCollectionObserver
{
    public function created(DataCollection $dataCollection): void
    {
        DataCollectionCreatedEvent::dispatch($dataCollection);
    }

    public function updated(DataCollection $dataCollection): void
    {
        DataCollectionUpdatedEvent::dispatch($dataCollection);
    }

    public function deleted(DataCollection $dataCollection): void
    {
        DataCollectionDeletedEvent::dispatch($dataCollection);
    }
}
