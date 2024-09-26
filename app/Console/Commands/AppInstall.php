<?php

namespace App\Console\Commands;

use App\Events\AfterInstallEvent;
use App\Mail\OrderMail;
use App\Mail\ShipmentConfirmationMail;
use App\Mail\TransactionEmailReceiptMail;
use App\Models\Configuration;
use App\Models\MailTemplate;
use App\Models\NavigationMenu;
use App\Models\OrderStatus;
use App\Models\Warehouse;
use App\Modules;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\HasAnyShipmentCondition;
use App\Modules\Automations\src\Conditions\Order\IsFullyPackedCondition;
use App\Modules\Automations\src\Conditions\Order\IsFullyPaidCondition;
use App\Modules\Automations\src\Conditions\Order\IsFullyPickedCondition;
use App\Modules\Automations\src\Conditions\Order\LineCountEqualsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\AutoRestockLevels\src\AutoRestockLevelsServiceProvider;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovementsStatistics\src\InventoryMovementsStatisticsServiceProvider;
use App\Modules\InventoryQuantityIncoming\src\InventoryQuantityIncomingServiceProvider;
use App\Modules\InventoryReservations\src\EventServiceProviderBase;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\NonInventoryProductTag\src\NonInventoryProductTagServiceProvider;
use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use App\Modules\Slack\src\SlackServiceProvider;
use App\Modules\StocktakeSuggestions\src\StocktakeSuggestionsServiceProvider;
use App\Modules\Telescope\src\TelescopeModuleServiceProvider;
use App\Services\ModulesService;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the application and generates encryption keys';

    public function handle(): int
    {
        $configuration = Configuration::query()->firstOrCreate([], ['database_version' => '0.0.0']);

        if (version_compare($configuration->database_version, '0.0.0', '=')) {
            self::ensureAppKeysGenerated();
            $this->preconfigureDatabase();
            $configuration->update(['database_version' => '2.1.0']);
            $this->info('Application installed successfully');
        } else {
            $this->info('Application already installed');

            return 0;
        }

        AfterInstallEvent::dispatch();

        return 0;
    }

    private static function setVariable(string $key, string $value): void
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                $key.'='.env($key),
                $key.'='.$value,
                file_get_contents($path)
            ));
        }
    }

    private static function createPassportKeys(): void
    {
        Artisan::call('passport:keys', [
            '--force' => true,
        ]);

        self::setVariable('PASSPORT_PRIVATE_KEY', implode('', ['"', Storage::get('oauth-private.key'), '"']));
        self::setVariable('PASSPORT_PUBLIC_KEY', implode('', ['"', Storage::get('oauth-public.key'), '"']));
        unlink(storage_path('oauth-private.key'));
        unlink(storage_path('oauth-public.key'));

        info('Passport encryption keys generated successfully');
    }

    private function createDefaultNavigationLinks(): void
    {
        NavigationMenu::query()->create([
            'name' => 'Status: paid',
            'url' => '/picklist?order.status_code=paid',
            'group' => 'picklist',
        ]);

        NavigationMenu::query()->create([
            'name' => 'Status: picking',
            'url' => '/picklist?order.status_code=picking',
            'group' => 'picklist',
        ]);

        NavigationMenu::query()->create([
            'name' => 'Status: store_collection',
            'url' => '/picklist?order.status_code=store_collection',
            'group' => 'picklist',
        ]);

        NavigationMenu::query()->create([
            'name' => 'Status: paid',
            'url' => '/autopilot/packlist?status=paid',
            'group' => 'packlist',
        ]);

        NavigationMenu::query()->create([
            'name' => 'Status: packing',
            'url' => '/autopilot/packlist?status=packing',
            'group' => 'packlist',
        ]);

        NavigationMenu::query()->create([
            'name' => 'Status: single_line_orders',
            'url' => '/autopilot/packlist?status=single_line_orders',
            'group' => 'packlist',
        ]);

        NavigationMenu::query()->create([
            'name' => 'Status: store_collection',
            'url' => '/autopilot/packlist?status=store_collection',
            'group' => 'packlist',
        ]);
    }

    private function createNewToPaidAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"new" to "paid"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'new',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPaidCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'paid',
        ]);

        $automation->update(['enabled' => true]);
    }

    public function preconfigureDatabase(): void
    {
        $this->createDefaultUserRoles();
        $this->createDefaultNavigationLinks();
        $this->createShipmentConfirmationNotificationTemplate();
        $this->createReadyForCollectionNotificationTemplate();
        $this->createDefaultMailTemplateShipmentNotification();
        $this->createDefaultMailTemplateOversoldProduct();
        $this->createDefaultTransactionEmailReceiptMailTemplate();

        $this->createDefaultOrderStatuses();

        $this->createNewToPaidAutomation();
        $this->createNewToUnpaidAutomation();
        $this->createUnpaidToPaidAutomation();
        //        $this->createPaidToPickingAutomation();
        $this->createPickingToPackingAutomation();
        $this->createPackingToShippedAutomation();
        $this->createStoreCollectionToReadyToCollect();
        $this->createPickedToCompleteAutomation();
        $this->createPaidToCompleteAutomation();
        $this->createPaidToSingleLineOrdersAutomation();
        $this->createPackingToCompleteAutomation();
        $this->createSingleLineOrdersToCompleteAutomation();

        ModulesService::updateModulesTable();

        Configuration::query()->firstOrCreate();

        Warehouse::query()->insert(['code' => 'WH', 'name' => 'Warehouse']);

        StocktakeSuggestionsServiceProvider::installModule();
        AutoRestockLevelsServiceProvider::installModule();
        InventoryQuantityIncomingServiceProvider::installModule();
        DataCollectorServiceProvider::installModule();
        NonInventoryProductTagServiceProvider::installModule();
        QueueMonitorServiceProvider::installModule();
        TelescopeModuleServiceProvider::installModule();
        InventoryMovementsStatisticsServiceProvider::installModule();
        SlackServiceProvider::installModule();
        EventServiceProviderBase::installModule();
        InventoryMovementsServiceProvider::installModule();

        // misc modules
        Modules\Maintenance\src\EventServiceProviderBase::installModule();
        Modules\SystemHeartbeats\src\SystemHeartbeatsServiceProvider::installModule();
        Modules\StockControl\src\StockControlServiceProvider::installModule();
        Modules\OrderTotals\src\OrderTotalsServiceProvider::installModule();
        Modules\OrderStatus\src\OrderStatusServiceProvider::installModule();
        Modules\FireActiveOrderCheckEvent\src\ActiveOrderCheckEventServiceProvider::installModule();

        Modules\InventoryReservations\src\EventServiceProviderBase::installModule();
        Modules\InventoryTotals\src\InventoryTotalsServiceProvider::installModule();
        Modules\Automations\src\AutomationsServiceProvider::installModule();
        Modules\Reports\src\ReportsServiceProvider::installModule();

        // Automations modules
        // order MIGHT be important!
        Modules\AutoPilot\src\AutoPilotServiceProvider::installModule();
        Modules\AutoTags\src\EventServiceProviderBase::installModule();
        Modules\OversoldProductNotification\src\OversoldProductNotificationServiceProvider::installModule();

        // AutoStatus modules
        // order is important!
        Modules\AutoStatusPicking\src\AutoStatusPickingServiceProvider::installModule();

        // 3rd party integrations
        // order SHOULD not be important
        Modules\Webhooks\src\WebhooksServiceProviderBase::installModule();
        Modules\Api2cart\src\Api2cartServiceProvider::installModule();
        Modules\Rmsapi\src\RmsapiModuleServiceProvider::installModule();
        Modules\MagentoApi\src\EventServiceProviderBase::installModule();
        Modules\ScurriAnpost\src\ScurriServiceProvider::installModule();
        Modules\DpdUk\src\DpdUkServiceProvider::installModule();
        Modules\AddressLabel\src\AddressLabelServiceProvider::installModule();
        Modules\DpdIreland\src\DpdIrelandServiceProvider::installModule();
        Modules\PrintNode\src\PrintNodeServiceProvider::installModule();

        StocktakeSuggestionsServiceProvider::enableModule();
        InventoryMovementsStatisticsServiceProvider::enableModule();
        EventServiceProviderBase::enableModule();
        InventoryMovementsServiceProvider::enableModule();
        InventoryTotalsServiceProvider::enableModule();
        InventoryMovementsServiceProvider::enableModule();
    }

    public static function ensureAppKeysGenerated(): void
    {
        if (env('PASSPORT_PRIVATE_KEY', '') === '') {
            info('Generating passport keys');
            self::createPassportKeys();
        }

        if (env('APP_KEY', '') === '') {
            info('Generating application key');
            Artisan::call('key:generate');
        }
    }

    private function createPaidToPickingAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"paid" to "picking"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid',
        ]);

        // todo - what other conditions should be here? If no others then this automation is redundant and we could just go new to picking

        $automation->conditions()->create([
            'condition_class' => IsFullyPickedCondition::class,
            'condition_value' => 'False',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'picking',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createPickingToPackingAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"picking" to "packing"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'picking',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPickedCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'packing',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createPackingToShippedAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"packing" to "shipped"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'packing',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPackedCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->conditions()->create([
            'condition_class' => HasAnyShipmentCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'shipped',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createPaidToCompleteAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"paid" to "complete"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPackedCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->conditions()->create([
            'condition_class' => HasAnyShipmentCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'complete',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createPickedToCompleteAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"picked" to "complete"',
            'priority' => 91,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'picked',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPackedCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->conditions()->create([
            'condition_class' => HasAnyShipmentCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'complete',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createPaidToPickedAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"paid" to "packing"',
            'priority' => 10,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPickedCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'packing',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createDefaultUserRoles(): void
    {
        Role::findOrCreate('user');
        $admin = Role::findOrCreate('admin');

        $defaultAdminPermissions = ['manage users', 'list users', 'invite users', 'list roles'];

        foreach ($defaultAdminPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            $admin->givePermissionTo($permission);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createDefaultMailTemplateShipmentNotification(): void
    {
        MailTemplate::create([
            'mailable' => ShipmentConfirmationMail::class,
            'code' => 'module_shipment_confirmation',
            'subject' => 'Your Order #{{ variables.order.order_number }} has been Shipped!',
            'html_template' => '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns="http://www.w3.org/1999/xhtml"
          style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>We shipped your order!</title>

        <style>img {
            max-width: 100%;
        }
        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6;
        }
        body {
            background-color: #f6f6f6;
        }
        @media only screen and (max-width: 640px) {
            h1 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h2 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h3 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h4 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h1 {
                font-size: 22px !important;
            }
            h2 {
                font-size: 18px !important;
            }
            h3 {
                font-size: 16px !important;
            }
            .container {
                width: 100% !important;
            }
            .content {
                padding: 10px !important;
            }
            .content-wrapper {
                padding: 10px !important;
            }
            .invoice {
                width: 100% !important;
            }
        }
        </style></head>

    <body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">

    <table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
            <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
                <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                    <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <!-------- logo -------->
    <!--                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">-->
    <!--                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">-->
    <!--                                        <a href="" target="_blank">-->
    <!--                                            <img src="" alt="logo">-->
    <!--                                        </a>-->
    <!--                                    </td>-->
    <!--                                </tr>-->
                                    <!-------- logo -------->
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                                We shipped your order!<br>
                                                #{{ variables.order.order_number }}
                                            </h2>
                                        </td>
                                    </tr>
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 80%; margin: 40px auto; padding: 0;">
                                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                        Tracking Information:<br style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" />
                                                    </td>
                                                </tr>
                                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                        <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">

                                                            {{#variables.shipments}}

                                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                    {{ carrier }}
                                                                </td>
                                                                <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                    <a href="{{ tracking_url }}" target="_blank">
                                                                        {{ shipping_number }}
                                                                    </a>
                                                                </td>
                                                            </tr>

                                                            {{/variables.shipments}}
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <p>
                                                Please note that any tracking information above <br>
                                                may not update until this evening.
                                            </p>
                                            <p>
                                                If you have any questions, please feel free <br>
                                                to email us at <a href="mailto:support@ship.town?subject=Order #{{ variables.order.order_number }} Enquiry">no-reply@products.management</a></br>
                                                or call us on <b>+353 (1) 1234567</b><br>
                                            </p>
                                            <p>Thank you again for your business.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                        <table width="100%" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                <td class="aligncenter content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                    Questions? Email
                                    <a href="mailto:" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0; padding: 0;">
                                        no-reply@products.management
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div></div>
            </td>
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
        </tr>
    </table>

    </body>
    </html>
        ',
        ]);
    }

    public function createDefaultMailTemplateOversoldProduct(): void
    {
        MailTemplate::create([
            'mailable' => \App\Mail\OversoldProductMail::class,
            'code' => 'module_oversold_product_mail',
            'subject' => 'Product Oversold - ({{ variables.product.name }})',
            'html_template' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Product Oversold!</title>

    <style>img {
        max-width: 100%;
    }
    body {
        -webkit-font-smoothing: antialiased;
        -webkit-text-size-adjust: none;
        width: 100% !important;
        height: 100%;
        line-height: 1.6;
    }
    body {
        background-color: #f6f6f6;
    }
    @media only screen and (max-width: 640px) {
        h1 {
            font-weight: 600 !important; margin: 20px 0 5px !important;
        }
        h2 {
            font-weight: 600 !important; margin: 20px 0 5px !important;
        }
        h3 {
            font-weight: 600 !important; margin: 20px 0 5px !important;
        }
        h4 {
            font-weight: 600 !important; margin: 20px 0 5px !important;
        }
        h1 {
            font-size: 22px !important;
        }
        h2 {
            font-size: 18px !important;
        }
        h3 {
            font-size: 16px !important;
        }
        .container {
            width: 100% !important;
        }
        .content {
            padding: 10px !important;
        }
        .content-wrapper {
            padding: 10px !important;
        }
        .invoice {
            width: 100% !important;
        }
    }
    </style></head>

<body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">

<table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
        <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
        <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
            <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                        <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                            <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                <!-------- logo -------->
<!--                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">-->
<!--                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">-->
<!--                                        <a href="" target="_blank">-->
<!--                                            <img src="" alt="logo">-->
<!--                                        </a>-->
<!--                                    </td>-->
<!--                                </tr>-->
                                <!-------- logo -------->
                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                        <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                            Oversold Product Detected<br>
                                        </h2>
                                    </td>
                                </tr>
                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                        <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 80%; margin: 40px auto; padding: 0;">
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                    Product Information:<br style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" />
                                                </td>
                                            </tr>
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                    <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">

                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                SKU
                                                            </td>
                                                            <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                <a href="/products?search={{ variables.product.sku }}">{{ variables.product.sku }}</a>
                                                            </td>
                                                        </tr>

                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                Name
                                                            </td>
                                                            <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                {{ variables.product.name }}
                                                            </td>
                                                        </tr>

                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                        <p>
                                            <!-------- footer -------->
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="footer" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                    <table width="100%" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <td class="aligncenter content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                Questions? Email
                                <a href="mailto:" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0; padding: 0;">
                                    support@products.management
                                </a>
                            </td>
                        </tr>
                    </table>
                </div></div>
        </td>
        <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
    </tr>
</table>

</body>
</html>',
        ]);
    }

    private function createShipmentConfirmationNotificationTemplate()
    {
        MailTemplate::query()->firstOrCreate(['code' => 'shipment_confirmation'], [
            'mailable' => OrderMail::class,
            'subject' => 'Your Order #{{ variables.order.order_number }} has been Shipped!',
            'html_template' => '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns="http://www.w3.org/1999/xhtml"
          style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>We shipped your order!</title>

        <style>img {
            max-width: 100%;
        }
        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6;
        }
        body {
            background-color: #f6f6f6;
        }
        @media only screen and (max-width: 640px) {
            h1 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h2 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h3 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h4 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h1 {
                font-size: 22px !important;
            }
            h2 {
                font-size: 18px !important;
            }
            h3 {
                font-size: 16px !important;
            }
            .container {
                width: 100% !important;
            }
            .content {
                padding: 10px !important;
            }
            .content-wrapper {
                padding: 10px !important;
            }
            .invoice {
                width: 100% !important;
            }
        }
        </style></head>

    <body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">

    <table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
            <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
                <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                    <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <!-------- logo -------->
    <!--                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">-->
    <!--                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">-->
    <!--                                        <a href="" target="_blank">-->
    <!--                                            <img src="" alt="logo">-->
    <!--                                        </a>-->
    <!--                                    </td>-->
    <!--                                </tr>-->
                                    <!-------- logo -------->
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                                We shipped your order!<br>
                                                #{{ variables.order.order_number }}
                                            </h2>
                                        </td>
                                    </tr>
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 80%; margin: 40px auto; padding: 0;">
                                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                        Tracking Information:<br style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" />
                                                    </td>
                                                </tr>
                                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                        <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">

                                                            {{#variables.shipments}}

                                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                    {{ carrier }}
                                                                </td>
                                                                <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                    <a href="{{ tracking_url }}" target="_blank">
                                                                        {{ shipping_number }}
                                                                    </a>
                                                                </td>
                                                            </tr>

                                                            {{/variables.shipments}}
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <p>
                                                Please note that any tracking information above <br>
                                                may not update until this evening.
                                            </p>
                                            <p>
                                                If you have any questions, please feel free <br>
                                                to email us at <a href="mailto:support@ship.town?subject=Order #{{ variables.order.order_number }} Enquiry">no-reply@products.management</a></br>
                                                or call us on <b>+353 (1) 1234567</b><br>
                                            </p>
                                            <p>Thank you again for your business.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                        <table width="100%" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                <td class="aligncenter content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                    Questions? Email
                                    <a href="mailto:" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0; padding: 0;">
                                        no-reply@products.management
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div></div>
            </td>
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
        </tr>
    </table>

    </body>
    </html>
        ',
        ]);
    }

    private function createDefaultTransactionEmailReceiptMailTemplate(): void
    {
        MailTemplate::query()->firstOrCreate(['code' => 'transaction_email_receipt'], [
            'mailable' => TransactionEmailReceiptMail::class,
            'subject' => 'You just completed a #{{ variables.transaction.id }} transaction! Here is your receipt.',
            'html_template' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns="http://www.w3.org/1999/xhtml"
          style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>You just completed a transaction! Here is your receipt.</title>

        <style>img {
            max-width: 100%;
        }
        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6;
        }
        body {
            background-color: #f6f6f6;
        }
        @media only screen and (max-width: 640px) {
            h1 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h2 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h3 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h4 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h1 {
                font-size: 22px !important;
            }
            h2 {
                font-size: 18px !important;
            }
            h3 {
                font-size: 16px !important;
            }
            .container {
                width: 100% !important;
            }
            .content {
                padding: 10px !important;
            }
            .content-wrapper {
                padding: 10px !important;
            }
            .invoice {
                width: 100% !important;
            }
        }
        </style></head>

        <body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">
            <table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
                    <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
                        <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                            <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                                        <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                            <!-------- main message -------->
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                                    <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                                        Thanks for your order!
                                                    </h2>
                                                    <p style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 14px; color: #000; line-height: 1.2; font-weight: 400; margin: 20px 0 0; padding: 0;">
                                                        Order: <strong>#{{ variables.transaction.id }}</strong> | Date ordered: <strong>{{ variables.transaction.created_at }}</strong>
                                                    </p>
                                                </td>
                                            </tr>

                                            <!-------- addresses -------->
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top">
                                                    <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; margin: 20px auto 0; padding: 0;">
                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                                <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">
                                                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                        <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 20px 0 0;" valign="top">
                                                                            <p style="font-weight: 500;">Billing Address:</p>
                                                                            <p>{{ variables.billing_address.gender }} {{ variables.billing_address.first_name }} {{ variables.billing_address.last_name }}</p>
                                                                            <p>{{ variables.billing_address.company }}</p>
                                                                            <p>{{ variables.billing_address.address1 }}</p>
                                                                            <p>{{ variables.billing_address.address2 }}</p>
                                                                            <p>{{ variables.billing_address.city }} {{ variables.billing_address.postcode }}</p>
                                                                            <p>{{ variables.billing_address.country_name }}</p>
                                                                        </td>
                                                                        <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                            <p style="font-weight: 500;">Shipping Address:</p>
                                                                            <p>{{ variables.shipping_address.gender }} {{ variables.shipping_address.first_name }} {{ variables.shipping_address.last_name }}</p>
                                                                            <p>{{ variables.shipping_address.company }}</p>
                                                                            <p>{{ variables.shipping_address.address1 }}</p>
                                                                            <p>{{ variables.shipping_address.address2 }}</p>
                                                                            <p>{{ variables.shipping_address.city }} {{ variables.shipping_address.postcode }}</p>
                                                                            <p>{{ variables.shipping_address.country_name }}</p>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="border-top-width: 1px; border-top-color: #eee; border-top-style: solid;"></td>
                                            </tr>

                                            <!-------- products -------->
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                                    <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 100%; margin: 20px auto; padding: 0;">
                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                                Products:
                                                            </td>
                                                        </tr>
                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                                <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">
                                                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                        <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                                            <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">
                                                                                <thead style="text-align:center;">
                                                                                    <tr>
                                                                                        <th>Product</th>
                                                                                        <th>Quantity</th>
                                                                                        <th>Price</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody style="text-align:center;">
                                                                                    {{#variables.products}}
                                                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                                                {{name}}
                                                                                            </td>
                                                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                                                {{quantity}}
                                                                                            </td>
                                                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                                                {{price}}
                                                                                            </td>
                                                                                        </tr>
                                                                                    {{/variables.products}}
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="border-top-width: 1px; border-top-color: #eee; border-top-style: solid;"></td>
                                            </tr>

                                            <!-------- summary -------->
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                                    <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; margin: 20px 0 0 auto; padding: 0;">
                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                                <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">
                                                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                        <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; padding: 5px 10px 5px 0;" valign="top">
                                                                            Subtotal
                                                                        </td>
                                                                        <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                            {{ variables.transaction.subtotal }}
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                        <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 10px 5px 0;" valign="top">
                                                                            Shipping
                                                                        </td>
                                                                        <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                            {{ variables.transaction.shipping }}
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                        <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 10px 5px 0;" valign="top">
                                                                            Tax
                                                                        </td>
                                                                        <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                            {{ variables.transaction.tax }}
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                        <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 10px 5px 0;" valign="top">
                                                                            <strong>Total</strong>
                                                                        </td>
                                                                        <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                            <strong>{{ variables.transaction.total }}</strong>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="border-top-width: 1px; border-top-color: #eee; border-top-style: solid;"></td>
                                            </tr>

                                            <!-------- footer -------->
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                                    <p>
                                                        If you have any questions, please feel free <br>
                                                        to email us at <a href="mailto:support@ship.town?subject=Order #{{ variables.order.order_number }} Enquiry">no-reply@products.management</a></br>
                                                        or call us on <b>+353 (1) 1234567</b><br>
                                                    </p>
                                                    <p>Thank you again for your business.</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ',
        ]);
    }

    private function createReadyForCollectionNotificationTemplate()
    {
        MailTemplate::query()->firstOrCreate(['code' => 'ready_for_collection_notification'], [
            'mailable' => OrderMail::class,
            'subject' => 'Your Order #{{ variables.order.order_number }} is ready for Collection!',
            'html_template' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns="http://www.w3.org/1999/xhtml"
          style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" c\ontent="text/html; charset=UTF-8" />
        <title>We shipped your order!</title>

        <style>img {
            max-width: 100%;
        }
        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6;
        }
        body {
            background-color: #f6f6f6;
        }
        @media only screen and (max-width: 640px) {
            h1 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h2 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h3 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h4 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h1 {
                font-size: 22px !important;
            }
            h2 {
                font-size: 18px !important;
            }
            h3 {
                font-size: 16px !important;
            }
            .container {
                width: 100% !important;
            }
            .content {
                padding: 10px !important;
            }
            .content-wrapper {
                padding: 10px !important;
            }
            .invoice {
                width: 100% !important;
            }
        }
        </style></head>

    <body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">

    <table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
            <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
                <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                    <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <!-------- logo -------->
    <!--                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">-->
    <!--                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">-->
    <!--                                        <a href="" target="_blank">-->
    <!--                                            <img src="" alt="logo">-->
    <!--                                        </a>-->
    <!--                                    </td>-->
    <!--                                </tr>-->
                                    <!-------- logo -------->

                                    <!-------- main message -------->
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                                Your order is ready for collection!<br>
                                                #{{ variables.order.order_number }}
                                            </h2>
                                        </td>
                                    </tr>

                                    <!-------- footer -------->
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <p>
                                                If you have any questions, please feel free <br>
                                                to email us at <a href="mailto:support@ship.town?subject=Order #{{ variables.order.order_number }} Enquiry">no-reply@products.management</a></br>
                                                or call us on <b>+353 (1) 1234567</b><br>
                                            </p>
                                            <p>Thank you again for your business.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                        <table width="100%" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                <td class="aligncenter content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                    Questions? Email
                                    <a href="mailto:" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0; padding: 0;">
                                        no-reply@products.management
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div></div>
            </td>
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
        </tr>
    </table>

    </body>
    </html>
        ',
        ]);
    }

    private function createPaidToSingleLineOrdersAutomation()
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"paid" to "single_line_orders"',
            'priority' => 10,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid',
        ]);

        $automation->conditions()->create([
            'condition_class' => LineCountEqualsCondition::class,
            'condition_value' => '1',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'single_line_orders',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createPackingToCompleteAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"packing" to "complete"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'packing',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPackedCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->conditions()->create([
            'condition_class' => HasAnyShipmentCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'complete',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createNewToUnpaidAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"new" to "unpaid"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'new',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPaidCondition::class,
            'condition_value' => 'False',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'unpaid',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createUnpaidToPaidAutomation()
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"unpaid" to "new"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'unpaid',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPaidCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'paid',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createDefaultOrderStatuses(): void
    {
        OrderStatus::query()->updateOrCreate([
            'code' => 'canceled',
        ], [
            'name' => 'canceled',
            'order_on_hold' => false,
            'order_active' => false,
        ]);

        OrderStatus::query()->updateOrCreate([
            'code' => 'unpaid',
        ], [
            'name' => 'unpaid',
            'order_on_hold' => true,
            'order_active' => true,
        ]);
    }

    private function createStoreCollectionToReadyToCollect(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"store_collection" to "ready_for_collection"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'store_collection',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPackedCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->conditions()->create([
            'condition_class' => HasAnyShipmentCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'ready_for_collection',
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createSingleLineOrdersToCompleteAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"single_line_orders" to "complete"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'single_line_orders',
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPackedCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->conditions()->create([
            'condition_class' => HasAnyShipmentCondition::class,
            'condition_value' => 'True',
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'complete',
        ]);

        $automation->update(['enabled' => true]);
    }
}
