<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class PlayerCompleteProfileRequest extends FormRequest
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
            'gender'           => 'nullable|in:male,female',
            'type'             => 'nullable|in:parent,player',
            'address'          => 'nullable|string|max:500',
            'birth_date'       => 'nullable|date',
            'weight'           => 'nullable|numeric|min:0',
            'has_health_issues' => 'nullable|boolean',
            'health_issues'    => 'nullable|string|max:1000',
        ];
    }
}
