<?php

namespace App\Modules\DataCollectorQuantityDiscounts\src\Models;

use App\Models\DataCollectionRecord;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property int id
 * @property string name
 * @property string job_class
 * @property array configuration
 * @property float quantity_at_full_price
 * @property float quantity_at_discounted_price
 * @property float quantity_required
 * @property float total_quantity_per_discount
 * @property bool is_multibuy_discount
 * @property string deleted_at
 * @property string updated_at
 * @property string created_at
 * @property QuantityDiscountsProduct[] products
 */
class QuantityDiscount extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    use SoftDeletes;

    protected $table = 'modules_quantity_discounts';

    protected $fillable = [
        'name',
        'job_class',
        'configuration',
    ];

    protected $casts = [
        'id' => 'integer',
        'configuration' => 'array',
    ];

    public function getQuantityAtFullPriceAttribute(): float
    {
        return data_get($this->configuration, 'quantity_full_price', 0.00);
    }

    public function getQuantityAtDiscountedPriceAttribute(): float
    {
        return data_get($this->configuration, 'quantity_discounted', 0.00);
    }

    public function getQuantityRequiredAttribute(): float
    {
        return data_get($this->configuration, 'quantity_required', 0.00);
    }

    public function getTotalQuantityPerDiscountAttribute(): float
    {
        return $this->quantity_at_full_price + $this->quantity_at_discounted_price + $this->quantity_required;
    }

    public function getIsMultibuyDiscountAttribute(): bool
    {
        $ranges = data_get($this->configuration, 'multibuy_discount_ranges', []);

        return (bool) count($ranges);
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(QuantityDiscount::class)
            ->allowedFilters([AllowedFilter::scope('search', 'whereHasText')])
            ->allowedSorts(['id', 'name', 'type'])
            ->allowedIncludes(['products']);
    }

    public function scopeWhereHasText(mixed $query, string $text): mixed
    {
        return $query->where('name', $text)
            ->orWhere('type', $text)
            ->orWhere('name', 'like', '%'.$text.'%')
            ->orWhere('job_class', 'like', '%'.$text.'%');
    }

    public function products(): HasMany
    {
        return $this->hasMany(QuantityDiscountsProduct::class);
    }

    public function dataCollectionRecords(): HasMany
    {
        return $this->hasMany(DataCollectionRecord::class, 'price_source_id', 'id');
    }
}
