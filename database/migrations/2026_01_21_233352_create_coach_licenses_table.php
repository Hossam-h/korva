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
        Schema::create('coach_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('coaches')->onDelete('cascade');
            $table->string('license_name');
            $table->string('issuing_authority');
            $table->date('obtained_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_licenses');
    }
};
