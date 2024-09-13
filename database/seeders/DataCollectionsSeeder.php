<?php

namespace Database\Seeders;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferIn;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DataCollectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::query()
            ->get()
            ->each(function ($sourceWarehouse) {

                Warehouse::query()
                    ->whereNotIn('id', [$sourceWarehouse->id])
                    ->get()
                    ->each(function ($destinationWarehouse) use ($sourceWarehouse) {
                        $dataCollection = DataCollection::factory()->create([
                            'warehouse_id' => $destinationWarehouse->getKey(),
                            'name' => implode(' ', [
                                $sourceWarehouse->code,
                                'to',
                                $destinationWarehouse->code,
                            ]),
                            'type' => DataCollectionTransferIn::class,
                        ]);

                        DataCollectionRecord::factory()->create([
                            'data_collection_id' => $dataCollection->getKey(),
                            'product_id' => Product::factory()->create()->getKey(),
                            'quantity_requested' => rand(1, 100),
                        ]);
                    });
            });
    }
}
