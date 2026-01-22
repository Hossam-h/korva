<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachLicense extends Model
{
    protected $fillable = [
        'coach_id',
        'license_name',
        'issuing_authority',
        'obtained_at',
    ];

    protected $casts = [
        'obtained_at' => 'date',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}
