<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academy_id' => $this->academy_id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'user_name' => $this->player?->full_name,
            'user_image' => $this->player?->image_url,
            'images' => $this->image_urls,
            'user' => [
                'id' => $this->player?->id,
                'name' => $this->player?->full_name,
                'image_url' => $this->player?->image_url,
            ],
        ];
    }
}
