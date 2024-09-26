<?php

namespace App\Modules\PrintNode\src\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PrintJob.
 *
 * @property int $printer_id
 * @property string $title
 * @property string $content_type
 * @property string $content
 * @property string $pdf
 * @property string $pdf_url
 * @property int $expire_after
 *
 * @mixin Eloquent
 */
class PrintJob extends Model
{
    protected $table = 'modules_printnode_print_jobs';

    protected $fillable = [
        'printer_id', 'title', 'content_type', 'content', 'expire_after', 'pdf_url', 'pdf',
    ];

    protected $attributes = [
        'title' => '',
        'content_type' => 'pdf_uri',
        'expire_after' => 15,
    ];

    public function setPdfAttribute($value)
    {
        $this->content_type = 'pdf_base64';
        $this->content = $value;

        if (@is_file($value)) { // if file exists and is a file and not a directory
            $this->content = file_get_contents($value);
        }
    }

    public function setPdfUrlAttribute($value): void
    {
        $this->content_type = 'pdf_uri';
        $this->content = $value;
    }

    public function toPrintNodePayload(): array
    {
        return [
            'source' => 'ShipTown',
            'expireAfter' => $this->expire_after, // seconds
            'printerId' => $this->printer_id,
            'title' => $this->title,
            'contentType' => $this->content_type,
            'content' => $this->content,
        ];
    }
}
