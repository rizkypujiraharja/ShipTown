<?php

namespace App\Modules\DataCollector\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Modules\DataCollector\src\Services\DataCollectorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferInJob extends UniqueJob
{
    public int $dataCollection_id;

    public function __construct($dataCollection_id)
    {
        $this->dataCollection_id = $dataCollection_id;
    }

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->dataCollection_id]);
    }

    public function handle(): void
    {
        Log::debug('TransferInJob started', ['data_collection_id' => $this->dataCollection_id]);

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::withTrashed()->findOrFail($this->dataCollection_id);

        $dataCollection->records()
            ->with('inventory')
            ->where('quantity_scanned', '!=', 0)
            ->chunkById(10, function ($records) {
                $records->each(function (DataCollectionRecord $record) {
                    DataCollectorService::transferInRecord($record);
                });
            });

        $hasMoreRecordsToTransfer = $dataCollection->records()
            ->where('quantity_scanned', '!=', 0)
            ->exists();

        if ($hasMoreRecordsToTransfer) {
            return;
        } else {
            $dataCollection->update(['currently_running_task' => null]);
            Log::debug('TransferInJob finished', ['data_collection_id' => $this->dataCollection_id]);
        }

        if ($dataCollection->records()->where('quantity_to_scan', '>', 0)->doesntExist()) {
            $dataCollection->delete();
        }
    }
}
