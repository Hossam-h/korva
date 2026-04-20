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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Core relations
            $table->foreignId('academy_id')->constrained('academies')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');

            // The account holder who made the booking (player or parent)
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');

            // Booking type: one-time session or monthly subscription
            $table->enum('booking_type', ['single', 'monthly']);

            // --- Single booking fields ---
            $table->date('session_date')->nullable();           // chosen date
            $table->time('session_start_time')->nullable();     // chosen slot start
            $table->time('session_end_time')->nullable();       // chosen slot end

            // --- Monthly booking fields ---
            $table->tinyInteger('duration_months')->nullable(); // 1, 2, or 3 months

            // Booking lifecycle status
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])
                  ->default('pending');

            // Pricing
            $table->decimal('subtotal', 10, 2)->default(0);       // price before discount
            $table->decimal('discount_amount', 10, 2)->default(0); // coupon discount
            $table->decimal('total_amount', 10, 2)->default(0);    // final payable amount

            // Coupon (snapshot kept even if coupon is later deleted)
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code')->nullable();

            // Payment
            $table->enum('payment_method_type', ['card', 'apple_pay', 'stc_pay', 'other'])->nullable();
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('payment_reference')->nullable(); // gateway transaction id

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
