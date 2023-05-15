<?php

namespace App\Modules\Telescope\src;

use App\Events\DailyEvent;
use App\Events\HourlyEvent;
use App\Modules\BaseModuleServiceProvider;

class TelescopeModuleServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Telescope';

    public static string $module_description = 'Telescope will help you debug your application';

    public static string $settings_link = '/telescope';

    public static bool $autoEnable = true;

    protected $listen = [
        HourlyEvent::class => [
            Listeners\HourlyEventListener::class
        ],
    ];
}