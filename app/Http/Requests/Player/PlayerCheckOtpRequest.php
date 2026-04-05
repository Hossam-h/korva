<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class PlayerCheckOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact' => 'required|string',
            'otp'     => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'contact.required' => __('validation.required', ['attribute' => 'email or phone']),
            'otp.required'     => __('validation.required', ['attribute' => 'otp']),
        ];
    }
}
