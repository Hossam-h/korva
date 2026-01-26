<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyNotificationSetting extends Model
{
    protected $fillable = [
        'academy_id',
        'email_academic_news',
        'email_subscription_reminder',
        'email_training_tips',
        'app_new_member',
        'app_main_page_review',
        'app_cancel_subscription_alert',
        'app_upcoming_training',
        'app_training_change',
        'app_subscription_alert',
    ];

    protected $casts = [
        'email_academic_news' => 'boolean',
        'email_subscription_reminder' => 'boolean',
        'email_training_tips' => 'boolean',
        'app_new_member' => 'boolean',
        'app_main_page_review' => 'boolean',
        'app_cancel_subscription_alert' => 'boolean',
        'app_upcoming_training' => 'boolean',
        'app_training_change' => 'boolean',
        'app_subscription_alert' => 'boolean',
    ];

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }
}
