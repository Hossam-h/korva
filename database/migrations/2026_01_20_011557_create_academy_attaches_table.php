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
        Schema::create('academy_attaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained('academies');
            $table->string('attach_type');
            $table->string('attach_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academy_attaches');
    }
};
