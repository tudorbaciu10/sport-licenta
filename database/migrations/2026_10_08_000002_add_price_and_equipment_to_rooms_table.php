<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Price per player in MDL (lei). 0 = free.
            $table->unsignedSmallInteger('price')->default(0)->after('max_players')->index();
            // Who collects the money: the organiser or the venue (null when free).
            $table->enum('price_collector', ['organizer', 'venue'])->nullable()->after('price');
            // Who brings the ball / bibs.
            $table->enum('equipment_by', ['organizer', 'players', 'venue'])->nullable()->after('price_collector');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['price']);
            $table->dropColumn(['price', 'price_collector', 'equipment_by']);
        });
    }
};
