<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\StocktakeSuggestions\src\Models\StocktakeSuggestionsConfiguration */
class StocktakeSuggestionsConfigurationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'id' => $this->id,
            'min_count_date' => $this->min_count_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
