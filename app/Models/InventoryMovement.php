<?php

namespace App\Models;

use App\BaseModel;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    id
 * @property string warehouse_code
 * @property Carbon occurred_at
 * @property int    sequence_number
 * @property string type
 * @property string custom_unique_reference_id
 * @property int    inventory_id
 * @property int    product_id
 * @property int    warehouse_id
 * @property float  quantity_delta
 * @property float  quantity_before
 * @property float  quantity_after
 * @property string description
 * @property int    user_id
 * @property int    previous_movement_id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Product   product
 * @property Warehouse warehouse
 * @property Inventory inventory
 */
class InventoryMovement extends BaseModel
{
    use HasFactory;

    const TYPE_ADJUSTMENT = 'adjustment';

    const TYPE_SALE = 'sale';

    const TYPE_RETURN = 'return';

    const TYPE_STOCKTAKE = 'stocktake';

    const TYPE_TRANSFER_IN = 'transfer in';

    const TYPE_TRANSFER_OUT = 'transfer out';

    protected $fillable = [
        'warehouse_code',
        'occurred_at',
        'sequence_number',
        'type',
        'custom_unique_reference_id',
        'inventory_id',
        'product_id',
        'warehouse_id',
        'quantity_before',
        'quantity_delta',
        'quantity_after',
        'unit_cost',
        'unit_price',
        'description',
        'user_id',
        'previous_movement_id',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'quantity_before' => 'float',
            'quantity_delta' => 'float',
            'quantity_after' => 'float',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alias(): HasMany
    {
        return $this->hasMany(ProductAlias::class, 'product_id', 'product_id');
    }
}
