<?php

namespace App\Modules\Rmsapi\src\Models;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductPrice;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\RmsapiProductImport.
 *
 * @property int $id
 * @property int $connection_id
 * @property string|null $batch_uuid
 * @property Carbon|null $reserved_at
 * @property Carbon|null $when_processed
 * @property int|null $product_id
 * @property int|null $rms_product_id
 * @property string|null $sku
 * @property string|null $name
 * @property array $raw_import
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property RmsapiConnection $rmsapiConnection
 * @property Product $product
 * @property Inventory $inventory
 * @property ProductPrice $prices
 *
 * @method static Builder|RmsapiProductImport newModelQuery()
 * @method static Builder|RmsapiProductImport newQuery()
 * @method static Builder|RmsapiProductImport query()
 * @method static Builder|RmsapiProductImport whereBatchUuid($value)
 * @method static Builder|RmsapiProductImport whereConnectionId($value)
 * @method static Builder|RmsapiProductImport whereCreatedAt($value)
 * @method static Builder|RmsapiProductImport whereId($value)
 * @method static Builder|RmsapiProductImport whereProductId($value)
 * @method static Builder|RmsapiProductImport whereRawImport($value)
 * @method static Builder|RmsapiProductImport whereSku($value)
 * @method static Builder|RmsapiProductImport whereUpdatedAt($value)
 * @method static Builder|RmsapiProductImport whereWhenProcessed($value)
 *
 * @mixin Eloquent
 */
class RmsapiProductImport extends Model
{
    use HasFactory;

    protected $table = 'modules_rmsapi_products_imports';

    protected $fillable = [
        'connection_id',
        'warehouse_id',
        'warehouse_code',
        'inventory_id',
        'product_id',
        'rms_product_id',
        'sku',
        'is_web_item',
        'quantity_on_hand',
        'quantity_committed',
        'quantity_available',
        'quantity_on_order',
        'reorder_point',
        'restock_level',
        'price',
        'cost',
        'sale_price',
        'sale_price_start_date',
        'sale_price_end_date',
        'department_name',
        'category_name',
        'sub_description_1',
        'sub_description_2',
        'sub_description_3',
        'supplier_name',
        'batch_uuid',
        'raw_import',
        'reserved_at',
        'processed_at',
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

    public function rmsapiConnection(): BelongsTo
    {
        return $this->belongsTo(RmsapiConnection::class, 'connection_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function prices(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class, 'product_price_id', 'id');
    }
}
