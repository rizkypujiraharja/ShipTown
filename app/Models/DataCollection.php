<?php

namespace App\Models;

use App\BaseModel;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\DataCollection
 *
 * @property-read OrderAddress|null $shippingAddress
 * @property-read OrderAddress|null $billingAddress
 *
 * @property int $id
 * @property int $warehouse_code
 * @property int $warehouse_id
 * @property int $destination_warehouse_id
 * @property int $destination_collection_id
 * @property string $name
 * @property string $recount_required
 * @property string $calculated_at
 * @property string $type
 * @property string $currently_running_task
 * @property int $shipping_address_id
 * @property int $billing_address_id
 * @property float $total_quantity_scanned
 * @property float $total_cost
 * @property float $total_full_price
 * @property float $total_discount
 * @property float $total_sold_price
 * @property float $total_profit
 * @property string $custom_uuid
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property HasMany $records
 * @property Warehouse $warehouse
 * @property DataCollection $destinationCollection
 */
class DataCollection extends BaseModel
{
    use HasFactory;
    use LogsActivityTrait;
    use SoftDeletes;

    protected static $logAttributes = [
        'deleted_at',
        'type',
        'currently_running_task',
    ];

    protected $fillable = [
        'type',
        'warehouse_id',
        'warehouse_code',
        'destination_warehouse_id',
        'destination_warehouse_code',
        'destination_collection_id',
        'shipping_address_id',
        'billing_address_id',
        'name',
        'custom_uuid',
        'currently_running_task',
    ];

    protected function casts(): array
    {
        return [
            'total_quantity_scanned' => 'double',
            'total_cost' => 'double',
            'total_full_price' => 'double',
            'total_discount' => 'double',
            'total_sold_price' => 'double',
            'total_profit' => 'double',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(DataCollectionRecord::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function destinationCollection(): BelongsTo
    {
        return $this->belongsTo(DataCollection::class, 'destination_collection_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(OrderAddress::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(OrderAddress::class);
    }

    /**
     * @return HasMany|DataCollectionComment
     */
    public function comments(): HasMany|DataCollectionComment
    {
        return $this->hasMany(DataCollectionComment::class)->orderByDesc('id');
    }

    public function firstOrCreateProductRecord(mixed $product_id, ?float $unit_sold_price = null): DataCollectionRecord
    {
        $inventory = Inventory::query()
            ->with('prices')
            ->where(['product_id' => $product_id, 'warehouse_id' => $this->warehouse_id])
            ->first();

        return DataCollectionRecord::query()
            ->where([
                'data_collection_id' => $this->id,
                'product_id' => $product_id,
            ])
            ->firstOr(function () use ($inventory, $unit_sold_price) {
                return DataCollectionRecord::query()->create([
                    'data_collection_id' => $this->id,
                    'unit_cost' => $inventory->prices->cost,
                    'unit_full_price' => data_get($inventory, 'prices.price'),
                    'unit_sold_price' => $unit_sold_price ?? data_get($inventory, 'prices.current_price'),
                    'price_source' => null,
                    'price_source_id' => null,
                    'inventory_id' => $inventory->id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'warehouse_code' => $inventory->warehouse_code,
                    'product_id' => $inventory->product_id,
                    'quantity_requested' => 0,
                ]);
            });
    }
}
