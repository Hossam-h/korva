<?php

namespace App\Models;

use App\Traits\HasFileAttachment;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Player extends Authenticatable implements JWTSubject
{
    use HasFileAttachment;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'type',
        'address',
        'latitude',
        'longitude',
        'birth_date',
        'weight',
        'image',
        'has_health_issues',
        'health_issues',
        'other_health_issue',
        'parent_contact',
        'provider',
        'provider_id',
        'group_id',
        'period',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'password' => 'hashed',
        'latitude' => 'float',
        'longitude' => 'float',
        'has_health_issues' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'is_profile_completed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /** Bookings this player created as the account holder (payer). */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /** Booking slots where this player is a participant (child). */
    public function bookingSlots()
    {
        return $this->hasMany(BookingPlayer::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /** Public URL for the player's profile image (null when none set). */
    public function getImageUrlAttribute(): ?string
    {
        return $this->getFileUrl('image');
    }

    /**
     * Tells the app whether to route the user into the profile-completion /
     * account-type screens or straight into the app. `type` is left null by
     * OTP/social registration until complete-profile sets it; first/last name
     * are blank on OTP signup and on Apple (which never sends a name) until
     * complete-profile sets those too.
     */
    public function getIsProfileCompletedAttribute(): bool
    {
        return ! is_null($this->type)
            && filled($this->first_name)
            && filled($this->last_name);
    }

    protected function getFileFields(): array
    {
        return ['image'];
    }

    public function scopeFilter($query)
    {
        return $query->when(request('group_id'), function ($q, $groupId) {
            $q->where('group_id', $groupId);
        })->when(request('subscription'), function ($q, $subscription) {
            $q->whereHas('bookings', function ($b) use ($subscription) {
                $b->where('booking_type', $subscription);
            });
        })->when(request('payment_status'), function ($q, $status) {
            $q->whereHas('bookings', function ($b) use ($status) {
                $b->where('payment_status', $status);
            });
        })->when(request('attendance'), function ($q, $attendance) {
            $q->whereHas('bookings', function ($b) use ($attendance) {
                if ($attendance === 'attended') {
                    $b->where('status', 'completed');
                } elseif ($attendance === 'absent') {
                    $b->where('status', 'cancelled');
                } elseif ($attendance === 'upcoming') {
                    $b->whereIn('status', ['pending', 'confirmed']);
                }
            });
        })->when(request('age'), function ($q, $age) {
            $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) = ?', [(int) $age]);
        })->when(request('min_age'), function ($q, $minAge) {
            $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [(int) $minAge]);
        })->when(request('max_age'), function ($q, $maxAge) {
            $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [(int) $maxAge]);
        })->when(request('joined_from'), function ($q, $from) {
            $q->whereDate('created_at', '>=', $from);
        })->when(request('joined_to'), function ($q, $to) {
            $q->whereDate('created_at', '<=', $to);
        });
    }
}
