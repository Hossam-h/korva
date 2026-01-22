<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Player extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'type',
        'address',
        'birth_date',
        'weight',
        'has_health_issues',
        'health_issues',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'password' => 'hashed',
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
        return [
            'guard' => 'player',
        ];
    }

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }
}
