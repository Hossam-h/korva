<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyAcademicSetting extends Model
{
    protected $fillable = [
        'academy_id',
        'work_days',
        'morning_start',
        'morning_end',
        'has_evening',
        'evening_start',
        'evening_end',
        'age_ranges',
    ];

    protected $casts = [
        'work_days' => 'array',
        'morning_start' => 'datetime:H:i',
        'morning_end' => 'datetime:H:i',
        'has_evening' => 'boolean',
        'evening_start' => 'datetime:H:i',
        'evening_end' => 'datetime:H:i',
        'age_ranges' => 'array',
    ];

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }
}
