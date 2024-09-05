<?php

namespace App\Modules\DataCollector\src\Services;

use App\Events\DataCollection\DataCollectionRecalculateRequestEvent;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionStocktake;
use App\Models\DataCollectionTransferIn;
use App\Models\DataCollectionTransferOut;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\Jobs\ImportAsStocktakeJob;
use App\Modules\DataCollector\src\Jobs\TransferInJob;
use App\Modules\DataCollector\src\Jobs\TransferOutJob;
use App\Modules\DataCollector\src\Jobs\TransferToJob;
use App\Services\InventoryService;
use Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataCollectorService
{
    public static function recalculate(DataCollection $dataCollection): void
    {
        $lockKey = 'recalculating_data_collection_lock_'.$dataCollection->id;

        Cache::lock($lockKey, 5)->get(function () use ($dataCollection) {
            DataCollectionRecalculateRequestEvent::dispatch($dataCollection);
        });
    }

    public static function runAction(DataCollection $dataCollection, $action): void
    {
        if ($action === 'transfer_in_scanned') {
            $dataCollection->update([
                'type' => DataCollectionTransferIn::class,
                'currently_running_task' => TransferInJob::class,
            ]);

            if (! $dataCollection->records()
                ->where('quantity_to_scan', '>', 0)
                ->exists()) {
                $dataCollection->delete();
            }

            TransferInJob::dispatch($dataCollection->id);

            return;
        }

        if ($action === 'transfer_out_scanned') {
            $dataCollection->update([
                'type' => DataCollectionTransferOut::class,
                'currently_running_task' => TransferOutJob::class,
            ]);

            if (! $dataCollection->records()
                ->where('quantity_to_scan', '>', 0)
                ->exists()) {
                $dataCollection->delete();
            }

            TransferOutJob::dispatch($dataCollection->id);

            return;
        }

        if ($action === 'transfer_to_scanned') {
            $dataCollection->update([
                'type' => DataCollectionTransferOut::class,
                'currently_running_task' => TransferToJob::class,
            ]);

            $dataCollection->delete();

            TransferToJob::dispatch($dataCollection->id);

            return;
        }

        if ($action === 'auto_scan_all_requested') {
            DB::transaction(function () use ($dataCollection) {
                $dataCollection->records()
                    ->whereNotNull('quantity_requested')
                    ->where('quantity_scanned', 0)
                    ->update(['quantity_scanned' => DB::raw('quantity_to_scan')]);
            });

            return;
        }

        if ($action === 'import_as_stocktake') {
            $dataCollection->update([
                'type' => DataCollectionStocktake::class,
                'currently_running_task' => ImportAsStocktakeJob::class,
            ]);

            $dataCollection->delete();

            ImportAsStocktakeJob::dispatch($dataCollection->id);
        }
    }

    public static function transferScannedTo(DataCollection $sourceDataCollection, int $warehouse_id): DataCollection
    {
        $destinationDataCollection = $sourceDataCollection->destinationCollection;

        /** @var Warehouse $destinationWarehouse */
        $destinationWarehouse = Warehouse::findOrFail($warehouse_id);

        DB::transaction(function () use (
            $sourceDataCollection,
            &$destinationDataCollection,
            $destinationWarehouse
        ) {
            // create collection
            if ($destinationDataCollection == null) {
                $name = implode(
                    ' ',
                    ['Transfer from', $sourceDataCollection->warehouse->code, '-', $sourceDataCollection->name]
                );

                $destinationDataCollection = $sourceDataCollection->replicate([
                    'destination_warehouse_id',
                    'currently_running_task',
                    'deleted_at',
                ]);
                $destinationDataCollection->type = DataCollectionTransferIn::class;
                $destinationDataCollection->warehouse_code = $destinationWarehouse->code;
                $destinationDataCollection->warehouse_id = $destinationWarehouse->id;
                $destinationDataCollection->name = $name;
                $destinationDataCollection->save();

                $sourceDataCollection->update(['destination_collection_id' => $destinationDataCollection->id]);
            }

            $sourceDataCollection->update([
                'name' => implode(' ', ['Transfer To', $destinationWarehouse->code, '-', $sourceDataCollection->name]),
                'type' => DataCollectionTransferOut::class,
                'currently_running_task' => TransferOutJob::class,
            ]);

            $sourceDataCollection->delete();
        });

        $sourceDataCollection->records()
            ->where('quantity_scanned', '!=', 0)
            ->get()
            ->each(function (DataCollectionRecord $sourceRecord) use ($destinationDataCollection) {
                $inventory = Inventory::query()->firstOrCreate([
                    'product_id' => $sourceRecord->product_id,
                    'warehouse_code' => $destinationDataCollection->warehouse_code,
                    'warehouse_id' => $destinationDataCollection->warehouse_id,
                ]);

                $pricing = $sourceRecord->product->prices()
                    ->where('warehouse_id', $destinationDataCollection->warehouse_id)
                    ->first();

                $custom_uuid = implode('-', ['source_data_collections_records_id', $sourceRecord->getKey()]);

                DataCollectionRecord::query()->firstOrCreate([
                    'custom_uuid' => $custom_uuid,
                ], [
                    'data_collection_id' => $destinationDataCollection->id,
                    'inventory_id' => $inventory->id,
                    'warehouse_code' => $inventory->warehouse_code,
                    'warehouse_id' => $inventory->warehouse_id,
                    'product_id' => $inventory->product_id,
                    'quantity_requested' => $sourceRecord->quantity_scanned,
                    'unit_cost' => $sourceRecord->unit_full_price,
                    'unit_sold_price' => $pricing->price,
                    'unit_full_price' => $sourceRecord->unit_full_price,
                ]);
            });

        TransferOutJob::dispatch($sourceDataCollection->getKey());

        return $destinationDataCollection;
    }

    public static function transferInRecord(DataCollectionRecord $record): void
    {
        DB::transaction(function () use ($record) {
            $record->refresh();

            if ($record->quantity_scanned == 0) {
                return;
            }

            $custom_unique_reference_id = implode(':', [
                'data_collection_id',
                $record->data_collection_id,
                'data_collection_record_id',
                $record->getKey(),
                $record->updated_at,
            ]);

            $inventory = Inventory::query()->firstOrCreate([
                'warehouse_id' => $record->dataCollection->warehouse_id,
                'product_id' => $record->product_id,
            ], []);

            InventoryService::transferIn($inventory, $record->quantity_scanned, [
                'occurred_at' => $record->dataCollection->deleted_at ?? now()->utc()->toDateTimeLocalString(),
                'description' => Str::substr('Data Collection - '.$record->dataCollection->name, 0, 50),
                'custom_unique_reference_id' => $custom_unique_reference_id,
            ]);

            $record->update([
                'total_transferred_in' => $record->total_transferred_in + $record->quantity_scanned,
                'quantity_scanned' => 0,
            ]);
        });
    }

    public static function transferOutRecord(DataCollectionRecord $record): void
    {
        DB::transaction(function () use ($record) {
            $record->refresh();

            if ($record->quantity_scanned == 0) {
                return;
            }

            $inventory = Inventory::query()->firstOrCreate([
                'warehouse_id' => $record->dataCollection->warehouse_id,
                'product_id' => $record->product_id,
            ], []);

            $custom_unique_reference_id = implode(':', [
                'occurred_at' => $record->dataCollection->deleted_at ?? now()->utc()->toDateTimeLocalString(),
                'data_collection_id',
                $record->data_collection_id,
                'data_collection_record_id',
                $record->getKey(),
                $record->updated_at,
            ]);

            InventoryService::transferOut($inventory, $record->quantity_scanned * -1, [
                'description' => Str::substr('Data Collection - '.$record->dataCollection->name, 0, 50),
                'custom_unique_reference_id' => $custom_unique_reference_id,
            ]);

            $record->update([
                'total_transferred_out' => $record->total_transferred_out + $record->quantity_scanned,
                'quantity_scanned' => 0,
            ]);
        });
    }

    public static function splitRecord(DataCollectionRecord $record, mixed $quantityToExtract): void
    {
        $record->decrement('quantity_scanned', $quantityToExtract);

        if ($record->quantity_scanned == 0) {
            $record->delete();
        }

        $newRecord = DataCollectionRecord::firstOrCreate(
            array_merge(
                [
                    'data_collection_id' => $record->data_collection_id,
                    'inventory_id' => $record->inventory_id,
                    'unit_cost' => $record->unit_cost,
                    'unit_sold_price' => $record->unit_sold_price,
                    'price_source' => $record->price_source,
                    'price_source_id' => $record->price_source_id,
                ],
            ),
            [
                'unit_full_price' => $record->unit_full_price,
                'warehouse_id' => $record->warehouse_id,
                'product_id' => $record->product_id,
                'warehouse_code' => $record->warehouse_code,
                'quantity_requested' => 0,
            ]
        );

        //        $newRecord->update($discountedAttributes);
        $newRecord->increment('quantity_scanned', $quantityToExtract);
    }
}
