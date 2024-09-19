<?php

namespace App\Models;

use App\BaseModel;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\OrderStatus.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $order_active
 * @property bool $order_on_hold
 * @property bool $sync_ecommerce
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Eloquent
 */
class OrderStatus extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'orders_statuses';

    protected $fillable = [
        'name',
        'code',
        'order_active',
        'order_on_hold',
        'hidden',
        'sync_ecommerce',
    ];

    protected $attributes = [
        'order_active' => true,
        'order_on_hold' => false,
        'hidden' => false,
        'sync_ecommerce' => false,
    ];

    protected function casts(): array
    {
        return [
            'order_active' => 'boolean',
            'order_on_hold' => 'boolean',
            'hidden' => 'boolean',
            'sync_ecommerce' => 'boolean',
        ];
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(OrderStatus::class)
            ->allowedFilters([
                AllowedFilter::exact('hidden'),
            ])
            ->allowedIncludes([])
            ->allowedSorts([
                'updated_at',
                'code',
            ]);
    }
}
