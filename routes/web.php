<?php

/*
|--------------------------------------------------------------------------
| User Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Admin\Settings\Modules\Webhooks\SubscriptionController;
use App\Http\Controllers\Api\BarcodeGeneratorController;
use App\Http\Controllers\Api\QuantityDiscountsController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Csv;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataCollectorController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FulfillmentDashboardController;
use App\Http\Controllers\MailTemplatePreviewController;
use App\Http\Controllers\Order;
use App\Http\Controllers\PdfOrderController;
use App\Http\Controllers\ProductsMergeController;
use App\Http\Controllers\Reports;
use App\Http\Controllers\ShippingLabelController;
use App\Modules\Magento2MSI\src\Http\Controllers\InventorySourceItemsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login')->middleware('guest');
Route::redirect('home', '/')->name('home');

Route::middleware('auth')->group(function () {
    Route::resource('verify', Auth\TwoFactorController::class)->only(['index', 'store']);
    Route::view('quick-connect', 'quick-connect');
    Route::view('quick-connect/magento', 'quick-connect.magento');
    Route::view('quick-connect/shopify', 'quick-connect.shopify');

    Route::apiResource('barcode-generator', BarcodeGeneratorController::class)->only(['index']);
    Route::resource('documents', DocumentController::class, ['index'])->only(['index']);

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('fulfillment-dashboard', [FulfillmentDashboardController::class, 'index'])->name('fulfillment-dashboard');
    Route::get('inventory-dashboard', [Reports\InventoryDashboardController::class, 'index'])->name('inventory-dashboard');
    Route::get('products-merge', [ProductsMergeController::class, 'index'])->name('products-merge');
    Route::view('fulfillment-statistics', 'fulfillment-statistics')->name('fulfillment-statistics');
    Route::view('products', 'products')->name('products');
    Route::view('picklist', 'picklist')->name('picklist');
    Route::view('orders', 'orders')->name('orders');
    Route::view('stocktaking', 'stocktaking')->name('stocktaking');
    Route::view('setting-profile', 'setting-profile')->name('setting-profile');
    Route::view('data-collector', 'data-collector-list')->name('data-collector');
    Route::get('data-collector/{data_collection_id}', [DataCollectorController::class, 'index'])->name('data-collector-show');
    Route::get('shipping-labels/{shipping_label}', [ShippingLabelController::class, 'show'])->name('shipping-labels');
    Route::view('autopilot/packlist', 'autopilot/packlist')->name('autopilot.packlist');
    Route::resource('order/packsheet', Order\PacksheetController::class)->only(['show']);
    Route::view('tools/printer', 'tools/printer')->name('tools.printer');

    Route::as('tools.')->group(function () {
        Route::view('tools/data-collector/transaction', 'tools/data-collector/transaction')->name('point_of_sale');
    });

    Route::as('reports.')->group(function () {
        Route::resource('reports/activity-log', Reports\ActivityLogController::class)->only('index');
        Route::resource('reports/inventory', Reports\InventoryController::class)->only('index');
        Route::resource('reports/stocktake-suggestions', Reports\StocktakeSuggestionsController::class)->only('index');
        Route::resource('reports/inventory-dashboard', Reports\InventoryDashboardController::class)->only('index');
        Route::resource('reports/picks', Reports\PickController::class)->only('index');
        Route::resource('reports/shipments', Reports\ShipmentController::class)->only('index');
        Route::resource('reports/restocking', Reports\RestockingReportController::class)->only('index');
        Route::resource('reports/stocktake-suggestions-totals', Reports\StocktakeSuggestionsTotalsReportController::class)->only('index');
        Route::resource('reports/inventory-movements-summary', Reports\InventoryMovementsSummaryController::class)->only('index');
        Route::resource('reports/inventory-movements', Reports\InventoryMovementController::class)->only('index');
        Route::resource('reports/inventory-transfers', Reports\InventoryTransfersController::class)->only('index');
        Route::resource('reports/order', Reports\OrderController::class)->only('index');
    });

    Route::get('pdf/orders/{order_number}/{template}', [PdfOrderController::class, 'show']);
    Route::get('csv/ready_order_shipments', [Csv\ReadyOrderShipmentController::class, 'index'])->name('ready_order_shipments_as_csv');
    Route::get('csv/order_shipments', [Csv\PartialOrderShipmentController::class, 'index'])->name('partial_order_shipments_as_csv');
    Route::get('csv/products/picked', [Csv\ProductsPickedInWarehouse::class, 'index'])->name('warehouse_picks.csv');
    Route::get('csv/products/shipped', [Csv\ProductsShippedFromWarehouseController::class, 'index'])->name('warehouse_shipped.csv');
    Route::get('csv/boxtop/stock', [Csv\BoxTopStockController::class, 'index'])->name('boxtop-warehouse-stock.csv');
    Route::view('settings/warehouses', 'settings/warehouses')->name('settings.warehouses');

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('settings/modules/magento2msi/inventory-source-items', InventorySourceItemsController::class)->only(['index']);

        Route::view('settings/modules/magento2msi', 'settings/modules/magento2msi');
        Route::view('settings/modules/stocktake-suggestions', 'settings/modules/stocktake-suggestions');
        Route::view('settings/modules/active-orders-inventory-reservations', 'settings/modules/active-orders-inventory-reservations');

        Route::view('settings', 'settings')->name('settings');
        Route::view('settings/general', 'settings/general')->name('settings.general');
        Route::view('settings/order-statuses', 'settings/order-statuses')->name('settings.order_statuses');
        Route::view('settings/printnode', 'settings/printnode')->name('settings.printnode');
        Route::view('settings/rmsapi', 'settings/rmsapi')->name('settings.rmsapi');
        Route::view('settings/dpd-ireland', 'settings/dpd-ireland')->name('settings.dpd-ireland');
        Route::view('settings/api2cart', 'settings/api2cart')->name('settings.api2cart');
        Route::view('settings/api', 'settings/api')->name('settings.api');
        Route::view('settings/users', 'settings/users')->name('settings.users');
        Route::view('settings/mail-templates', 'settings/mail-templates')->name('settings.mail_templates');
        Route::get('settings/mail-templates/{mailTemplate}/preview', [MailTemplatePreviewController::class, 'index'])->name('settings.mail_template_preview');
        Route::view('settings/navigation-menu', 'settings/navigation-menu')->name('settings.navigation_menu');
        Route::view('settings/automations', 'settings/automations')->name('settings.automations');
        Route::view('settings/modules', 'settings/modules')->name('settings.modules');
        Route::view('settings/modules/dpd-uk', 'settings/dpd-uk')->name('settings.modules.dpd-uk');
        Route::get('settings/modules/webhooks/subscriptions', [SubscriptionController::class, 'index'])->name('webhooks::subscriptions');
        Route::view('modules/slack/config', 'modules/slack/config')->name('modules.slack.config');
        Route::view('settings/modules/magento-api', 'settings/magento-api')->name('settings.modules.magento-api');
        Route::view('settings/modules/quantity-discounts', 'settings/modules/quantity-discounts/index')->name('settings.modules.quantity-discounts.index');
        Route::get('settings/modules/quantity-discounts/{id}', [QuantityDiscountsController::class, 'edit'])->name('settings.modules.quantity-discounts.edit');
    });
});
