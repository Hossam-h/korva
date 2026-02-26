<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademyAcademicSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_days' => 'sometimes|array',
            'work_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'morning_start' => 'sometimes|date_format:H:i',
            'morning_end' => 'sometimes|date_format:H:i|after:morning_start',
            'has_evening' => 'sometimes|boolean',
            'evening_start' => 'sometimes|nullable|date_format:H:i',
            'evening_end' => 'sometimes|nullable|date_format:H:i|after:evening_start',
            'age_ranges' => 'sometimes|array',
            'age_ranges.*' => 'string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'work_days.array' => 'Work days must be an array.',
            'work_days.*.in' => 'Each work day must be a valid weekday name.',
            'morning_start.date_format' => 'Morning start must be in HH:MM format.',
            'morning_end.date_format' => 'Morning end must be in HH:MM format.',
            'morning_end.after' => 'Morning end must be after morning start.',
            'has_evening.boolean' => 'Has evening must be true or false.',
            'evening_start.date_format' => 'Evening start must be in HH:MM format.',
            'evening_end.date_format' => 'Evening end must be in HH:MM format.',
            'evening_end.after' => 'Evening end must be after evening start.',
            'age_ranges.array' => 'Age ranges must be an array.',
            'age_ranges.*.string' => 'Each age range must be a string.',
        ];
    }
}
