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
        Schema::table('groups', function (Blueprint $table) {
            // Single session price
            $table->decimal('session_price', 10, 2)->nullable()->after('days');
            // Monthly subscription price
            $table->decimal('monthly_price', 10, 2)->nullable()->after('session_price');
            // Maximum number of players allowed in the group
            $table->unsignedSmallInteger('capacity')->nullable()->after('monthly_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['session_price', 'monthly_price', 'capacity']);
        });
    }
};
