<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $playerId = $this->route('player');

        return [
            'first_name'       => 'sometimes|string|max:255',
            'last_name'        => 'sometimes|string|max:255',
            'email'            => 'sometimes|nullable|email|unique:players,email,' . $playerId . '|max:255',
            'phone'            => 'sometimes|string|unique:players,phone,' . $playerId . '|max:20',
            'gender'           => 'sometimes|in:male,female',
            'type'             => 'sometimes|string|max:100',
            'address'          => 'sometimes|string|max:500',
            'birth_date'       => 'sometimes|date',
            'weight'           => 'sometimes|numeric|min:0',
            'has_health_issues'=> 'sometimes|boolean',
            'health_issues'    => 'sometimes|nullable|string|max:1000',
            'group_id'         => 'sometimes|exists:groups,id',
            'period'           => 'sometimes|nullable|string|max:100',
            'password'         => 'sometimes|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'          => 'Email must be a valid email address.',
            'email.unique'         => 'Email is already taken.',
            'phone.unique'         => 'Phone number is already taken.',
            'gender.in'            => 'Gender must be male or female.',
            'birth_date.date'      => 'Birth date must be a valid date.',
            'weight.numeric'       => 'Weight must be a number.',
            'has_health_issues.boolean' => 'Health issues flag must be true or false.',
            'password.min'         => 'Password must be at least 8 characters.',
        ];
    }
}
