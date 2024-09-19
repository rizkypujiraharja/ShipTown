<?php

namespace App\Models;

use App\BaseModel;

/**
 * @property string $business_name
 * @property string $database_version
 * @property bool $ecommerce_connected
 * @property bool $disable_2fa
 */
class Configuration extends BaseModel
{
    protected $fillable = [
        'business_name',
        'database_version',
        'ecommerce_connected',
        'disable_2fa',
    ];

    protected function casts(): array
    {
        return [
            'ecommerce_connected' => 'boolean',
            'disable_2fa' => 'boolean',
        ];
    }
}
