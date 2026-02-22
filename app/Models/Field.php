<?php

namespace App\Models;

use App\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use BelongsToAcademy;

    protected $fillable = [
        'academy_id',
        'name',
        'type',
        'status',
        'available_from',
        'available_to',
        'day_of_week',
    ];

    protected $casts = [
        'available_from' => 'datetime:H:i',
        'available_to' => 'datetime:H:i',
        'day_of_week' => 'array',
    ];
}
