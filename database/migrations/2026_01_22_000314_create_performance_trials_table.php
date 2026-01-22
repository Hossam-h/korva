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
        Schema::create('performance_trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained('academies')->onDelete('cascade');
            $table->string('title');
            $table->string('age_category');
            $table->integer('max_players')->default(20);
            $table->date('start_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->json('day_of_week')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_trials');
    }
};
