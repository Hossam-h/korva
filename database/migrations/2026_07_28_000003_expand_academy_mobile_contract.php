<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academies', function (Blueprint $table) {
            $table->text('description')->nullable()->after('address');
            $table->unsignedTinyInteger('min_age')->nullable()->after('age_group');
            $table->unsignedTinyInteger('max_age')->nullable()->after('min_age');
            $table->json('accepted_genders')->nullable()->after('max_age');
            $table->string('currency', 3)->default('SAR')->after('longitude');
        });

        Schema::table('coaches', function (Blueprint $table) {
            $table->string('image')->nullable()->after('bio');
        });

        Schema::table('academy_reviews', function (Blueprint $table) {
            $table->json('images')->nullable()->after('comment');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('players')
                ->nullOnDelete();
        });

        Schema::create('academy_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->enum('day', [
                'monday', 'tuesday', 'wednesday', 'thursday',
                'friday', 'saturday', 'sunday',
            ]);
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();
            $table->unique(['academy_id', 'day']);
        });

        Schema::create('academy_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('icon')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coach_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['coach_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_reviews');
        Schema::dropIfExists('academy_services');
        Schema::dropIfExists('academy_operating_hours');

        Schema::table('players', fn (Blueprint $table) => $table->dropConstrainedForeignId('parent_id'));
        Schema::table('academy_reviews', fn (Blueprint $table) => $table->dropColumn('images'));
        Schema::table('coaches', fn (Blueprint $table) => $table->dropColumn('image'));
        Schema::table('academies', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'min_age', 'max_age', 'accepted_genders', 'currency',
            ]);
        });
    }
};
