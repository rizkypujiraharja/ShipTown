<?php

namespace App\Models;

use App\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DataCollectionComment extends BaseModel
{
    protected $table = 'data_collections_comments';

    protected $fillable = [
        'data_collection_id',
        'user_id',
        'comment',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(DataCollectionComment::class)
            ->allowedFilters([
                AllowedFilter::exact('data_collection_id'),
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
