<?php

namespace App\Models;

use App\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    use BelongsToAcademy;

    protected $fillable = [
        'academy_id',
        'full_name',
        'phone',
        'email',
        'training_category',
        'bio',
    ];


    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'coach_group');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(CoachLicense::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(CoachTournament::class);
    }
}
