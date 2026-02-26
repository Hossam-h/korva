<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class StorePerformanceTrialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'age_category' => 'required|string|max:100',
            'max_players'  => 'sometimes|integer|min:1',
            'start_date'   => 'required|date',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'thumbnail'    => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status'       => 'sometimes|in:active,expired,cancelled',
            'day_of_week'  => 'sometimes|array',
            'day_of_week.*'=> 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'        => 'Trial title is required.',
            'age_category.required' => 'Age category is required.',
            'max_players.integer'   => 'Max players must be an integer.',
            'max_players.min'       => 'Max players must be at least 1.',
            'start_date.required'   => 'Start date is required.',
            'start_date.date'       => 'Start date must be a valid date.',
            'start_time.required'   => 'Start time is required.',
            'start_time.date_format'=> 'Start time must be in HH:MM format.',
            'end_time.required'     => 'End time is required.',
            'end_time.date_format'  => 'End time must be in HH:MM format.',
            'end_time.after'        => 'End time must be after start time.',
            'thumbnail.image'       => 'Thumbnail must be an image.',
            'thumbnail.mimes'       => 'Thumbnail must be jpeg, png, jpg, or webp.',
            'thumbnail.max'         => 'Thumbnail must not exceed 2MB.',
            'status.in'             => 'Status must be active, expired, or cancelled.',
            'day_of_week.array'     => 'Day of week must be an array.',
            'day_of_week.*.in'      => 'Each day must be a valid weekday name.',
        ];
    }
}
