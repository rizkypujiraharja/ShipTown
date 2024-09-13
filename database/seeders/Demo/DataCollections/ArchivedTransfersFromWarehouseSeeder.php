<?php

namespace Database\Seeders\Demo\DataCollections;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferIn;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\Jobs\TransferInJob;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Modules\InventoryMovementsStatistics\src\Jobs\RecalculateStatisticsTableJob;
use Illuminate\Database\Seeder;

class ArchivedTransfersFromWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sourceWarehouse = Warehouse::query()->firstOrCreate(['code' => 'WHS'], ['name' => 'Warehouse']);

        Warehouse::query()
            ->whereNotIn('code', ['WHS', '999'])
            ->get()
            ->each(function ($destinationWarehouse) use ($sourceWarehouse) {
                $dataCollection = DataCollection::factory()
                    ->create([
                        'warehouse_id' => $destinationWarehouse->getKey(),
                        'warehouse_code' => $destinationWarehouse->code,
                        'name' => 'Transfer from '.$sourceWarehouse->name,
                        'type' => DataCollectionTransferIn::class,
                        'created_at' => now()->subHours(rand(24, 48)),
                    ]);

                DataCollectionRecord::factory()
                    ->count(5)
                    ->create([
                        'data_collection_id' => $dataCollection->getKey(),
                        'warehouse_id' => $destinationWarehouse->getKey(),
                        'warehouse_code' => $destinationWarehouse->code,
                        'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    ]);

                $dataCollection->delete();

                TransferInJob::dispatch($dataCollection->getKey());
            });

        SequenceNumberJob::dispatch();
        RecalculateStatisticsTableJob::dispatch();
    }
}
