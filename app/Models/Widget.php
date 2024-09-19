<?php

namespace App\Models;

use App\BaseModel;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\Widget.
 *
 * @property int $id
 * @property string $name
 * @property array $config
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Widget newModelQuery()
 * @method static Builder|Widget newQuery()
 * @method static Builder|Widget query()
 * @method static Builder|Widget whereConfig($value)
 * @method static Builder|Widget whereCreatedAt($value)
 * @method static Builder|Widget whereId($value)
 * @method static Builder|Widget whereName($value)
 * @method static Builder|Widget whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class Widget extends BaseModel
{
    protected $fillable = [
        'config',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }
}
