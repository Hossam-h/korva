<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademyGeneralSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => 'sometimes|string|max:10',
            'timezone' => 'sometimes|string|timezone',
            'phone'    => 'sometimes|nullable|string|max:20',
            'email'    => 'sometimes|nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'language.string'   => 'Language must be a string.',
            'timezone.timezone' => 'Timezone must be a valid timezone.',
            'phone.string'      => 'Phone must be a string.',
            'email.email'       => 'Email must be a valid email address.',
        ];
    }
}
