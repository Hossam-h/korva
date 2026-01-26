<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyGeneralSetting extends Model
{
    protected $fillable = [
        'academy_id',
        'language',
        'timezone',
        'phone',
        'email',
    ];

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }
}
