<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MailTemplates\Models\MailTemplate as SpatieMailTemplate;

/**
 * @mixin Eloquent
 *
 * @property string code
 * @property string to
 * @property string reply_to
 * @property string html_template
 */
class MailTemplate extends SpatieMailTemplate
{
    use HasFactory;

    protected $fillable = [
        'code',
        'mailable',
        'subject',
        'html_template',
        'text_template',
        'reply_to',
        'to',
    ];

    public function getNameAttribute()
    {
        return $this->code;
    }
}
