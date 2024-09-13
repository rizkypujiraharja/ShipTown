<?php

namespace Database\Seeders\Demo\DataCollections;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferIn;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class TransfersFromWarehouseSeeder extends Seeder
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

                // not scanned yet
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::inRandomOrder('45')->first()->getKey(),
                    'quantity_requested' => 12,
                    'quantity_scanned' => 0,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::inRandomOrder('46')->first()->getKey(),
                    'quantity_requested' => 12,
                    'quantity_scanned' => 0,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::inRandomOrder('47')->first()->getKey(),
                    'quantity_requested' => 54,
                    'quantity_scanned' => 0,
                ]);

                // fully scanned
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::inRandomOrder('48')->first()->getKey(),
                    'quantity_requested' => 6,
                    'quantity_scanned' => 6,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => 24,
                    'quantity_scanned' => 24,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => 1,
                    'quantity_scanned' => 1,
                ]);

                // over scanned
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => 6,
                    'quantity_scanned' => 12,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => 24,
                    'quantity_scanned' => 25,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => 1,
                    'quantity_scanned' => 6,
                ]);

                // under scanned
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => 6,
                    'quantity_scanned' => 5,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => 24,
                    'quantity_scanned' => 11,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => 3,
                    'quantity_scanned' => 1,
                ]);

                // not requested
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => null,
                    'quantity_scanned' => 12,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => null,
                    'quantity_scanned' => 8,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'warehouse_id' => $destinationWarehouse->getKey(),
                    'warehouse_code' => $destinationWarehouse->code,
                    'product_id' => Product::query()->inRandomOrder()->first()->getKey(),
                    'quantity_requested' => null,
                    'quantity_scanned' => 1,
                ]);
            });
    }
}
