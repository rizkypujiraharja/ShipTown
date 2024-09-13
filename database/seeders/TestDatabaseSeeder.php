<?php

namespace Database\Seeders;

use App\Jobs\DispatchEveryHourEventJobs;
use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ConfigurationSeeder::class,
            NavigationMenuSeeder::class,
            WarehousesSeeder::class,

            UsersSeeder::class,
            ProductsSeeder::class,
            PaidOrdersSeeder::class,

            //            AutomationsSeeder::class,
            //
            //            PrintNodeClientSeeder::class,
            DpdUkTestConnectionSeeder::class,
            WebhooksTestSeeder::class,
            //            DpdIrelandSeeder::class,
            //
            //            ProductAliasSeeder::class,
            //            ProductTagsSeeder::class,
            //            ProductPriceSeeder::class,
            //
            InventorySeeder::class,

            //
            //            SplitOrdersScenarioSeeder::class,
            //
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
