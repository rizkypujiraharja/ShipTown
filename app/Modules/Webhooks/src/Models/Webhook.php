<?php

namespace App\Modules\Webhooks\src\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $table = 'modules_webhooks_pending_webhooks';

    protected $fillable = [
        'model_class',
        'model_id',
        'reserved_at',
        'available_at',
        'published_at',
        'message',
        'sns_message_id',
    ];

    protected $attributes = [
        'message' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'message' => 'array',
        ];
    }
}
