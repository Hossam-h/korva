<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $coach = $this->whenLoaded('coaches', fn () => $this->coaches->first());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'coach_id' => $coach?->id,
            'coach_name' => $coach?->full_name,
            'days' => $this->days ?? [],
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'capacity' => $this->capacity,
            'available_seats' => $this->available_seats,
            'price' => $this->monthly_price !== null ? (float) $this->monthly_price : null,
            'currency' => $this->academy?->currency ?? 'SAR',
        ];
    }
}
