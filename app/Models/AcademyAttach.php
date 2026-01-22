<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class AcademyAttach extends Model
{
    //

    protected $fillable = [
        'academy_id',
        'attach_type',
        'attach_path',
    ];

    protected $appends = [
        'full_attach_path',
    ];

    public function academy()
    {
        return $this->belongsTo(Academy::class);
    }

    public function getFullAttachPathAttribute()
    {
        return Storage::url($this->attach_path);
    }
}
