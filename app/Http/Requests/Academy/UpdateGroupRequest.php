<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $academyId = auth('academy')->id();

        return [
            'field_id'          => "sometimes|integer|exists:fields,id,academy_id,{$academyId}",
            'name'              => 'sometimes|string|max:255',
            'training_category' => 'sometimes|string|max:255',
            'color_code'        => 'sometimes|nullable|string|max:7',
            'start_time'        => 'sometimes|date_format:H:i',
            'end_time'          => 'sometimes|date_format:H:i|after:start_time',
            'days'              => 'sometimes|array|min:1',
            'days.*'            => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }

    public function messages(): array
    {
        return [
            'field_id.exists'        => 'The selected field does not belong to your academy.',
            'color_code.max'         => 'Color code must be a valid hex color (e.g. #FFFFFF).',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'end_time.date_format'   => 'End time must be in HH:MM format.',
            'end_time.after'         => 'End time must be after start time.',
            'days.array'             => 'Days must be an array.',
            'days.*.in'              => 'Each day must be a valid weekday name.',
        ];
    }
}
