<?php

namespace App\Modules\Api2cart\src\Models;

use App\BaseModel;
use App\Models\Product;
use App\Modules\Api2cart\src\Services\Api2cartService;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Api2cartProductLink.
 *
 * @property int $id
 * @property int $product_id
 * @property Product $product
 * @property string|null $api2cart_product_type
 * @property string|null $api2cart_product_id
 * @property Carbon $last_fetched_at
 * @property array $last_fetched_data
 * @property Api2cartConnection $api2cartConnection
 * @property string|null $api2cart_connection_id
 * @property float|null $api2cart_quantity
 * @property float|null $api2cart_price
 * @property float|null $api2cart_sale_price
 * @property Carbon|null $api2cart_sale_price_start_date
 * @property Carbon|null $api2cart_sale_price_end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array raw_import
 * @property bool|null $is_in_sync
 *
 * @method static Builder|Api2cartProductLink newModelQuery()
 * @method static Builder|Api2cartProductLink newQuery()
 * @method static Builder|Api2cartProductLink query()
 * @method static Builder|Api2cartProductLink whereApi2cartConnectionId($value)
 * @method static Builder|Api2cartProductLink whereApi2cartPrice($value)
 * @method static Builder|Api2cartProductLink whereApi2cartProductId($value)
 * @method static Builder|Api2cartProductLink whereApi2cartProductType($value)
 * @method static Builder|Api2cartProductLink whereApi2cartQuantity($value)
 * @method static Builder|Api2cartProductLink whereApi2cartSalePrice($value)
 * @method static Builder|Api2cartProductLink whereApi2cartSalePriceEndDate($value)
 * @method static Builder|Api2cartProductLink whereApi2cartSalePriceStartDate($value)
 * @method static Builder|Api2cartProductLink whereCreatedAt($value)
 * @method static Builder|Api2cartProductLink whereId($value)
 * @method static Builder|Api2cartProductLink whereLastFetchedAt($value)
 * @method static Builder|Api2cartProductLink whereLastFetchedData($value)
 * @method static Builder|Api2cartProductLink whereProductId($value)
 * @method static Builder|Api2cartProductLink whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class Api2cartProductLink extends BaseModel
{
    use HasFactory;

    protected $table = 'modules_api2cart_product_links';

    /**
     * @var string[]
     */
    protected $fillable = [
        'is_in_sync',
        'product_id',
        'last_pushed_at',
        'last_pushed_response',
        'last_fetched_at',
        'api2cart_connection_id',
        'api2cart_product_type',
        'api2cart_product_id',
        'api2cart_quantity',
        'api2cart_price',
        'api2cart_sale_price',
        'api2cart_sale_price_start_date',
        'api2cart_sale_price_end_date',
        'last_fetched_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_price_start_date' => 'datetime',
            'sale_price_end_date' => 'datetime',
            'api2cart_product_id' => 'integer',
            'last_pushed_response' => 'array',
            'last_fetched_data' => 'array',
        ];
    }

    public function setLastFetchedDataAttribute($value)
    {
        $sprice_create = data_get($value, 'sprice_create', '2000-01-01 00:00:00');
        $sprice_expire = data_get($value, 'sprice_expire', '2000-01-01 00:00:00');

        $this->last_fetched_at = $value ? now() : null;
        $this->api2cart_quantity = data_get($value, 'quantity');
        $this->api2cart_price = data_get($value, 'price');
        $this->api2cart_sale_price = data_get($value, 'special_price');
        $this->api2cart_sale_price_start_date = Carbon::createFromTimeString($sprice_create)->format('Y-m-d H:i:s');
        $this->api2cart_sale_price_end_date = Carbon::createFromTimeString($sprice_expire)->format('Y-m-d H:i:s');

        $this->attributes['last_fetched_data'] = json_encode($value);
    }

    public function updateTypeAndId(): Api2cartProductLink
    {
        $store_key = $this->api2cartConnection->bridge_api_key;

        $response = Api2cartService::getProductTypeAndId($store_key, $this->product->sku);

        $this->api2cart_product_type = $response['type'];
        $this->api2cart_product_id = $response['id'];

        return $this;
    }

    public function api2cartConnection(): BelongsTo
    {
        return $this->belongsTo(Api2cartConnection::class, 'api2cart_connection_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
