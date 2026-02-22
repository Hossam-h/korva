<?php

namespace App\Models;

use App\Traits\HasFileAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AcademyAttach extends Model
{
    use HasFileAttachment;

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
        return $this->attach_path ? Storage::url($this->attach_path) : null;
    }

    protected function getFileFields(): array
    {
        return ['attach_path'];
    }

    protected function getDefaultFolder(): string
    {
        return 'academy_attaches';
    }
}
