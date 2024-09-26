<?php

namespace App\Models;

use App\User;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

/**
 * App\Models\Packlist.
 *
 * @property mixed $is_packed
 * @property-read Order $order
 * @property-read Product $product
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Packlist addInventorySource($inventory_location_id)
 * @method static \Illuminate\Database\Eloquent\Builder|Packlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Packlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Packlist query()
 *
 * @mixin Eloquent
 */
class Packlist extends Model
{
    protected $fillable = [
        'order_id',
        'order_product_id',
        'product_id',
        'location_id',
        'sku_ordered',
        'name_ordered',
        'quantity_requested',
        'quantity_packed',
        'packer_user_id',
        'packed_at',
        'is_packed',
    ];

    protected $appends = [
        'is_packed',
    ];

    public function scopeAddInventorySource(Builder $query, int $inventory_location_id): Builder
    {
        $source_inventory = Inventory::query()
            ->select([
                'shelve_location as inventory_source_shelf_location',
                'quantity as inventory_source_quantity',
                'product_id as inventory_source_product_id',
            ])
            ->where(['location_id' => $inventory_location_id])
            ->toBase();

        return $query->leftJoinSub($source_inventory, 'inventory_source', function ($join) {
            $join->on('packlists.product_id', '=', 'inventory_source_product_id');
        });
    }

    public function getIsPackedAttribute()
    {
        return $this->packed_at !== null;
    }

    public function setIsPackedAttribute($value)
    {
        $this->packed_at = $value ? now() : null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picker_user_id');
    }
}
