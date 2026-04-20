<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Player\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends BaseController
{
    public function __construct(protected BookingService $bookingService) {}

    // ─── POST /api/player/bookings ────────────────────────────────────────────

    /**
     * Create a new booking.
     * A player (or parent) books a spot in a group at an academy.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $player  = auth('player')->user();
            $booking = $this->bookingService->create($request->validated(), $player);

            return $this->sendResponse($booking, __('message.booking_created'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    // ─── GET /api/player/bookings ─────────────────────────────────────────────

    /**
     * List all bookings for the authenticated player.
     */
    public function index(): JsonResponse
    {
        $player   = auth('player')->user();
        $bookings = $this->bookingService->listForPlayer($player);

        return $this->sendResponse($bookings, 'Bookings retrieved successfully.');
    }

    // ─── GET /api/player/bookings/{id} ────────────────────────────────────────

    /**
     * Show a single booking that belongs to the authenticated player.
     */
    public function show(int $id): JsonResponse
    {
        $player  = auth('player')->user();
        $booking = Booking::with(['academy', 'group.coaches', 'bookingPlayers.player', 'coupon'])
            ->where('player_id', $player->id)
            ->findOrFail($id);

        return $this->sendResponse($booking, 'Booking retrieved successfully.');
    }

    // ─── POST /api/player/bookings/{id}/cancel ───────────────────────────────

    /**
     * Cancel a booking owned by the authenticated player.
     */
    public function cancel(int $id): JsonResponse
    {
        $player  = auth('player')->user();
        $booking = Booking::where('player_id', $player->id)->findOrFail($id);

        try {
            $booking = $this->bookingService->cancel($booking);

            return $this->sendResponse($booking, __('message.booking_cancelled'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    // ─── GET /api/player/groups/{groupId}/slots?date=YYYY-MM-DD ─────────────

    /**
     * Get available training time slots for a group on a specific date.
     * Returns an empty array if the date's weekday doesn't match the group's training days.
     */
    public function availableSlots(int $groupId): JsonResponse
    {
        $date = request()->query('date');

        if (! $date) {
            return $this->sendError('The date parameter is required.', [], 422);
        }

        try {
            $slots = $this->bookingService->getAvailableSlots($groupId, $date);

            return $this->sendResponse($slots, 'Available slots retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    // ─── POST /api/player/bookings/{id}/confirm ──────────────────────────────

    /**
     * Confirm a booking (typically called after a successful payment callback).
     * For internal/webhook use — or for test environments.
     */
    public function confirm(int $id): JsonResponse
    {
        $player  = auth('player')->user();
        $booking = Booking::where('player_id', $player->id)->findOrFail($id);

        $paymentReference = request()->input('payment_reference');

        $booking = $this->bookingService->confirm($booking, $paymentReference);

        return $this->sendResponse($booking, __('message.booking_confirmed'));
    }
}
