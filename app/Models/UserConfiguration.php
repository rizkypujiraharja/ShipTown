<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserConfiguration.
 *
 * @method static Builder|UserConfiguration newModelQuery()
 * @method static Builder|UserConfiguration newQuery()
 * @method static Builder|UserConfiguration query()
 *
 * @mixin Eloquent
 */
class UserConfiguration extends Model
{
    protected $fillable = [
        'config',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }
}
