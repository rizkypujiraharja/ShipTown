<?php

namespace App\Models;

use App\BaseModel;
use App\Traits\LogsActivityTrait;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\OrderProduct.
 *
 * @property int $id
 * @property int|null $custom_unique_reference_id
 * @property int|null $order_id
 * @property int|null $product_id
 * @property string $sku_ordered
 * @property string $name_ordered
 * @property bool $is_shipped
 * @property float $price
 * @property float $quantity_ordered
 * @property float $quantity_split
 * @property float $total_price
 * @property float $quantity_shipped
 * @property float $quantity_to_ship
 * @property float $quantity_to_pick
 * @property float $quantity_picked
 * @property float $quantity_skipped_picking
 * @property float $quantity_not_picked
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Order $order
 * @property-read Product $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct addInventorySource($inventory_location_id)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct minimumShelfLocation($currentLocation)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereHasStockReserved($statusCodeArray)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereQuantityOutstanding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct createdBetween($min, $max)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereStatusCodeNotIn($statusCodeArray)
 *
 * @mixin Eloquent
 */
class OrderProduct extends BaseModel
{
    use HasFactory;
    use LogsActivityTrait;
    use SoftDeletes;

    protected $table = 'orders_products';

    /**
     * @var string[]
     */
    protected $touches = ['order'];

    protected static array $logAttributes = [
        'price',
        'quantity_ordered',
        'quantity_split',
        'quantity_shipped',
        'quantity_picked',
        'quantity_skipped_picking',
        'quantity_not_picked',
    ];

    protected $fillable = [
        'custom_unique_reference_id',
        'order_id',
        'product_id',
        'sku_ordered',
        'name_ordered',
        'price',
        'quantity_ordered',
        'quantity_split',
        'quantity_picked',
        'quantity_skipped_picking',
        'quantity_not_picked',
        'quantity_shipped',
    ];

    protected function casts(): array
    {
        return [
            'is_shipped' => 'boolean',
            'price' => 'float',
            'quantity_ordered' => 'float',
            'quantity_split' => 'float',
            'total_price' => 'float',
            'quantity_shipped' => 'float',
            'quantity_to_ship' => 'float',
            'quantity_to_pick' => 'float',
            'quantity_picked' => 'float',
            'quantity_skipped_picking' => 'float',
            'quantity_not_picked' => 'float',
            'inventory_source_quantity' => 'float',
        ];
    }

    /**
     * @param  Builder|QueryBuilder  $query
     * @return Builder|QueryBuilder
     */
    public function scopeCreatedBetween($query, $min, $max)
    {
        try {
            $startingDateTime = Carbon::parse($min);
            $endingDateTime = Carbon::parse($max);
        } catch (Exception $exception) {
            return $query;
        }

        return $query->whereBetween('orders_products.created_at', [
            $startingDateTime,
            $endingDateTime,
        ]);
    }

    public function scopeOrderPlacedBetween($query, $min, $max)
    {
        try {
            $startingDateTime = Carbon::parse($min);
            $endingDateTime = Carbon::parse($max);
        } catch (Exception $exception) {
            return $query;
        }

        return $query->whereBetween('orders.order_placed_at', [
            $startingDateTime,
            $endingDateTime,
        ]);
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(OrderProduct::class)
            ->leftJoin('products as product', 'product.id', '=', 'orders_products.product_id')
            ->leftJoin('orders', 'orders.id', '=', 'orders_products.order_id')
            ->select(['orders_products.*', 'inventory_source.*', 'orders.order_placed_at'])
            ->allowedFilters([
                AllowedFilter::scope('has_stock_reserved', 'whereHasStockReserved'),
                AllowedFilter::scope('warehouse_id', 'addWarehouseSource')->default(0),
                AllowedFilter::scope('in_stock_only', 'whereInStock'),

                AllowedFilter::scope('not_picked_only', 'whereNotPicked'),

                AllowedFilter::exact('product_id'),
                AllowedFilter::exact('order_id'),
                AllowedFilter::exact('is_shipped'),

                AllowedFilter::scope('current_shelf_location', 'MinimumShelfLocation'),
                AllowedFilter::exact('order.status_code')->ignore(''),
                AllowedFilter::exact('order.is_active'),
                AllowedFilter::scope('created_between'),
                AllowedFilter::scope('order_placed_between'),

                AllowedFilter::exact('packer_user_id', 'orders.packer_user_id'),

                AllowedFilter::scope('order.packed_between'),
            ])
            ->allowedIncludes([
                'order',
                'product',
                'product.aliases',
            ])
            ->allowedSorts([
                'inventory_source_shelf_location',
                'sku_ordered',
                'is_shipped',
                'orders_products.id',
                'product.department',
                'product.category',
                'order_placed_at'
            ]);
    }

    public function getQuantityToShipAttribute(): float
    {
        return $this->quantity_ordered - $this->quantity_split - $this->quantity_shipped;
    }

    public function getTotalPriceAttribute(): float
    {
        return ($this->quantity_ordered - $this->quantity_split) * $this->price;
    }

    public function scopeMinimumShelfLocation(Builder $query, string $currentLocation): Builder
    {
        return $query->where('inventory_source.inventory_source_shelf_location', '>=', $currentLocation);
    }

    /**
     * @param  mixed  $query
     * @return Builder|mixed
     */
    public function scopeWhereHasStockReserved($query)
    {
        return $query->where('quantity_to_ship', '>', 0)
            ->whereHas('order', function ($query) {
                $query->select([DB::raw(1)])
                    ->whereIn('status_code', OrderStatus::whereReservesStock(true)->get('code'));
            });
    }

    public function scopeWhereStatusCodeIn(Builder $query, array $statusCodeArray): Builder
    {
        return $query->whereHas('order', function ($query) use ($statusCodeArray) {
            $query->select(['id'])->whereIn('status_code', $statusCodeArray);
        });
    }

    public function scopeWhereStatusCodeNotIn(Builder $query, array $statusCodeArray): Builder
    {
        return $query->whereHas('order', function ($query) use ($statusCodeArray) {
            $query->select(['id'])->whereNotIn('status_code', $statusCodeArray);
        });
    }

    /**
     * @return mixed
     */
    public function scopeWhereInStock(Builder $query, bool $in_stock)
    {
        if (! $in_stock) {
            return $query;
        }

        return $query->where('inventory_source.inventory_source_quantity', '>', 0);
    }

    public function scopeAddWarehouseSource(Builder $query, int $warehouse_id): Builder
    {
        $source_inventory = Inventory::query()
            ->select([
                'warehouse_id as inventory_source_warehouse_id',
                'warehouse_code as inventory_source_warehouse_code',
                'shelve_location as inventory_source_shelf_location',
                'quantity as inventory_source_quantity',
                'product_id as inventory_source_product_id',
            ])
            ->where(['warehouse_id' => $warehouse_id])
            ->toBase();

        return $query->leftJoinSub($source_inventory, 'inventory_source', function ($join) {
            $join->on('orders_products.product_id', '=', 'inventory_source_product_id');
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function picks(): HasMany
    {
        return $this->hasMany(Pick::class);
    }
}
