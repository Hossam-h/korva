<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyResource extends JsonResource
{
    /**
     * Transform the academy into the player-facing API shape.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'age_group' => $this->age_group,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'image_url' => $this->image_url,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'travel_time' => $this->travel_time !== null ? $this->travel_time.' minutes' : null,
            'price' => $this->price,
            'currency' => $this->currency ?? 'SAR',
            'price_period' => 'month',
            'is_favorite' => (bool) $this->is_favorite,
            'is_active' => (bool) $this->is_active,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'reviews_avg_rating' => $this->reviews_avg_rating !== null
                ? round((float) $this->reviews_avg_rating, 2)
                : 0,
            'reviews_count' => (int) ($this->reviews_count ?? 0),
        ];
    }
}
