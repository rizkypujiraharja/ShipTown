<?php

namespace App\Modules\PrintNode\src\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintNodeClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'api_key' => '****',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
