<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained()->restrictOnDelete();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location_name');
            $table->dateTime('match_date_time');
            $table->unsignedSmallInteger('max_players');
            $table->unsignedSmallInteger('current_players_count')->default(0);
            $table->json('rules')->nullable();
            $table->enum('status', ['open', 'full', 'finished', 'cancelled'])->default('open');
            $table->timestamps();

            $table->index(['city_id', 'sport_id', 'status', 'match_date_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
