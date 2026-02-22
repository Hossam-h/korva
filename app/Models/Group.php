<?php

namespace App\Models;

use App\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use BelongsToAcademy;

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
