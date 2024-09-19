<?php

namespace App\Modules\Magento2MSI\src\Models;

use App\BaseModel;
use App\Models\Warehouse;
use App\Traits\HasTagsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property string $api_access_token
 * @property string $base_url
 * @property string $magento_source_code
 * @property int $inventory_source_warehouse_tag_id
 */
class Magento2msiConnection extends BaseModel
{
    use HasFactory;
    use HasTagsTrait;

    protected $table = 'modules_magento2msi_connections';

    protected $fillable = [
        'base_url',
        'magento_source_code',
        'inventory_source_warehouse_tag_id',
        'api_access_token',
    ];

    protected $hidden = [
        'api_access_token',
    ];

    protected function casts(): array
    {
        return [
            'api_access_token' => 'encrypted',
        ];
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(self::class)
            ->allowedIncludes([
                'tags', 'warehouse',
            ]);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'pricing_source_warehouse_id', 'id');
    }
}
