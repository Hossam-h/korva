<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class SubscriptionPlan extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = [
        'title',                                                 
        'description',
        'short_description',
    ];

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }
}
