<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingPlayer;
use App\Models\Coupon;
use App\Models\Group;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Create a new booking for a player / parent.
     *
     * @param  array   $data    Validated booking data (from StoreBookingRequest)
     * @param  Player  $player  The authenticated player (account holder / payer)
     * @return Booking
     *
     * @throws \Exception
     */
    public function create(array $data, Player $player): Booking
    {
        $group = Group::findOrFail($data['group_id']);

        // ── Capacity check ───────────────────────────────────────────────────
        if ($group->capacity !== null) {
            $participantCount = count($data['player_ids']);
            $occupied = BookingPlayer::whereHas('booking', function ($q) use ($group) {
                $q->where('group_id', $group->id)
                  ->whereIn('status', ['pending', 'confirmed']);
            })->count();

            if (($occupied + $participantCount) > $group->capacity) {
                throw new \Exception(
                    "Group is at capacity. Only " . max(0, $group->capacity - $occupied) . " spot(s) remaining."
                );
            }
        }

        // ── Compute pricing ──────────────────────────────────────────────────
        $participantCount = count($data['player_ids']);

        if ($data['booking_type'] === 'single') {
            $unitPrice = (float) ($group->session_price ?? 0);
            $subtotal  = $unitPrice * $participantCount;
        } else {
            // monthly: price × months × participants
            $unitPrice = (float) ($group->monthly_price ?? 0);
            $months    = (int) ($data['duration_months'] ?? 1);
            $subtotal  = $unitPrice * $months * $participantCount;
        }

        // ── Coupon / Discount ────────────────────────────────────────────────
        $discountAmount = 0;
        $coupon         = null;

        if (! empty($data['coupon_code'])) {
            [$coupon, $discountAmount] = $this->applyCoupon($data['coupon_code'], $subtotal);
        }

        $totalAmount = max(0, $subtotal - $discountAmount);

        // ── Persist ──────────────────────────────────────────────────────────
        return DB::transaction(function () use ($data, $player, $group, $subtotal, $discountAmount, $totalAmount, $coupon) {
            $booking = Booking::create([
                'academy_id'          => $data['academy_id'],
                'group_id'            => $group->id,
                'player_id'           => $player->id,
                'booking_type'        => $data['booking_type'],
                'session_date'        => $data['session_date'] ?? null,
                'session_start_time'  => $data['session_start_time'] ?? null,
                'session_end_time'    => $data['session_end_time'] ?? null,
                'duration_months'     => $data['duration_months'] ?? null,
                'status'              => 'pending',
                'subtotal'            => $subtotal,
                'discount_amount'     => $discountAmount,
                'total_amount'        => $totalAmount,
                'coupon_id'           => $coupon?->id,
                'coupon_code'         => $coupon?->code,
                'payment_method_type' => $data['payment_method_type'] ?? null,
                'payment_status'      => 'unpaid',
                'notes'               => $data['notes'] ?? null,
            ]);

            // Insert participants
            foreach ($data['player_ids'] as $participantId) {
                BookingPlayer::create([
                    'booking_id' => $booking->id,
                    'player_id'  => $participantId,
                ]);
            }

            return $booking->load(['academy', 'group', 'bookingPlayers.player', 'coupon']);
        });
    }

    /**
     * Validate a coupon code and compute the discount amount.
     *
     * @return array{Coupon, float}  [coupon, discountAmount]
     *
     * @throws \Exception
     */
    public function applyCoupon(string $code, float $subtotal): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw new \Exception('Coupon not found.');
        }

        if (! $coupon->is_active) {
            throw new \Exception('This coupon is no longer active.');
        }

        $today = Carbon::today();

        if ($coupon->valid_from && $today->lt($coupon->valid_from)) {
            throw new \Exception('This coupon is not valid yet.');
        }

        if ($coupon->valid_until && $today->gt($coupon->valid_until)) {
            throw new \Exception('This coupon has expired.');
        }

        if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) {
            throw new \Exception('This coupon has reached its usage limit.');
        }

        // Calculate discount
        if ($coupon->discount_type === 'percentage') {
            $discount = round(($subtotal * $coupon->discount_value) / 100, 2);
        } else {
            $discount = min((float) $coupon->discount_value, $subtotal);
        }

        return [$coupon, $discount];
    }

    /**
     * Increment the coupon usage count after a booking is confirmed/paid.
     */
    public function incrementCouponUsage(Booking $booking): void
    {
        if ($booking->coupon_id) {
            Coupon::where('id', $booking->coupon_id)->increment('usage_count');
        }
    }

    /**
     * Confirm a booking (e.g. after payment gateway callback).
     */
    public function confirm(Booking $booking, ?string $paymentReference = null): Booking
    {
        $booking->update([
            'status'             => 'confirmed',
            'payment_status'     => 'paid',
            'payment_reference'  => $paymentReference,
        ]);

        $this->incrementCouponUsage($booking);

        return $booking->fresh();
    }

    /**
     * Cancel a booking.
     *
     * @throws \Exception
     */
    public function cancel(Booking $booking): Booking
    {
        if (! $booking->isCancellable()) {
            throw new \Exception('This booking cannot be cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        return $booking->fresh();
    }

    /**
     * Get available time slots for a group on a given date.
     * Returns empty array if the date's weekday doesn't match group's training days.
     *
     * @return array{start_time: string, end_time: string}[]
     */
    public function getAvailableSlots(int $groupId, string $date): array
    {
        $group = Group::findOrFail($groupId);
        $carbon = Carbon::parse($date);

        // Map PHP's dayOfWeek (0=Sun..6=Sat) to Arabic day names used in the system
        $dayMap = [
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ];

        // Also support English abbreviated day names
        $dayMapEn = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];

        $dayOfWeek  = $carbon->dayOfWeek;
        $arabicDay  = $dayMap[$dayOfWeek];
        $englishDay = $dayMapEn[$dayOfWeek];

        $groupDays = array_map('mb_strtolower', (array) $group->days);

        $matches = in_array(mb_strtolower($arabicDay), $groupDays)
                || in_array($englishDay, $groupDays);

        if (! $matches) {
            return [];
        }

        // The group has a single daily training window — return it as one slot
        return [
            [
                'start_time' => $group->start_time,
                'end_time'   => $group->end_time,
            ],
        ];
    }

    /**
     * Get paginated bookings for the authenticated player.
     */
    public function listForPlayer(Player $player, int $perPage = 15)
    {
        return Booking::forPlayer($player->id)
            ->with(['academy', 'group', 'bookingPlayers.player', 'coupon'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get paginated bookings for a given academy.
     */
    public function listForAcademy(int $academyId, int $perPage = 15)
    {
        return Booking::forAcademy($academyId)
            ->with(['player', 'group', 'bookingPlayers.player', 'coupon'])
            ->latest()
            ->paginate($perPage);
    }
}
