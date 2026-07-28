<?php

namespace App\Http\Requests\Player;

class UpdateChildRequest extends StoreChildRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'date', 'before:today'],
            'gender' => ['sometimes', 'in:male,female'],
            'weight' => ['sometimes', 'nullable', 'numeric', 'min:1'],
            'has_health_issues' => ['sometimes', 'boolean'],
            'health_issues' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'other_health_issue' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
}
