<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyDetailResource extends JsonResource
{
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
            'gallery' => $this->whenLoaded('attaches', fn () => $this->attaches
                ->where('attach_type', 'gallery')
                ->pluck('full_attach_path')
                ->values()),
            'travel_time' => $this->travel_time !== null ? $this->travel_time.' minutes' : null,
            'distance' => $this->distance !== null ? $this->distance.' km' : null,
            'price' => $this->price,
            'currency' => $this->currency ?? 'SAR',
            'price_period' => 'month',
            'is_favorite' => (bool) $this->is_favorite,
            'reviews_avg_rating' => round((float) ($this->reviews_avg_rating ?? 0), 2),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'description' => $this->description,
            'accepted_genders' => $this->accepted_genders ?? [],
            'operating_hours' => $this->whenLoaded('operatingHours', fn () => $this->operatingHours->map(fn ($hour) => [
                'day' => $hour->day,
                'opens_at' => $hour->opens_at,
                'closes_at' => $hour->closes_at,
            ])),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'coaches_count' => (int) ($this->coaches_count ?? 0),
            'services_count' => (int) ($this->services_count ?? 0),
            'groups_count' => (int) ($this->groups_count ?? 0),
            'is_active' => (bool) $this->is_active,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
