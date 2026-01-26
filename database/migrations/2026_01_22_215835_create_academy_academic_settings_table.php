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
        Schema::create('academy_academic_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained('academies')->onDelete('cascade');
            $table->json('work_days');
            $table->time('morning_start')->default('09:00');
            $table->time('morning_end')->default('18:00');
            $table->boolean('has_evening')->default(false);
            $table->time('evening_start')->nullable();
            $table->time('evening_end')->nullable();
            $table->json('age_ranges');
            $table->timestamps();
            $table->unique('academy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academy_academic_settings');
    }
};
