<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'comment' => $this->comment,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
