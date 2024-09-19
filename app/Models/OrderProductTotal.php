<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int order_id
 * @property int count
 * @property float quantity_ordered
 * @property float quantity_split
 * @property float total_price
 * @property float quantity_picked
 * @property float quantity_skipped_picking
 * @property float quantity_not_picked
 * @property float quantity_shipped
 * @property float quantity_to_pick
 * @property float quantity_to_ship
 * @property Carbon max_updated_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class OrderProductTotal extends Model
{
    protected $table = 'orders_products_totals';

    protected $fillable = [
        'order_id',
        'count',
        'quantity_ordered',
        'quantity_split',
        'total_price',
        'quantity_picked',
        'quantity_skipped_picking',
        'quantity_not_picked',
        'quantity_shipped',
        'quantity_to_pick',
        'quantity_to_ship',
        'max_updated_at',
    ];

    protected $attributes = [
        'count' => 0,
        'quantity_ordered' => 0,
        'quantity_split' => 0,
        'total_price' => 0,
        'quantity_picked' => 0,
        'quantity_skipped_picking' => 0,
        'quantity_not_picked' => 0,
        'quantity_shipped' => 0,
        'quantity_to_pick' => 0,
        'quantity_to_ship' => 0,
        'max_updated_at' => '2000-01-01 00:00:00',
    ];

    protected function casts(): array
    {
        return [
            'count' => 'integer',
            'quantity_ordered' => 'float',
            'quantity_split' => 'float',
            'total_price' => 'float',
            'quantity_picked' => 'float',
            'quantity_skipped_picking' => 'float',
            'quantity_not_picked' => 'float',
            'quantity_shipped' => 'float',
            'quantity_to_pick' => 'float',
            'quantity_to_ship' => 'float',
            'max_updated_at' => 'timestamp',
        ];
    }
}
