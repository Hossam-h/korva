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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('type', ['parent', 'player']);

            $table->string('address')->nullable();

            $table->date('birth_date')->nullable();
            $table->decimal('weight', 8, 2)->nullable();

            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');

            $table->boolean('has_health_issues')->default(false);

            $table->string('health_issues')->nullable();


            $table->string('period')->nullable();
            $table->string('password');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
