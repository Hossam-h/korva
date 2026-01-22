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
        Schema::create('coach_tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('coaches')->onDelete('cascade');
            $table->string('tournament_name');
            $table->string('achievement');
            $table->date('tournament_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_tournaments');
    }
};
