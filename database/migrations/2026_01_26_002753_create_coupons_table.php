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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique(); 
            $table->enum('discount_type', ['percentage', 'fixed']); 
            $table->string('title');
            $table->decimal('discount_value', 8, 2); 
            $table->text('description')->nullable(); 
            $table->enum('subscription_type', ['monthly', 'quarterly', 'yearly'])->nullable(); 
            $table->integer('usage_limit')->nullable(); 
            $table->integer('usage_count')->default(0); 
            $table->date('valid_from')->nullable(); 
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
