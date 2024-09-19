<?php

use Illuminate\Support\Facades\Facade;

return [

    'license_valid_until' => env('APP_LICENSE_VALID_UNTIL', '2025-06-01 00:00:00'),

    /*
     *  Enables demo mode
     *  This will display a demo message on login page containing the demo credentials
     */
    'demo_mode' => env('DEMO_MODE', false),

    /*
     * Prefix used for all SNS topics
     * This is used to have topics per tenant subdomain
     * ie democompany_orders_events
     */
    'tenant_name' => env('TENANT_NAME', 'demo'),

    'sns_topic_prefix' => '',

    /*
     * API2CART application API key
     */
    'api2cart_api_key' => env('API2CART_API_KEY', ''),


    'aliases' => Facade::defaultAliases()->merge([
        'AWS' => Aws\Laravel\AwsFacade::class,
        'DNS1D' => Milon\Barcode\Facades\DNS1DFacade::class,
        'DNS2D' => Milon\Barcode\Facades\DNS2DFacade::class,
        'PDF' => Barryvdh\DomPDF\Facade\Pdf::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Sentry' => Sentry\Laravel\Facade::class,
    ])->toArray(),

];
