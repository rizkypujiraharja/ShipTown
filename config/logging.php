<?php

use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    'channels' => [
        'insightOps' => [
            'driver' => 'monolog',
            'level' => env('INSIGHTOPS_LOG_LEVEL'),
            'handler' => \Monolog\Handler\InsightOpsHandler::class,
            'handler_with' => [
                'region' => env('INSIGHTOPS_REGION'),
                'token' => env('INSIGHTOPS_TOKEN'),
            ],
        ],
    ],

];
