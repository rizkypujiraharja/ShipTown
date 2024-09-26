<?php

namespace Database\Seeders;

use App\Jobs\DispatchEveryDayEventJob;
use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Jobs\DispatchEveryHourEventJobs;
use App\Jobs\DispatchEveryMinuteEventJob;
use App\Jobs\DispatchEveryTenMinutesEventJob;
use App\Models\AutoStatusPickingConfiguration;
use App\Modules\AutoStatusPicking\src\AutoStatusPickingServiceProvider;
use App\Modules\ScurriAnpost\database\seeders\ScurriAnpostSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('up');

        $this->call([
            Demo\ConfigurationSeeder::class,
            //            Demo\NavigationSeeder::class,
            Demo\OrderStatusesSeeder::class,
            Demo\UsersSeeder::class,

            \Database\Seeders\WarehousesSeeder::class,

            Demo\ProductsSeeder::class,
            Demo\ProductsTagsSeeder::class,
            Demo\ProductsPricesSeeder::class,
            \Database\Seeders\InventorySeeder::class,
            Demo\QuantityDiscountSeeder::class,

            // Orders Seeders
            Demo\PaidOrdersSeeder::class,
            Demo\PaidPickedOrdersSeeder::class,
            Demo\CollectionOrdersSeeder::class,
            Demo\TestOrdersSeeder::class,

            // Data Collector Seeders
            Demo\DataCollections\TransferToCorkBranchSeeder::class,
            Demo\DataCollections\TransfersFromWarehouseSeeder::class,
            Demo\DataCollections\ArchivedTransfersFromWarehouseSeeder::class,
            Demo\DataCollections\TransactionInProcessSeeder::class,

            SalesSeeder::class,
            StocktakeSuggestionsSeeder::class,

            PrintNodeClientSeeder::class,
            DpdIrelandSeeder::class,
            DpdUKSeeder::class,
            ScurriAnpostSeeder::class,
            Modules\Slack\ConfigurationSeeder::class,
            Modules\Magento2MSI\ConnectionSeeder::class,
            Modules\Magento2API\ConnectionSeeder::class,

            RestockingReportSeeder::class,
            //            DataCollectionsSeeder::class,
            //            RmsapiConnectionSeeder::class,
            //            AutomationsSeeder::class,

            //            ProductAliasSeeder::class,
            //            ProductTagsSeeder::class,
            //            SplitOrdersScenarioSeeder::class,
            //            Orders_PackingWebDemoSeeder::class,
            //            Orders_StorePickupDemoSeeder::class,
            //            UnpaidOrdersSeeder::class,
            //            ClosedOrdersSeeder::class,
            //            PicksSeeder::class,
            //            OrderShipmentsSeeder::class,

        ]);

        AutoStatusPickingConfiguration::query()->updateOrCreate(['max_batch_size' => 5]);
        AutoStatusPickingServiceProvider::enableModule();

        DispatchEveryMinuteEventJob::dispatch();
        DispatchEveryFiveMinutesEventJob::dispatch();
        DispatchEveryTenMinutesEventJob::dispatch();
        DispatchEveryHourEventJobs::dispatch();
        DispatchEveryDayEventJob::dispatch();
    }
}
