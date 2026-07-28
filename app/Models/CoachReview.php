<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachReview extends Model
{
    protected $fillable = [
        'coach_id',
        'player_id',
        'rating',
        'comment',
    ];

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
