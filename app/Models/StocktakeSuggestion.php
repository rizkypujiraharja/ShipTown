<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int inventory_id
 * @property int $product_id
 * @property int $warehouse_id
 * @property int points
 * @property string reason
 */
class StocktakeSuggestion extends BaseModel
{
    use HasFactory;

    protected $table = 'stocktake_suggestions';

    protected $fillable = [
        'inventory_id',
        'product_id',
        'warehouse_id',
        'points',
        'reason',
    ];

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
}
