<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $academyId = auth('academy')->id();

        return [
            'field_id'          => "required|integer|exists:fields,id,academy_id,{$academyId}",
            'name'              => 'required|string|max:255',
            'training_category' => 'required|string|max:255',
            'color_code'        => 'sometimes|nullable|string|max:7',
            'start_time'        => 'required|date_format:H:i',
            'end_time'          => 'required|date_format:H:i|after:start_time',
            'days'              => 'required|array|min:1',
            'days.*'            => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }

    public function messages(): array
    {
        return [
            'field_id.required'          => 'Field is required.',
            'field_id.exists'            => 'The selected field does not belong to your academy.',
            'name.required'              => 'Group name is required.',
            'training_category.required' => 'Training category is required.',
            'color_code.max'             => 'Color code must be a valid hex color (e.g. #FFFFFF).',
            'start_time.required'        => 'Start time is required.',
            'start_time.date_format'     => 'Start time must be in HH:MM format.',
            'end_time.required'          => 'End time is required.',
            'end_time.date_format'       => 'End time must be in HH:MM format.',
            'end_time.after'             => 'End time must be after start time.',
            'days.required'              => 'At least one day is required.',
            'days.array'                 => 'Days must be an array.',
            'days.*.in'                  => 'Each day must be a valid weekday name.',
        ];
    }
}
