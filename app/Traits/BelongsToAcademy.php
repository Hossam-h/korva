<?php

namespace App\Traits;

use App\Models\Academy;
use App\Models\AcademyScope;

trait BelongsToAcademy
{
    protected static function bootBelongsToAcademy(): void
    {
        static::addGlobalScope(new AcademyScope);

        static::creating(function ($model) {
            if (auth('academy')->check() && ! $model->academy_id) {
                $model->academy_id = auth('academy')->id();
            }
        });
    }

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }
}
