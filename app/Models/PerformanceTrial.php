<?php

namespace App\Models;

use App\Traits\HasFileAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceTrial extends Model
{
    use HasFileAttachment;

    protected $fillable = [
        'academy_id',
        'title',
        'age_category',
        'max_players',
        'start_date',
        'start_time',
        'end_time',
        'thumbnail',
        'status',
        'day_of_week',
    ];

    protected $casts = [
        'start_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'day_of_week' => 'array',
    ];

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }

    /**
     * Override to specify file fields for this model.
     */
    protected function getFileFields(): array
    {
        return ['thumbnail'];
    }
}
