<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MailTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'subject' => $this->subject,
            'reply_to' => $this->reply_to,
            'to' => $this->to,
            'html_template' => $this->html_template,
            'text_template' => $this->text_template,
        ];
    }
}
