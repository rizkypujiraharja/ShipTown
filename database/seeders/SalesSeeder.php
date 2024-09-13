<?php

namespace Database\Seeders;

use App\Events\EveryMinuteEvent;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Modules\InventoryMovementsStatistics\src\Jobs\RecalculateStatisticsTableJob;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventory::query()->update(['recount_required' => true]);

        EveryMinuteEvent::dispatch();

        SequenceNumberJob::dispatch();

        RecalculateStatisticsTableJob::dispatch();

        $inventoryMovements = Inventory::query()
            ->whereNotIn('warehouse_code', ['999'])
            ->get()
            ->map(function (Inventory $inventory) {
                $quantityDelta = -rand(1, $inventory->quantity / 2);

                return [
                    'occurred_at' => now(),
                    'type' => 'sale',
                    'inventory_id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'warehouse_code' => $inventory->warehouse_code,
                    'warehouse_id' => $inventory->warehouse_id,
                    'quantity_before' => $inventory->quantity,
                    'quantity_delta' => $quantityDelta,
                    'quantity_after' => $inventory->quantity + $quantityDelta,
                    'unit_cost' => $inventory->prices->cost,
                    'unit_price' => $inventory->prices->price,
                    'description' => 'sale',
                ];
            });

        InventoryMovement::query()->insert($inventoryMovements->toArray());

        SequenceNumberJob::dispatch();

        RecalculateStatisticsTableJob::dispatch();
    }
}
