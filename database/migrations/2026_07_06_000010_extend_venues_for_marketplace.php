<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('venue_category_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('slug')->nullable()->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->string('surface')->nullable()->after('description');       // synthetic / grass / hard / parquet ...
            $table->boolean('is_indoor')->nullable()->after('surface');
            $table->decimal('price_per_hour', 8, 2)->nullable()->after('is_indoor');
            $table->string('currency', 8)->default('MDL')->after('price_per_hour');
            $table->string('contact_phone')->nullable()->after('currency');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->string('country')->default('Moldova')->after('city');
            $table->string('locality')->nullable()->after('country');
            $table->string('photo_path')->nullable()->after('capacity');
            $table->string('source')->default('user')->after('photo_path');    // user / osm / google
            $table->string('external_id')->nullable()->after('source');
            $table->boolean('is_published')->default(true)->after('external_id');

            $table->index('city');
            $table->index('is_published');
            $table->index(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['venue_category_id']);
            $table->dropIndex(['city']);
            $table->dropIndex(['is_published']);
            $table->dropIndex(['source', 'external_id']);
            $table->dropColumn([
                'user_id', 'venue_category_id', 'slug', 'description', 'surface',
                'is_indoor', 'price_per_hour', 'currency', 'contact_phone', 'contact_email',
                'country', 'locality', 'photo_path', 'source', 'external_id', 'is_published',
            ]);
        });
    }
};
