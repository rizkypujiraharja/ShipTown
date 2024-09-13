<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_provider_class' => $this->service_provider_class,
            'enabled' => $this->enabled,
            'name' => $this->name,
            'description' => $this->description,
            'settings_link' => $this->settings_link,
        ];
    }
}
