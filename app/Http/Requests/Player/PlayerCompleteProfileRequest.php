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
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'gender'             => 'nullable|in:male,female',
            'type'               => 'nullable|in:parent,player',
            'address'            => 'nullable|string|max:500',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'birth_date'         => 'nullable|date',
            'weight'             => 'nullable|numeric|min:0',
            'has_health_issues'  => 'nullable|boolean',
            'health_issues'      => 'nullable|string|max:1000',
            'other_health_issue' => 'nullable|string|max:1000',
            // Guardian contact captured for future auto-linking (stored, not yet linked).
            'parent_contact'     => 'nullable|string|max:255',
            // Profile image upload (handled via HasFileAttachment in the controller).
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
