<?php

namespace App\Models;

use App\BaseModel;
use App\Modules\InventoryMovementsStatistics\src\Models\InventoryMovementsStatistic;
use App\Traits\LogsActivityTrait;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\Inventory.
 *
 * @property int $id
 * @property string $product_sku
 * @property int|null $warehouse_id
 * @property int $product_id
 * @property string $warehouse_code
 * @property string $shelve_location
 * @property float $quantity
 * @property float $quantity_reserved
 * @property float $quantity_incoming
 * @property float $quantity_required
 * @property float $restock_level
 * @property float $reorder_point
 * @property int|null $last_movement_id
 * @property Carbon|null $first_movement_at
 * @property Carbon|null $last_movement_at
 * @property Carbon|null $first_received_at
 * @property Carbon|null $last_received_at
 * @property Carbon|null $first_sold_at
 * @property Carbon|null $last_sold_at
 * @property Carbon|null $first_counted_at
 * @property Carbon|null $last_counted_at
 * @property Carbon|null $in_stock_since
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read float $quantity_available
 * @property-read Product $product
 * @property-read Warehouse $warehouse
 * @property-read ProductPrice $prices
 * @property-read Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property bool|null $recount_required
 *
 * @mixin Eloquent
 */
class Inventory extends BaseModel
{
    use HasFactory;
    use LogsActivityTrait;

    protected $table = 'inventory';

    protected static $logAttributes = [
        'quantity_reserved',
        'restock_level',
        'reorder_point',
        'shelve_location',
    ];

    protected $fillable = [
        'recount_required',
        'warehouse_id',
        'warehouse_code',
        'shelve_location',
        'product_id',
        'quantity',
        'quantity_reserved',
        'quantity_incoming',
        'restock_level',
        'reorder_point',
        'last_counted_at',
        'in_stock_since',
        'last_movement_id',
        'first_movement_at',
        'last_movement_at',
        'first_received_at',
        'last_received_at',
        'first_sold_at',
        'last_sold_at',
        'first_counted_at',
        'last_counted_at',
    ];

    protected $attributes = [
        'quantity' => 0,
        'quantity_reserved' => 0,
        'quantity_incoming' => 0,
        'restock_level' => 0,
        'reorder_point' => 0,
    ];

    protected function casts(): array
    {
        return [
            'recount_required' => 'boolean',
            'quantity' => 'float',
            'quantity_reserved' => 'float',
            'quantity_available' => 'float',
            'quantity_incoming' => 'float',
            'restock_level' => 'float',
            'reorder_point' => 'float',
            'quantity_required' => 'float',
            'occurred_at' => 'datetime',
            'first_movement_at' => 'datetime',
            'last_movement_at' => 'datetime',
            'first_received_at' => 'datetime',
            'last_received_at' => 'datetime',
            'first_sold_at' => 'datetime',
            'last_sold_at' => 'datetime',
            'first_counted_at' => 'datetime',
            'last_counted_at' => 'datetime',
            'in_stock_since' => 'datetime',
            'deleted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Inventory::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('product_id'),
                AllowedFilter::exact('warehouse_id'),
                AllowedFilter::exact('warehouse_code'),
                AllowedFilter::exact('quantity_incoming'),
                AllowedFilter::scope('quantity_between'),
                AllowedFilter::scope('quantity_available_between'),
                AllowedFilter::scope('quantity_required_between'),
                AllowedFilter::scope('restock_level_between'),

                AllowedFilter::scope('sku_or_alias'),
                AllowedFilter::scope('source_warehouse_code', 'addWarehouseSource'),
                AllowedFilter::scope('inventory_source_quantity_available_between'),
            ])
            ->allowedSorts([
                'id',
                'product_id',
                'warehouse_id',
                'quantity',
                'quantity_reserved',
                'quantity_available',
                'quantity_required',
                'quantity_incoming',
                'restock_level',
                'shelve_location',
            ])
            ->allowedIncludes([
                'product',
            ]);
    }

    public static function find(int $product_id, int $warehouse_id): self
    {
        /** @var Inventory $inventory */
        $inventory = static::query()
            ->where('product_id', $product_id)
            ->where('warehouse_id', $warehouse_id)
            ->first();

        return $inventory;
    }

    public function scopeSkuOrAlias($query, string $value)
    {
        $aliases = ProductAlias::query()
            ->select('product_id')
            ->where(['alias' => $value]);

        return $query->whereIn('product_id', $aliases);
    }

    /**
     * @param  mixed  $query
     * @param  mixed  $min
     * @param  mixed  $max
     * @return mixed
     */
    public function scopeQuantityBetween($query, $min, $max)
    {
        return $query->whereBetween('quantity', [floatval($min), floatval($max)]);
    }

    /**
     * @param  mixed  $query
     * @param  mixed  $min
     * @param  mixed  $max
     * @return mixed
     */
    public function scopeQuantityAvailableBetween($query, $min, $max)
    {
        return $query->whereBetween('quantity_available', [floatval($min), floatval($max)]);
    }

    /**
     * @param  mixed  $query
     * @param  mixed  $min
     * @param  mixed  $max
     * @return mixed
     */
    public function scopeQuantityRequiredBetween($query, $min, $max)
    {
        return $query->whereBetween('quantity_required', [floatval($min), floatval($max)]);
    }

    /**
     * @param  mixed  $query
     * @param  mixed  $min
     * @param  mixed  $max
     * @return mixed
     */
    public function scopeRestockLevelBetween($query, $min, $max)
    {
        return $query->whereBetween('restock_level', [floatval($min), floatval($max)]);
    }

    /**
     * @return mixed
     */
    public function scopeWithWarehouseTags($query, $tags)
    {
        return $query->whereHas('warehouse', function (Builder $query) use ($tags) {
            $query->withAllTags($tags);
        });
    }

    /**
     * @return mixed
     */
    public function scopeAddWarehouseSource($query, $warehouse_code)
    {
        $source_inventory = Inventory::query()
            ->select([
                'warehouse_id as inventory_source_warehouse_id',
                'warehouse_code as inventory_source_warehouse_code',
                'quantity_available as inventory_source_quantity_available',
                'product_id as inventory_source_product_id',
            ])
            ->where(['warehouse_code' => $warehouse_code])
            ->toBase();

        return $query->leftJoinSub($source_inventory, 'inventory_source', function ($join) {
            $join->on('inventory.product_id', '=', 'inventory_source_product_id');
        });
    }

    /**
     * @param  mixed  $query
     * @param  mixed  $min
     * @param  mixed  $max
     * @return mixed
     */
    public function scopeInventorySourceQuantityAvailableBetween($query, $min, $max)
    {
        return $query->whereBetween('inventory_source_quantity_available', [floatval($min), floatval($max)]);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function prices(): HasOne
    {
        return $this->hasOne(ProductPrice::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productAliases(): HasMany
    {
        return $this->hasMany(ProductAlias::class, 'product_id', 'product_id');
    }

    public function last7daysSales(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_id', 'id')
            ->where('type', InventoryMovement::TYPE_SALE)
            ->whereBetween('created_at', [Carbon::now()->subDays(7), Carbon::now()]);
    }

    public function movementsStatistics(): HasMany
    {
        return $this->hasMany(InventoryMovementsStatistic::class, 'inventory_id', 'id');
    }
}
