<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Academy extends Authenticatable implements JWTSubject
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'age_group',
        'country',
        'city',
        'address',
        'business_owner_email',
        'business_owner_phone',
        'is_active',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
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


    

    // public function getJWTCustomClaims()
    // {
    //     return [];
    // }

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
}
