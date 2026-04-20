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
        Schema::create('booking_players', function (Blueprint $table) {
            $table->id();

            // The booking this player is associated with
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');

            // The actual participant (child / the player themselves)
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');

            // Prevent the same player from appearing twice in one booking
            $table->unique(['booking_id', 'player_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_players');
    }
};
