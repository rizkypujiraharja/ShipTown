<?php

namespace Database\Seeders;

use App\Jobs\DispatchEveryHourEventJobs;
use Illuminate\Database\Seeder;

class BlankDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            Demo\ConfigurationSeeder::class,

            //            WebhooksTestSeeder::class,
            //            RmsapiConnectionSeeder::class,

            //            AutomationsSeeder::class,
            //            PrintNodeClientSeeder::class,
            //            DpdIrelandSeeder::class,
            //            ProductAliasSeeder::class,
            //            ProductTagsSeeder::class,
            //            ProductPriceSeeder::class,
            //            SplitOrdersScenarioSeeder::class,
            //            Orders_PackingWebDemoSeeder::class,
            //            Orders_StorePickupDemoSeeder::class,
            //            UnpaidOrdersSeeder::class,
            //            ClosedOrdersSeeder::class,
            //            PicksSeeder::class,
            //            OrderShipmentsSeeder::class,
        ]);

        DispatchEveryHourEventJobs::dispatchSync();
    }
}
