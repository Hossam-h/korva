<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PlayerResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reset_token'           => 'required|string',
            'password'              => ['required', 'string', 'confirmed', Password::defaults()],
            'password_confirmation' => 'required|string',
        ];
    }
}
