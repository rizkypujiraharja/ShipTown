<?php

namespace App\Models;

use App\BaseModel;
use App\Events\Warehouse\WarehouseTagAttachedEvent;
use App\Traits\HasTagsTrait;
use App\Traits\LogsActivityTrait;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\Warehouse.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $address_id
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property OrderAddress|null $address
 *
 * @mixin Eloquent
 * @mixin HasTagsTrait
 */
class Warehouse extends BaseModel
{
    use HasFactory;
    use HasTagsTrait;
    use LogsActivityTrait;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Warehouse::class)
            ->allowedFilters([
                AllowedFilter::scope('search', 'whereHasText'),
                AllowedFilter::exact('id'),
                AllowedFilter::exact('code'),
                AllowedFilter::exact('name'),

                AllowedFilter::scope('has_tags', 'hasTags'),
                AllowedFilter::scope('without_tags', 'withoutAllTags'),
            ])
            ->allowedSorts([
                'id',
                'code',
                'name',
            ])
            ->allowedIncludes([
                'address',
                'tags',
            ]);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(OrderAddress::class);
    }

    protected function onTagAttached($tag)
    {
        WarehouseTagAttachedEvent::dispatch($this, $tag);
    }

    protected function onTagDetached($tag)
    {
        WarehouseTagAttachedEvent::dispatch($this, $tag);
    }
}
