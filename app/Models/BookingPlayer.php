<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPlayer extends Model
{
    protected $fillable = [
        'booking_id',
        'player_id',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** The participant (child or the player themselves) in this booking. */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
