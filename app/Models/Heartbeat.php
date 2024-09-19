<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static expired()
 *
 * @property string $auto_heal_job_class
 */
class Heartbeat extends Model
{
    const LEVEL_ERROR = 'error';

    const LEVEL_WARNING = 'warning';

    const LEVEL_INFO = 'info';

    protected $fillable = [
        'code',
        'level',
        'error_message',
        'auto_heal_job_class',
        'expires_at',
    ];

    protected $attributes = [
        'level' => 'error',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
}
