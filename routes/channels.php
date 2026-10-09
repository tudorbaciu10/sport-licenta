<?php

use App\Models\Room;
use App\Models\User;
use App\Support\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Match chat: joined players only (the organiser is one), same rule as the page.
Broadcast::channel('chat.room.{room}', fn (User $user, Room $room) => Chat::canChat($user, $room));

// The support team's list of threads. Declared before chat.support.{owner} so "inbox" isn't taken as a user id.
Broadcast::channel('chat.support.inbox', fn (User $user) => $user->is_support);

// A user's private thread with the support team.
Broadcast::channel('chat.support.{owner}', fn (User $user, User $owner) => Chat::canSupport($user, $owner));
