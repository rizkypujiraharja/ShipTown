<?php

namespace App\Modules\Rmsapi\src\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\RmsapiProductImport.
 *
 * @property int $id
 * @property int $connection_id
 * @property array $raw_import
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Eloquent
 */
class RmsapiShippingImports extends Model
{
    use HasFactory;

    protected $table = 'modules_rmsapi_shipping_imports';

    protected $fillable = [
        'connection_id',
        'raw_import',
    ];

    // we use attributes to set default values
    // we won't use database default values
    // as this is then not populated
    // correctly to events
    protected $attributes = [
        'raw_import' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'raw_import' => 'array',
        ];
    }
}
