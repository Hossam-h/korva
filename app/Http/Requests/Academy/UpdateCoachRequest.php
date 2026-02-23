<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Coach fields
            'full_name'         => 'sometimes|string|max:255',
            'phone'             => 'sometimes|nullable|string|max:30',
            'email'             => 'sometimes|nullable|email|max:255',
            'training_category' => 'sometimes|nullable|string|max:255',
            'bio'               => 'sometimes|nullable|string',

            // Many-to-many groups
            'group_ids'         => 'sometimes|array',
            'group_ids.*'       => 'integer|exists:groups,id',

            // Licenses — optional; if sent, replaces all existing licenses
            'licenses'                      => 'sometimes|array',
            'licenses.*.license_name'       => 'required|string|max:255',
            'licenses.*.issuing_authority'  => 'sometimes|nullable|string|max:255',
            'licenses.*.obtained_at'        => 'sometimes|nullable|date',

            // Tournaments — optional; if sent, replaces all existing tournaments
            'tournaments'                       => 'sometimes|array',
            'tournaments.*.tournament_name'     => 'required|string|max:255',
            'tournaments.*.achievement'         => 'sometimes|nullable|string|max:255',
            'tournaments.*.tournament_date'     => 'sometimes|nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'                           => 'Please provide a valid email address.',
            'group_ids.array'                       => 'Group IDs must be an array.',
            'group_ids.*.integer'                   => 'Each group ID must be an integer.',
            'group_ids.*.exists'                    => 'One or more selected groups do not exist.',
            'licenses.array'                        => 'Licenses must be an array.',
            'licenses.*.license_name.required'      => 'Each license must have a name.',
            'tournaments.array'                     => 'Tournaments must be an array.',
            'tournaments.*.tournament_name.required' => 'Each tournament must have a name.',
        ];
    }
}
