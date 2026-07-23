<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
        DB::statement("ALTER TABLE players MODIFY type ENUM('parent', 'player') NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE players SET type = 'player' WHERE type IS NULL");
        DB::statement("ALTER TABLE players MODIFY type ENUM('parent', 'player') NOT NULL");
    }
};
