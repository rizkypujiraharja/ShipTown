<?php

namespace App\Console\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Spatie\Tags\Tag;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ClearDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the database except migrations table as this is intended only for testing purposes, please use with caution.';

    public function handle(): int
    {
        $this->call('down');

        $this->resetDatabase();

        Log::info('Database cleared.');

        DB::transaction(function () {
            Artisan::call('app:install');
        });

        Log::info('Application installed.');

        $this->call('up');

        return CommandAlias::SUCCESS;
    }

    public static function resetDatabase(): void
    {
        Tag::query()->forceDelete();
        Activity::query()->forceDelete();

        App\Modules\InventoryTotals\src\Models\Configuration::query()->forceDelete();
        App\Modules\InventoryTotals\src\Models\InventoryTotal::query()->forceDelete();
        App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag::query()->forceDelete();
        App\Modules\InventoryMovements\src\Models\Configuration::query()->forceDelete();
        App\Modules\InventoryMovementsStatistics\src\Models\InventoryMovementsStatistic::query()->forceDelete();
        App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration::query()->forceDelete();
        App\Modules\PrintNode\src\Models\PrintJob::query()->forceDelete();
        App\Modules\PrintNode\src\Models\Client::query()->forceDelete();
        App\Modules\DpdUk\src\Models\Connection::query()->forceDelete();
        App\Modules\Rmsapi\src\Models\RmsapiSaleImport::query()->forceDelete();
        App\Modules\Magento2MSI\src\Models\Magento2msiProduct::query()->forceDelete();
        App\Modules\Magento2MSI\src\Models\Magento2msiConnection::query()->forceDelete();
        App\Modules\MagentoApi\src\Models\MagentoProduct::query()->forceDelete();
        App\Modules\MagentoApi\src\Models\MagentoConnection::query()->forceDelete();

        App\Models\InventoryReservation::query()->forceDelete();

        App\Models\OrderProductPick::query()->forceDelete();
        App\Models\Pick::query()->forceDelete();
        App\Models\NavigationMenu::query()->forceDelete();
        App\Models\ProductAlias::query()->forceDelete();
        App\Models\Product::query()->forceDelete();
        App\Models\Inventory::query()->forceDelete();
        App\Models\InventoryMovement::query()->forceDelete();
        App\Models\OrderProduct::query()->forceDelete();
        App\Models\Order::query()->forceDelete();
        App\Models\OrderStatus::query()->forceDelete();
        App\Models\Configuration::query()->forceDelete();
        App\Models\OrderProductTotal::query()->forceDelete();
        App\Models\Heartbeat::query()->forceDelete();
        App\Models\Module::query()->forceDelete();
        App\Models\DataCollection::query()->forceDelete();
        App\Models\DataCollectionRecord::query()->forceDelete();
        App\Models\Warehouse::query()->forceDelete();
        App\Models\Session::query()->forceDelete();
        App\Models\Configuration::query()->forceDelete();
        App\Models\StocktakeSuggestion::query()->forceDelete();

        App\Modules\Automations\src\Models\Automation::query()->forceDelete();
        App\Modules\Automations\src\Models\Condition::query()->forceDelete();
        App\Modules\Automations\src\Models\Action::query()->forceDelete();
        App\Modules\Api2cart\src\Models\Api2cartProductLink::query()->forceDelete();
        App\Modules\Api2cart\src\Models\Api2cartConnection::query()->forceDelete();
        App\Modules\Rmsapi\src\Models\RmsapiConnection::query()->forceDelete();
        App\Modules\MagentoApi\src\Models\MagentoProduct::query()->forceDelete();
        App\Modules\MagentoApi\src\Models\MagentoConnection::query()->forceDelete();
        App\Modules\DpdIreland\src\Models\DpdIreland::query()->forceDelete();
        App\User::query()->forceDelete();

        App\Services\ModulesService::updateModulesTable();

        DB::table('modules_queue_monitor_jobs')->delete();

        App\Models\Configuration::query()->updateOrCreate([], ['disable_2fa' => true]);
        App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider::enableModule();
        App\Modules\InventoryReservations\src\EventServiceProviderBase::enableModule();
    }
}
