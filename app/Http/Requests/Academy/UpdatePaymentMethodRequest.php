<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id'  => 'sometimes|integer|exists:payment_providers,id',
            'is_default'   => 'sometimes|boolean',
            'holder_name'  => 'sometimes|string|max:255',
            'card_number'  => 'sometimes|string|max:19',
            'expiry_date'  => 'sometimes|string|max:7',
            'cvv'          => 'sometimes|string|max:4',
            'status'       => 'sometimes|in:active,disabled,expired',
        ];
    }

    public function messages(): array
    {
        return [
            'provider_id.exists' => 'The selected payment provider does not exist.',
            'status.in'          => 'Status must be active, disabled, or expired.',
        ];
    }
}
