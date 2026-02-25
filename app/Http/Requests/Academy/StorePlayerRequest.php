<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class StorePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'email'            => 'nullable|email|unique:players,email|max:255',
            'phone'            => 'required|string|unique:players,phone|max:20',
            'gender'           => 'required|in:male,female',
            'type'             => 'sometimes|string|max:100',
            'address'          => 'sometimes|string|max:500',
            'birth_date'       => 'required|date',
            'weight'           => 'sometimes|numeric|min:0',
            'has_health_issues'=> 'sometimes|boolean',
            'health_issues'    => 'sometimes|nullable|string|max:1000',
            'group_id'         => 'required|exists:groups,id',
            'period'           => 'sometimes|nullable|string|max:100',
            'password'         => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'  => 'First name is required.',
            'last_name.required'   => 'Last name is required.',
            'email.email'          => 'Email must be a valid email address.',
            'email.unique'         => 'Email is already taken.',
            'phone.required'       => 'Phone number is required.',
            'phone.unique'         => 'Phone number is already taken.',
            'gender.required'      => 'Gender is required.',
            'gender.in'            => 'Gender must be male or female.',
            'birth_date.required'  => 'Birth date is required.',
            'birth_date.date'      => 'Birth date must be a valid date.',
            'weight.numeric'       => 'Weight must be a number.',
            'has_health_issues.boolean' => 'Health issues flag must be true or false.',
            'password.required'    => 'Password is required.',
            'password.min'         => 'Password must be at least 8 characters.',
        ];
    }
}
