<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Social login (Google/Apple) can't tell us whether the user is a parent
     * or a player — that's chosen afterwards via complete-profile, same as
     * the OTP registration flow. The column can't stay NOT NULL without a
     * default if we're no longer allowed to guess 'player'.
     */
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            // SQLite-compatible: change() works on both MySQL and SQLite in Laravel
            $table->string('type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('type')->nullable(false)->change();
        });
    }
};
