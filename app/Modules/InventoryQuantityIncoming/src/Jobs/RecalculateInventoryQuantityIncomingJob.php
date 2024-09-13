<?php

namespace App\Modules\InventoryQuantityIncoming\src\Jobs;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RecalculateInventoryQuantityIncomingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public ?int $product_id;

    public ?int $warehouse_id;

    public function __construct(int $product_id, int $warehouse_id)
    {
        $this->product_id = $product_id;
        $this->warehouse_id = $warehouse_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $inventoryRecords = DB::select('
            SELECT inventory.id as id,
                    MAX(inventory.product_id) as product_id,
                    MAX(inventory.warehouse_id) as warehouse_id,
                    MAX(inventory.quantity_incoming) as actual_quantity_incoming,
                    SUM(IFNULL(data_collection_records.quantity_to_scan + data_collection_records.quantity_scanned, 0)) as expected_quantity_incoming

            FROM inventory

            LEFT JOIN data_collections
                ON data_collections.warehouse_id = inventory.warehouse_id
                AND data_collections.deleted_at IS NULL
                AND data_collections.type = ?

            LEFT JOIN data_collection_records
              ON data_collection_records.data_collection_id = data_collections.id
              AND data_collection_records.product_id = inventory.product_id

           WHERE data_collections.warehouse_id = ?
                AND data_collection_records.product_id = ?

           GROUP BY inventory.id

           HAVING actual_quantity_incoming <> expected_quantity_incoming
        ', [\App\Models\DataCollectionTransferIn::class, $this->warehouse_id, $this->product_id]);

        collect($inventoryRecords)
            ->each(function ($incorrectRecord) {
                Inventory::query()
                    ->where('id', $incorrectRecord->id)
                    ->get()
                    ->each(function (Inventory $inventory) use ($incorrectRecord) {
                        $inventory->quantity_incoming = $incorrectRecord->expected_quantity_incoming;
                        $inventory->save();
                    });
            });
    }
}
