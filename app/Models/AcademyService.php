<?php

namespace App\Models;

use App\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AcademyService extends Model
{
    use BelongsToAcademy;

    protected $fillable = [
        'academy_id',
        'title',
        'description',
        'full_description',
        'icon',
        'images',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn (string $path) => Storage::url($path))
            ->values()
            ->all();
    }
}
