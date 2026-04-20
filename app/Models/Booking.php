<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'academy_id',
        'group_id',
        'player_id',
        'booking_type',
        'session_date',
        'session_start_time',
        'session_end_time',
        'duration_months',
        'status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'coupon_id',
        'coupon_code',
        'payment_method_type',
        'payment_status',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'session_date'     => 'date',
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_amount'     => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /** The account holder who created this booking (player or parent). */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /** All participants booked under this booking (can be multiple children). */
    public function bookingPlayers(): HasMany
    {
        return $this->hasMany(BookingPlayer::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForAcademy($query, int $academyId)
    {
        return $query->where('academy_id', $academyId);
    }

    public function scopeForPlayer($query, int $playerId)
    {
        return $query->where('player_id', $playerId);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
}
