<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academy_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')
                  ->constrained('academies')
                  ->cascadeOnDelete();
            $table->boolean('email_academic_news')->default(true);
            $table->boolean('email_subscription_reminder')->default(true);
            $table->boolean('email_training_tips')->default(true);
            $table->boolean('app_new_member')->default(true);
            $table->boolean('app_main_page_review')->default(false);
            $table->boolean('app_cancel_subscription_alert')->default(false);
            $table->boolean('app_upcoming_training')->default(true);
            $table->boolean('app_training_change')->default(true);
            $table->boolean('app_subscription_alert')->default(true);
            $table->timestamps();
            $table->unique('academy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academy_notification_settings');
    }
};
