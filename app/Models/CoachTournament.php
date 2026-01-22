<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachTournament extends Model
{
    protected $fillable = [
        'coach_id',
        'tournament_name',
        'achievement',
        'tournament_date',
    ];

    protected $casts = [
        'tournament_date' => 'date',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}
