<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class AcademySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('player')->check();
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'age_group' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'between:1,5'],
            'sort' => ['nullable', 'in:all,most_popular,top_rated,newest'],
            'training_days' => ['nullable', 'array'],
            'training_days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'training_time' => ['nullable', 'in:morning,afternoon,evening'],
            'accepted_genders' => ['nullable', 'array'],
            'accepted_genders.*' => ['in:male,female'],
            'min_age' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_age' => ['nullable', 'integer', 'gte:min_age', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
