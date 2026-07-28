<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => trim($this->full_name),
            'image_url' => $this->image_url,
            'age' => $this->birth_date?->age,
            'gender' => $this->gender,
        ];
    }
}
