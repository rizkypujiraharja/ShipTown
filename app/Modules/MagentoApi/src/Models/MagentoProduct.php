<?php

namespace App\Modules\MagentoApi\src\Models;

use App\BaseModel;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property MagentoConnection $magentoConnection
 * @property Product $product
 * @property bool base_price_sync_required
 * @property bool special_price_sync_required
 * @property float $magento_price
 * @property float $magento_sale_price
 * @property Carbon $magento_sale_price_start_date
 * @property Carbon $magento_sale_price_end_date
 * @property Carbon $base_prices_fetched_at
 * @property array $base_prices_raw_import
 * @property Carbon $special_prices_fetched_at
 * @property array $special_prices_raw_import
 * @property ProductPrice $prices
 */
class MagentoProduct extends BaseModel
{
    use HasFactory;

    protected $table = 'modules_magento2api_products';

    protected $fillable = [
        'connection_id',
        'exists_in_magento',
        'product_price_id',
        'product_id',
        'magento_price',
        'magento_sale_price',
        'magento_sale_price_start_date',
        'magento_sale_price_end_date',
        'base_price_sync_required',
        'base_prices_fetched_at',
        'base_prices_raw_import',
        'special_price_sync_required',
        'special_prices_fetched_at',
        'special_prices_raw_import',
    ];

    protected function casts(): array
    {
        return [
            'exists_in_magento' => 'boolean',
            'base_price_sync_required' => 'boolean',
            'special_price_sync_required' => 'boolean',
            'magento_sale_price_start_date' => 'datetime',
            'magento_sale_price_end_date' => 'datetime',
            'stock_items_fetched_at' => 'datetime',
            'base_prices_fetched_at' => 'datetime',
            'special_prices_fetched_at' => 'datetime',
            'stock_items_raw_import' => 'array',
            'base_prices_raw_import' => 'array',
            'special_prices_raw_import' => 'array',
            'magento_sale_price' => 'float',
            'magento_price' => 'float',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function magentoConnection(): BelongsTo
    {
        return $this->belongsTo(MagentoConnection::class, 'connection_id');
    }

    public function prices(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class, 'product_price_id');
    }
}
