<?php

namespace App\Modules\Rmsapi\src\Models;

use App\BaseModel;
use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\RmsapiProductImport.
 *
 * @property int $id
 * @property Carbon|null $reserved_at
 * @property int $connection_id
 * @property int $warehouse_id
 * @property int $product_id
 * @property Carbon|null $when_processed
 * @property string|null $uuid
 * @property string|null $type
 * @property string|null $sku
 * @property float|null $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $transaction_time
 * @property int|null $transaction_number
 * @property int|null $transaction_entry_id
 * @property string|null $comment
 * @property array $raw_import
 * @property RmsapiConnection $rmsapiConnection
 * @property mixed $inventory_id
 * @property Warehouse $warehouse
 */
class RmsapiSaleImport extends BaseModel
{
    use HasFactory;

    protected $table = 'modules_rmsapi_sales_imports';

    protected $fillable = [
        'connection_id',
        'warehouse_id',
        'product_id',
        'inventory_movement_id',
        'reserved_at',
        'processed_at',
        'uuid',
        'type',
        'sku',
        'price',
        'cost',
        'quantity',
        'transaction_time',
        'transaction_number',
        'transaction_entry_id',
        'comment',
        'raw_import',
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
        return $this->belongsTo(RmsapiConnection::class, 'connection_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
