<?php

namespace App\Models;

use App\Traits\HasFileAttachment;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Academy extends Authenticatable implements JWTSubject
{
    use HasFileAttachment;
    use SoftDeletes;

    /** Average urban travel speed used to estimate travel_time (minutes). */
    private const TRAVEL_SPEED_KMH = 30;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'age_group',
        'country',
        'city',
        'address',
        'image',
        'latitude',
        'longitude',
        'owner_name',
        'business_owner_email',
        'business_owner_phone',
        'is_active',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'image',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
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

    public function academicSetting(): HasOne
    {
        return $this->hasOne(AcademyAcademicSetting::class);
    }

    public function generalSetting(): HasOne
    {
        return $this->hasOne(AcademyGeneralSetting::class);
    }

    public function notificationSetting(): HasOne
    {
        return $this->hasOne(AcademyNotificationSetting::class);
    }

    public function attaches()
    {
        return $this->hasMany(AcademyAttach::class);
    }

    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function reviews()
    {
        return $this->hasMany(AcademyReview::class);
    }

    public function coaches()
    {
        return $this->hasMany(Coach::class);
    }

    /** All bookings received by this academy. */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /** Players who favorited this academy. */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'academy_favorites')
            ->withTimestamps();
    }

    /** Public URL for the academy image. */
    public function getImageUrlAttribute(): ?string
    {
        return $this->getFileUrl('image');
    }

    /**
     * Starting price for list cards: cheapest group session_price.
     * Prefers `groups_min_session_price` from withMin() when present.
     */
    public function getPriceAttribute(): ?float
    {
        if (array_key_exists('groups_min_session_price', $this->attributes)) {
            $value = $this->attributes['groups_min_session_price'];

            return $value !== null ? (float) $value : null;
        }

        if ($this->relationLoaded('groups')) {
            $min = $this->groups
                ->pluck('session_price')
                ->filter(fn ($p) => $p !== null)
                ->min();

            return $min !== null ? (float) $min : null;
        }

        $min = $this->groups()->whereNotNull('session_price')->min('session_price');

        return $min !== null ? (float) $min : null;
    }

    /**
     * Estimated travel time in minutes from the authenticated player (or request coords).
     * Returns null when either side lacks coordinates.
     */
    public function getTravelTimeAttribute(): ?int
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        [$lat, $lng] = $this->resolveViewerCoordinates();

        if ($lat === null || $lng === null) {
            return null;
        }

        $distanceKm = $this->haversineKm(
            (float) $lat,
            (float) $lng,
            (float) $this->latitude,
            (float) $this->longitude
        );

        return (int) max(1, round(($distanceKm / self::TRAVEL_SPEED_KMH) * 60));
    }

    /** Whether the authenticated player has favorited this academy. */
    public function getIsFavoriteAttribute(): bool
    {
        if (array_key_exists('is_favorite', $this->attributes)) {
            return (bool) $this->attributes['is_favorite'];
        }

        $player = auth('player')->user();

        if (! $player) {
            return false;
        }

        if ($this->relationLoaded('favoritedBy')) {
            return $this->favoritedBy->contains('id', $player->id);
        }

        return $this->favoritedBy()->where('player_id', $player->id)->exists();
    }

    protected function getFileFields(): array
    {
        return ['image'];
    }

    protected function getDefaultFolder(): string
    {
        return 'academies';
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveViewerCoordinates(): array
    {
        $request = request();

        if ($request && $request->filled(['latitude', 'longitude'])) {
            return [
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
            ];
        }

        $player = auth('player')->user();

        if ($player && $player->latitude !== null && $player->longitude !== null) {
            return [(float) $player->latitude, (float) $player->longitude];
        }

        return [null, null];
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
