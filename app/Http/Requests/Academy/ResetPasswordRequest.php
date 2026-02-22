<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_number' => 'required|string',
            'password' => 'required|string',
            'password_confirmation' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'contact_number.required' => 'Contact number is required.',
            'password.required' => 'Password is required.',
            'password_confirmation.required' => 'Password confirmation is required.',
        ];
    }
}
