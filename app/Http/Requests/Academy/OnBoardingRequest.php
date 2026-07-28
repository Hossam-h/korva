<?php

namespace App\Http\Requests\Academy;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OnBoardingRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'age_group' => 'required',
            'country' => 'required',
            'city' => 'required',
            'address' => 'required',
            'owner_name' => 'required',
            'business_owner_email' => 'required',
            'business_owner_phone' => 'required',
            'description' => 'sometimes|nullable|string|max:5000',
            'min_age' => 'sometimes|nullable|integer|min:1|max:100',
            'max_age' => 'sometimes|nullable|integer|gte:min_age|max:100',
            'accepted_genders' => 'sometimes|array|min:1',
            'accepted_genders.*' => 'in:male,female',
            'currency' => 'sometimes|string|size:3',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'operating_hours' => 'sometimes|array',
            'operating_hours.*.day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'operating_hours.*.opens_at' => 'required|date_format:H:i',
            'operating_hours.*.closes_at' => 'required|date_format:H:i|after:operating_hours.*.opens_at',

            // Attachments (optional array)
            'attachments' => 'sometimes|array',
            'attachments.*.attach_type' => 'required_with:attachments|string',
            'attachments.*.attach_path' => 'required_with:attachments|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ];
    }
}
