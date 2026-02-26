<?php

namespace App\Http\Requests\Academy;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => 'required|integer|exists:payment_providers,id',
            'is_default' => 'sometimes|boolean',
            'holder_name' => 'required|string|max:255',
            'card_number' => 'required|string|max:19',
            'expiry_date' => 'required|string|max:7',
            'cvv' => 'required|string|max:4',
            'status' => 'sometimes|in:active,disabled,expired',
        ];
    }

    public function messages(): array
    {
        return [
            'provider_id.required' => 'Payment provider is required.',
            'provider_id.exists' => 'The selected payment provider does not exist.',
            'holder_name.required' => 'Card holder name is required.',
            'card_number.required' => 'Card number is required.',
            'expiry_date.required' => 'Expiry date is required.',
            'cvv.required' => 'CVV is required.',
            'status.in' => 'Status must be active, disabled, or expired.',
        ];
    }
}
