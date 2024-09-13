<?php

namespace App\Models;

use App\BaseModel;
use App\User;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\OrderComment.
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $user_id
 * @property string $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static Builder|OrderComment newModelQuery()
 * @method static Builder|OrderComment newQuery()
 * @method static Builder|OrderComment query()
 * @method static Builder|OrderComment whereComment($value)
 * @method static Builder|OrderComment whereCreatedAt($value)
 * @method static Builder|OrderComment whereId($value)
 * @method static Builder|OrderComment whereOrderId($value)
 * @method static Builder|OrderComment whereUpdatedAt($value)
 * @method static Builder|OrderComment whereUserId($value)
 *
 * @mixin Eloquent
 */
class OrderComment extends BaseModel
{
    protected $table = 'orders_comments';

    protected $fillable = [
        'order_id',
        'user_id',
        'comment',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(OrderComment::class)
            ->allowedFilters([
                AllowedFilter::exact('order_id'),
            ])
            ->allowedIncludes([
                'user',
            ])
            ->allowedSorts([
                'id',
                'created_at',
            ])
            ->defaultSort('id');
    }
}
