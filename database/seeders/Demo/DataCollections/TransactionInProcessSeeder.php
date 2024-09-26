<?php

namespace Database\Seeders\Demo\DataCollections;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use Illuminate\Database\Seeder;

class TransactionInProcessSeeder extends Seeder
{
    public function run(): void
    {
        $dataCollection = DataCollection::factory()->create([
            'type' => DataCollectionTransaction::class,
            'custom_uuid' => 'TRANSACTION_IN_PROGRESS_FOR_USER_1_Artur Hanusek',
        ]);

        DataCollectionRecord::factory()
            ->count(rand(3, 5))
            ->create([
                'data_collection_id' => $dataCollection->getKey(),
                'warehouse_id' => 1,
                'warehouse_code' => 'WHS',
            ]);
    }
}
