<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Player must be authenticated via the 'player' JWT guard
        return auth('player')->check();
    }

    public function rules(): array
    {
        return [
            // ── Core fields ────────────────────────────────────────────────
            'academy_id'           => ['required', 'integer', 'exists:academies,id'],
            'group_id'             => ['required', 'integer', 'exists:groups,id'],
            'booking_type'         => ['required', 'string', 'in:single,monthly'],

            // ── Single-session fields (required only when booking_type=single) ──
            'session_date'         => ['required_if:booking_type,single', 'nullable', 'date', 'after_or_equal:today'],
            'session_start_time'   => ['nullable', 'date_format:H:i'],
            'session_end_time'     => ['nullable', 'date_format:H:i', 'after:session_start_time'],

            // ── Monthly-subscription fields ─────────────────────────────────
            'duration_months'      => ['required_if:booking_type,monthly', 'nullable', 'integer', 'in:1,2,3'],

            // ── Participants (the children / players being booked) ──────────
            'player_ids'           => ['required', 'array', 'min:1'],
            'player_ids.*'         => ['integer', 'exists:players,id'],

            // ── Optional fields ─────────────────────────────────────────────
            'coupon_code'          => ['nullable', 'string', 'exists:coupons,code'],
            'payment_method_type'  => ['nullable', 'string', 'in:card,apple_pay,stc_pay,other'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'academy_id.exists'           => 'The selected academy does not exist.',
            'group_id.exists'             => 'The selected group does not exist.',
            'booking_type.in'             => 'Booking type must be either "single" or "monthly".',
            'session_date.required_if'    => 'A session date is required for single bookings.',
            'session_date.after_or_equal' => 'Session date must be today or a future date.',
            'duration_months.required_if' => 'Duration in months is required for monthly bookings.',
            'duration_months.in'          => 'Duration must be 1, 2, or 3 months.',
            'player_ids.required'         => 'At least one player must be selected.',
            'player_ids.*.exists'         => 'One or more selected players do not exist.',
            'coupon_code.exists'          => 'The coupon code is invalid.',
            'payment_method_type.in'      => 'Invalid payment method type.',
        ];
    }
}
