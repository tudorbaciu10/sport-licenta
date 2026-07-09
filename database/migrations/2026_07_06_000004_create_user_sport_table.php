<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skill_level')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sport');
    }
};
