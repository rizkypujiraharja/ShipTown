<?php

namespace App\Modules\InventoryMovementsStatistics\src\Models;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $type
 * @property int $inventory_id
 * @property int $product_id
 * @property string $warehouse_code
 * @property float $last7days_quantity_delta
 * @property int $last7days_max_movement_id
 * @property int $last7days_min_movement_id
 * @property float $last14days_quantity_delta
 * @property int $last14days_max_movement_id
 * @property int $last14days_min_movement_id
 * @property float $last28days_quantity_delta
 * @property int $last28days_max_movement_id
 * @property int $last28days_min_movement_id
 */
class InventoryMovementsStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'inventory_id',
        'product_id',
        'warehouse_code',
        'last7days_quantity_delta',
        'last7days_max_movement_id',
        'last7days_min_movement_id',
        'last14days_quantity_delta',
        'last14days_max_movement_id',
        'last14days_min_movement_id',
        'last28days_quantity_delta',
        'last28days_max_movement_id',
        'last28days_min_movement_id',
    ];

    protected function casts(): array
    {
        return [
            'last7days_quantity_delta' => 'double',
            'last14days_quantity_delta' => 'double',
            'last28days_quantity_delta' => 'double',
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
        return $this->belongsTo(Warehouse::class, 'warehouse_code');
    }
}
