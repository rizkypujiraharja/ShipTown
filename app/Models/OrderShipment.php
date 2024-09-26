<?php

namespace App\Models;

use App\BaseModel;
use App\User;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\OrderShipment.
 *
 * @property int $id
 * @property int $order_id
 * @property string $shipping_number
 * @property string $carrier
 * @property string $service
 * @property string $tracking_url
 * @property int|null $user_id
 * @property string|null $base64_pdf_labels
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read User|null $user
 * @property-read int $age_in_days
 *
 * @method static Builder|OrderShipment newModelQuery()
 * @method static Builder|OrderShipment newQuery()
 * @method static Builder|OrderShipment query()
 * @method static Builder|OrderShipment whereCreatedAt($value)
 * @method static Builder|OrderShipment whereId($value)
 * @method static Builder|OrderShipment whereOrderId($value)
 * @method static Builder|OrderShipment whereShippingNumber($value)
 * @method static Builder|OrderShipment whereUpdatedAt($value)
 * @method static Builder|OrderShipment whereUserId($value)
 * @method static Builder|OrderShipment whereAgeInDaysBetween($ageInDaysFrom, $ageInDaysTo)
 *
 * @mixin Eloquent
 */
class OrderShipment extends BaseModel
{
    use HasFactory;

    protected $table = 'orders_shipments';

    /**
     * @var string[]
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'carrier',
        'service',
        'shipping_number',
        'tracking_url',
        'base64_pdf_labels',
    ];

    // we use attributes to set default values
    // we won't use database default values
    // as this is then not populated
    // correctly to events
    protected $attributes = [
        'base64_pdf_labels' => '',
    ];

    protected $appends = [
        'age_in_days',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderShipmentProducts(): HasMany
    {
        return $this->hasMany(OrderProductShipment::class);
    }

    public function getAgeInDaysAttribute(): int
    {
        return (int) Carbon::now()->ceilDay()->diffInDays($this->created_at);
    }

    public function scopeWhereAgeInDaysBetween($query, $ageInDaysFrom, $ageInDaysTo)
    {
        return $query->whereRaw('DATEDIFF(now(), `'.$this->getConnection()->getTablePrefix().$this->getTable()."`.`created_at`) BETWEEN $ageInDaysFrom AND $ageInDaysTo");
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(OrderShipment::class)
            ->allowedFilters([
                AllowedFilter::partial('shipping_number'),
                AllowedFilter::exact('order.status_code'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('order_id'),

                AllowedFilter::scope('age_in_days_between', 'whereAgeInDaysBetween'),
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'order',
                'user',
            ])
            ->defaultSort('-id')
            ->allowedSorts([
                'id',
            ]);
    }
}
