<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $academyId = $this->route('academy');

        return [
            'name'                 => 'sometimes|string|max:255',
            'email'                => 'sometimes|email|unique:academies,email,' . $academyId,
            'phone'                => 'sometimes|string|unique:academies,phone,' . $academyId,
            'age_group'            => 'sometimes|nullable|string|max:255',
            'country'              => 'sometimes|string|max:255',
            'city'                 => 'sometimes|string|max:255',
            'address'              => 'sometimes|string|max:255',
            'business_owner_email' => 'sometimes|email|max:255',
            'business_owner_phone' => 'sometimes|string|max:255',
            'is_active'            => 'sometimes|boolean',
        ];
    }
}
