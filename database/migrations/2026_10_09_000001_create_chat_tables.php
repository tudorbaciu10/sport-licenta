<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One table for both kinds of conversation: a match chat (room_id) or
        // a user's private thread with the support team (support_user_id). Exactly one is set.
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('support_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['room_id', 'id']);
            $table->index(['support_user_id', 'id']);
        });

        // Last message each user has seen per conversation (same room_id / support_user_id pair) → unread counts.
        Schema::create('chat_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('support_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('last_read_message_id')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'room_id']);
            $table->index(['user_id', 'support_user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_support')->default(false)->after('avatar_path');
            // Optional social handles, shown only to people who share a match with the user.
            $table->string('telegram', 40)->nullable()->after('is_support');
            $table->string('instagram', 40)->nullable()->after('telegram');
            $table->string('facebook', 60)->nullable()->after('instagram');
            $table->string('whatsapp', 20)->nullable()->after('facebook');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_support', 'telegram', 'instagram', 'facebook', 'whatsapp']);
        });
        Schema::dropIfExists('chat_reads');
        Schema::dropIfExists('messages');
    }
};
