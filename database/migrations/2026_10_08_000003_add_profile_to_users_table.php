<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->string('avatar_path')->nullable()->after('city_id');
        });

        // Level and preferred position per sport (docs/DESIGN.md §6.6). Also the base for v3 stats.
        Schema::create('sport_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['beginner', 'intermediate', 'advanced']);
            $table->string('position', 60)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_user');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn('avatar_path');
        });
    }
};
