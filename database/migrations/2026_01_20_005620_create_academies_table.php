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
        Schema::create('academies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('age_group')->nullable();

            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            $table->string('business_owner_email')->nullable();
            $table->string('business_owner_phone')->nullable();

            $table->boolean('is_active')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->enum('attachments_status', ['pending', 'approved', 'rejected','problem'])->default('pending');

            $table->string('password')->nullable();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academies');
    }
};
