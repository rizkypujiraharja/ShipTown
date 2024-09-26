<?php

namespace App\Modules\Reports\src;

use App\Events\EveryMinuteEvent;
use App\Modules\BaseModuleServiceProvider;
use Exception;

class ReportsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Reports';

    public static string $module_description = 'Provides reports under menu section';

    public static string $settings_link = '';

    public static bool $autoEnable = true;

    /**
     * @var array
     */
    protected $listen = [
        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEventListener::class,
        ],
    ];

    public static function disabling(): bool
    {
        return false;
    }

    /**
     * @throws Exception
     */
    public function boot()
    {
        parent::boot();

        $module_filename = 'reports';

        //        $this->publishes(
        //            [__DIR__. '/../config/' . $module_filename . '.php' => config_path($module_filename.'.php')],
        //            'config'
        //        );
        //
        //        $this->mergeConfigFrom(
        //            __DIR__.'/../config/' . $module_filename . '.php',
        //            $module_filename
        //        );

        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            $module_filename
        );

        //        if ($this->app->runningInConsole()) {
        //            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        //        }
    }
}
