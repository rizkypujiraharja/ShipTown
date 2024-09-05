<?php

namespace App\Modules\DataCollector\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Modules\DataCollector\src\Services\DataCollectorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class SyncCheckFailedProductsJob.
 */
class TransferOutJob extends UniqueJob
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

    public function handle()
    {
        Log::debug('TransferOutJob started', ['data_collection_id' => $this->dataCollection_id]);

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::withTrashed()->findOrFail($this->dataCollection_id);

        $dataCollection->records()
            ->where('quantity_scanned', '!=', 0)
            ->chunkById(10, function ($records) {
                $records->each(function (DataCollectionRecord $record) {
                    DataCollectorService::transferOutRecord($record);
                });
            });

        $hasMoreRecordsToTransfer = $dataCollection->records()
            ->where('quantity_scanned', '!=', 0)
            ->exists();

        if ($hasMoreRecordsToTransfer) {
            return;
        } else {
            $dataCollection->update(['currently_running_task' => null]);
            Log::debug('TransferOutJob finished', ['data_collection_id' => $this->dataCollection_id]);
        }

        if ($dataCollection->deleted_at === null && $dataCollection->records()->where('quantity_to_scan', '>', 0)->doesntExist()) {
            $dataCollection->delete();
        }
    }
}
