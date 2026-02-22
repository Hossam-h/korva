<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'sometimes|string|max:255',
            'type'           => 'sometimes|in:indoor,outdoor',
            'status'         => 'sometimes|in:available,unavailable,maintenance',
            'available_from' => 'sometimes|date_format:H:i',
            'available_to'   => 'sometimes|date_format:H:i|after:available_from',
            'day_of_week'    => 'sometimes|array',
            'day_of_week.*'  => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'                    => 'Field type must be indoor or outdoor.',
            'status.in'                  => 'Status must be available, unavailable, or maintenance.',
            'available_from.date_format' => 'Available from must be in HH:MM format.',
            'available_to.date_format'   => 'Available to must be in HH:MM format.',
            'available_to.after'         => 'Available to must be after available from.',
            'day_of_week.array'          => 'Day of week must be an array.',
            'day_of_week.*.in'           => 'Each day must be a valid weekday name.',
        ];
    }
}
