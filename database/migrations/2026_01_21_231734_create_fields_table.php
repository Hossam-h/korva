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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained('academies')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['indoor', 'outdoor']);
            
            $table->enum('status', ['available', 'unavailable', 'maintenance'])
                  ->default('available');

            $table->time('available_from');
            $table->time('available_to');
            $table->json('day_of_week')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
