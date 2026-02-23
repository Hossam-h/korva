<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Coach fields
            'full_name'         => 'required|string|max:255',
            'phone'             => 'sometimes|nullable|string|max:30',
            'email'             => 'sometimes|nullable|email|max:255',
            'training_category' => 'sometimes|nullable|string|max:255',
            'bio'               => 'sometimes|nullable|string',

            // Many-to-many groups
            'group_ids'         => 'required|array',
            'group_ids.*'       => 'integer|exists:groups,id',

            // Licenses (required, at least one)
            'licenses'                      => 'required|array|min:1',
            'licenses.*.license_name'       => 'required|string|max:255',
            'licenses.*.issuing_authority'  => 'sometimes|nullable|string|max:255',
            'licenses.*.obtained_at'        => 'sometimes|nullable|date',

            // Tournaments (required, at least one)
            'tournaments'                       => 'required|array|min:1',
            'tournaments.*.tournament_name'     => 'required|string|max:255',
            'tournaments.*.achievement'         => 'sometimes|nullable|string|max:255',
            'tournaments.*.tournament_date'     => 'sometimes|nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required'                    => 'Coach full name is required.',
            'email.email'                           => 'Please provide a valid email address.',
            'group_ids.required'                    => 'Group IDs are required.',
            'group_ids.array'                       => 'Group IDs must be an array.',
            'group_ids.*.integer'                   => 'Each group ID must be an integer.',
            'group_ids.*.exists'                    => 'One or more selected groups do not exist.',
            'licenses.required'                     => 'At least one license is required.',
            'licenses.array'                        => 'Licenses must be an array.',
            'licenses.min'                          => 'At least one license is required.',
            'licenses.*.license_name.required'      => 'Each license must have a name.',
            'tournaments.required'                  => 'At least one tournament is required.',
            'tournaments.array'                     => 'Tournaments must be an array.',
            'tournaments.min'                       => 'At least one tournament is required.',
            'tournaments.*.tournament_name.required' => 'Each tournament must have a name.',
        ];
    }
}
