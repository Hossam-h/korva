<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Field extends Model
{
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

    /**
     * Get the academy that owns the field.
     */
    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }
}
