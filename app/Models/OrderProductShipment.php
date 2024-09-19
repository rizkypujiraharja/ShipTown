<?php

namespace App\Models;

use App\BaseModel;
use App\User;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\OrderProduct.
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $user_id
 * @property int|null $warehouse_id
 * @property int|null $order_id
 * @property int|null $order_product_id
 * @property string $sku_shipped
 * @property float $quantity_shipped
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Product|null $product
 * @property Warehouse|null $warehouse
 * @property User|null $user
 * @property Order|null $order
 * @property OrderProduct|null $orderProduct
 * @property Inventory inventory
 *
 * @mixin Eloquent
 */
class OrderProductShipment extends BaseModel
{
    protected $table = 'orders_products_shipments';

    protected $fillable = [
        'product_id',
        'user_id',
        'warehouse_id',
        'order_id',
        'order_product_id',
        'order_shipment_id',
        'sku_shipped',
        'quantity_shipped',
    ];

    protected function casts(): array
    {
        return [
            'quantity_shipped' => 'float',
        ];
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'product_id', 'product_id')
            ->where([
                'warehouse_id' => $this->warehouse_id,
            ]);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class);
    }
}
