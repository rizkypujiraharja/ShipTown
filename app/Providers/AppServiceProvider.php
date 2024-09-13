<?php

namespace App\Providers;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryReservation;
use App\Models\Module;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductShipment;
use App\Models\OrderShipment;
use App\Models\OrderStatus;
use App\Models\Pick;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ShippingLabel;
use App\Models\Warehouse;
use App\Modules\PrintNode\src\Models\PrintJob;
use App\Observers\DataCollectionObserver;
use App\Observers\DataCollectionRecordObserver;
use App\Observers\InventoryMovementObserver;
use App\Observers\InventoryObserver;
use App\Observers\InventoryReservationObserver;
use App\Observers\ModuleObserver;
use App\Observers\OrderObserver;
use App\Observers\OrderProductObserver;
use App\Observers\OrderProductShipmentObserver;
use App\Observers\OrderShipmentObserver;
use App\Observers\OrderStatusObserver;
use App\Observers\PickObserver;
use App\Observers\PrintJobObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductPriceObserver;
use App\Observers\ShippingLabelObserver;
use App\Observers\WarehouseObserver;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Telescope\Telescope;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        parent::register();

        Passport::ignoreMigrations();
        Telescope::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerEnabledModules();

        // Core Models
        Product::observe(ProductObserver::class);
        ProductPrice::observe(ProductPriceObserver::class);
        Inventory::observe(InventoryObserver::class);
        InventoryReservation::observe(InventoryReservationObserver::class);
        Order::observe(OrderObserver::class);
        OrderProduct::observe(OrderProductObserver::class);
        OrderShipment::observe(OrderShipmentObserver::class);
        OrderStatus::observe(OrderStatusObserver::class);
        OrderProductShipment::observe(OrderProductShipmentObserver::class);
        Warehouse::observe(WarehouseObserver::class);
        Module::observe(ModuleObserver::class);
        InventoryMovement::observe(InventoryMovementObserver::class);
        ShippingLabel::observe(ShippingLabelObserver::class);
        DataCollection::observe(DataCollectionObserver::class);
        DataCollectionRecord::observe(DataCollectionRecordObserver::class);
        Pick::observe(PickObserver::class);
        PrintJob::observe(PrintJobObserver::class);
    }

    private function registerEnabledModules(): void
    {
        try {
            $enabled_modules = Module::query()
                ->where(['enabled' => true])
                ->get();
        } catch (Exception $exception) {
            return;
        }

        $enabled_modules->each(function (Module $module) {
            try {
                if (! class_exists($module->service_provider_class)) {
                    return;
                }
                $this->app->singleton($module->service_provider_class, $module->service_provider_class);
                App::register($module->service_provider_class);
                $module->service_provider_class::loaded();
            } catch (Exception $exception) {
                return;
            }
        });
    }
}
