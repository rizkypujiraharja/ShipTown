<?php

namespace App\Modules\Inventory\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\Inventory\InventoryUpdatedEvent;
use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Models\Inventory;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DispatchRecalculateInventoryRecordsJob extends UniqueJob
{
    public function handle(): void
    {
        Inventory::where(['recount_required' => true])
            ->chunkById(100, function (Collection $records) {
                $recordsUpdated = Inventory::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->update([
                        'recount_required'      => false,
                        'quantity'              => DB::raw('IFNULL((SELECT quantity_after FROM inventory_movements WHERE inventory_id = inventory.id ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1), 0)'),
                        'quantity_reserved'     => DB::raw('(SELECT IFNULL(SUM(quantity_reserved), 0) FROM inventory_reservations WHERE inventory_id = inventory.id)'),
                        'last_sequence_number'  => DB::raw('(SELECT sequence_number FROM inventory_movements WHERE inventory_id = inventory.id AND sequence_number IS NOT NULL ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1)'),
                        'last_movement_id'      => DB::raw('(SELECT ID FROM inventory_movements WHERE inventory_id = inventory.id ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1)'),
                        'first_movement_at'     => DB::raw('(SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id)'),
                        'last_movement_at'      => DB::raw('(SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id)'),
                        'first_counted_at'      => DB::raw("(SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = 'stocktake')"),
                        'last_counted_at'       => DB::raw("(SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = 'stocktake')"),
                        'in_stock_since'        => DB::raw('(SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_before = 0)'),
                        'first_sold_at'         => DB::raw("(SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = 'sale')"),
                        'last_sold_at'          => DB::raw("(SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = 'sale')"),
                        'first_received_at'     => DB::raw('(SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_delta > 0)'),
                        'last_received_at'      => DB::raw('(SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_delta > 0)'),
                        'updated_at'            => now(),
                        'recalculated_at'       => now(),
                    ]);

                $records->each(function (Inventory $inventory) {
                    InventoryUpdatedEvent::dispatch($inventory);
                });

                Log::info('Job processing', [
                    'job'            => self::class,
                    'recordsUpdated' => $recordsUpdated,
                ]);

                RecalculateInventoryRequestEvent::dispatch($records->pluck('id'), $records);

                usleep(50000); // 0.05 seconds
            });
    }
}
