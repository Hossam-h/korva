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
        Schema::table('players', function (Blueprint $table) {
            // Player profile completion — custom health text
            $table->string('other_health_issue')->nullable()->after('health_issues');

            // Location confirmation (guardian flow map coordinates)
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            // Guardian linking — captured for future auto-link; no relationship/logic yet.
            $table->string('parent_contact')->nullable()->after('other_health_issue');

            // Social login (Google / Apple)
            $table->string('provider')->nullable()->after('parent_contact');
            $table->string('provider_id')->nullable()->after('provider');
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_id']);
            $table->dropColumn([
                'other_health_issue',
                'latitude',
                'longitude',
                'parent_contact',
                'provider',
                'provider_id',
            ]);
        });
    }
};
