<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $guarded = [];

    protected $casts = [
        'valid_from'  => 'date',
        'valid_until' => 'date',
        'is_active'   => 'boolean',
    ];

    /** All bookings that used this coupon. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    
    


}
