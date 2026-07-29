<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace the global email unique constraint on coaches with a per-academy
     * composite unique (academy_id, email).
     *
     * This allows the same email to be used across different academies while
     * still preventing duplicates within the same academy.
     */
    public function up(): void
    {
        Schema::table('coaches', function (Blueprint $table) {
            // Drop the old global unique index
            $table->dropUnique('coaches_email_unique');

            // Add composite unique: same email allowed across academies, not within one
            $table->unique(['academy_id', 'email'], 'coaches_academy_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('coaches', function (Blueprint $table) {
            $table->dropUnique('coaches_academy_email_unique');
            $table->unique('email', 'coaches_email_unique');
        });
    }
};
