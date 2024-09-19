<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Document.
 *
 * @property int $id
 * @property string $type
 * @property string $base64_content
 * @property string $created_at
 * @property string $updated_at
 */
class Document extends Model
{
    use HasFactory;

    const TYPE_URL = 'url';

    const TYPE_PDF = 'pdf';

    protected $fillable = [
        'type',
        'base64_content',
    ];

    protected $hidden = [
        'base64_content_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
            'base64_content' => 'string',
        ];
    }

    public function getBase64ContentAttribute(): string
    {
        return decrypt($this->getAttribute('base64_content_encrypted'));
    }

    public function setBase64ContentAttribute(string $value): void
    {
        $this->setAttribute('base64_content_encrypted', encrypt($value));
    }
}
