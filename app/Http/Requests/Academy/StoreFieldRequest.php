<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:indoor,outdoor',
            'status'         => 'sometimes|in:available,unavailable,maintenance',
            'available_from' => 'required|date_format:H:i',
            'available_to'   => 'required|date_format:H:i|after:available_from',
            'day_of_week'    => 'sometimes|array',
            'day_of_week.*'  => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Field name is required.',
            'type.required'           => 'Field type is required.',
            'type.in'                 => 'Field type must be indoor or outdoor.',
            'status.in'               => 'Status must be available, unavailable, or maintenance.',
            'available_from.required' => 'Available from time is required.',
            'available_from.date_format' => 'Available from must be in HH:MM format.',
            'available_to.required'   => 'Available to time is required.',
            'available_to.date_format'   => 'Available to must be in HH:MM format.',
            'available_to.after'      => 'Available to must be after available from.',
            'day_of_week.array'       => 'Day of week must be an array.',
            'day_of_week.*.in'        => 'Each day must be a valid weekday name.',
        ];
    }
}
