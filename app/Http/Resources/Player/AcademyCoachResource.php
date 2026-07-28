<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyCoachResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $groups = $this->whenLoaded('groups', fn () => $this->groups, collect());
        $days = $groups->flatMap(fn ($group) => $group->days ?? [])->unique()->values();
        $firstGroup = $groups->first();
        $students = $groups->flatMap(fn ($group) => $group->players ?? collect())
            ->unique('id')
            ->values();

        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'image_url' => $this->image_url,
            'rating' => round((float) ($this->reviews_avg_rating ?? 0), 2),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'schedule' => $firstGroup
                ? $days->map(fn ($day) => ucfirst($day))->implode(' - ')
                    .' | '.$firstGroup->start_time.' - '.$firstGroup->end_time
                : null,
            'student_images' => $students->take(2)->pluck('image_url')->filter()->values(),
            'additional_students_count' => max(0, $students->count() - 2),
        ];
    }
}
