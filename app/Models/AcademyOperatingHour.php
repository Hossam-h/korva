<?php

namespace App\Models;

use App\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Model;

class AcademyOperatingHour extends Model
{
    use BelongsToAcademy;

    protected $fillable = [
        'academy_id',
        'day',
        'opens_at',
        'closes_at',
    ];

    protected $casts = [
        'opens_at' => 'datetime:H:i',
        'closes_at' => 'datetime:H:i',
    ];
}
