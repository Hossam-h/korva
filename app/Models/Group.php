<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'academy_id',
        'field_id',
        'name',
        'training_category',
        'color_code',
        'start_time',
        'end_time',
        'days',
    ];

    protected $casts = [
        'days' => 'array',
    ];

    /**
     * Get the academy that owns the group.
     */
    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }

    /**
     * Get the field that owns the group.
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Get the coaches for the group.
     */
    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(Coach::class, 'coach_group', 'group_id', 'coach_id');
    }
}
