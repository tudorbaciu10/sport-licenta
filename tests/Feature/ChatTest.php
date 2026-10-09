<?php

use App\Events\MessageSent;
use App\Models\City;
use App\Models\Message;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use App\Services\RoomMembership;
use App\Support\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

/** A match organised by $organizer with $players joined. */
function chatRoom(User $organizer, array $players = [], array $attrs = []): Room
{
    $membership = app(RoomMembership::class);
    $room = $membership->create($organizer, array_merge([
        'sport_id' => Sport::firstOrCreate(['slug' => 'fotbal'], ['name' => 'Fotbal', 'color' => '#34C759'])->id,
        'city_id' => City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău'])->id,
        'title' => 'Fotbal la Botanica',
        'location_name' => 'Botanica',
        'match_date_time' => now()->addDay()->setTime(19, 0),
        'max_players' => 10,
    ], $attrs));

    foreach ($players as $player) {
        $membership->join($room, $player);
    }

    return $room->fresh();
}

it('opens a match chat only once another player has joined', function () {
    $organizer = User::factory()->create();
    $room = chatRoom($organizer);

    // Alone in the match: no chat, no "Chat" in the nav
    $this->actingAs($organizer)->get('/my-matches')->assertDontSee('class="header-chat"', false);
    $this->get(route('chat.room', $room))->assertForbidden();

    app(RoomMembership::class)->join($room, User::factory()->create());

    $this->actingAs($organizer->fresh())->get('/my-matches')->assertSee('class="header-chat"', false);
    $this->get(route('chat.room', $room))->assertOk()->assertSee('Fotbal la Botanica');
});

it('lets only joined players read and write', function () {
    $organizer = User::factory()->create();
    $player = User::factory()->create();
    $room = chatRoom($organizer, [$player]);

    $stranger = User::factory()->create();
    $interested = User::factory()->create();
    app(RoomMembership::class)->markInterested($room, $interested);

    foreach ([$stranger, $interested] as $outsider) {
        $this->actingAs($outsider)->get(route('chat.room', $room))->assertForbidden();
        $this->getJson(route('chat.messages', ['rooms', $room->id]))->assertForbidden();
        $this->postJson(route('chat.send', ['rooms', $room->id]), ['body' => 'Salut'])->assertForbidden();
    }
    expect(Message::count())->toBe(0);

    $this->actingAs($player)->postJson(route('chat.send', ['rooms', $room->id]), ['body' => 'Salut'])->assertCreated();
    $this->actingAs($organizer)->getJson(route('chat.messages', ['rooms', $room->id]))
        ->assertOk()->assertJsonPath('messages.0.body', 'Salut')->assertJsonPath('messages.0.author', $player->name);
});

it('keeps guests out', function () {
    $room = chatRoom(User::factory()->create(), [User::factory()->create()]);

    $this->get(route('chat.room', $room))->assertRedirect(route('login'));
    $this->postJson(route('chat.send', ['rooms', $room->id]), ['body' => 'x'])->assertUnauthorized();
});

it('saves, validates and broadcasts a message', function () {
    Event::fake([MessageSent::class]);
    $organizer = User::factory()->create();
    $room = chatRoom($organizer, [User::factory()->create()]);
    $url = route('chat.send', ['rooms', $room->id]);

    $this->actingAs($organizer)->postJson($url, ['body' => '   '])->assertUnprocessable();
    $this->postJson($url, ['body' => str_repeat('a', Message::MAX_LENGTH + 1)])->assertUnprocessable();

    $this->postJson($url, ['body' => "  Vin cu mingea <b>!</b>  "])
        ->assertCreated()
        ->assertJsonPath('message.body', 'Vin cu mingea <b>!</b>');   // stored as text, escaped when drawn

    expect(Message::first())->room_id->toBe($room->id)->user_id->toBe($organizer->id);
    Event::assertDispatched(MessageSent::class, fn ($e) => $e->broadcastOn()[0]->name === 'private-chat.room.'.$room->id);
});

it('polls only newer messages and pages back through older ones', function () {
    $organizer = User::factory()->create();
    $room = chatRoom($organizer, [User::factory()->create()]);
    $ids = collect(range(1, 3))->map(fn ($i) => Message::create(['room_id' => $room->id, 'user_id' => $organizer->id, 'body' => "m$i"])->id);

    $this->actingAs($organizer)->getJson(route('chat.messages', ['rooms', $room->id, 'after' => $ids[0]]))
        ->assertJsonCount(2, 'messages')->assertJsonPath('messages.0.body', 'm2');
    $this->getJson(route('chat.messages', ['rooms', $room->id, 'before' => $ids[2]]))
        ->assertJsonCount(2, 'messages')->assertJsonPath('messages.1.body', 'm2');
});

it('counts unread messages until the conversation is opened', function () {
    $organizer = User::factory()->create();
    $player = User::factory()->create();
    $room = chatRoom($organizer, [$player]);

    $this->actingAs($player)->postJson(route('chat.send', ['rooms', $room->id]), ['body' => 'unu']);
    $this->postJson(route('chat.send', ['rooms', $room->id]), ['body' => 'doi']);

    expect(Chat::unreadTotal($organizer->fresh()))->toBe(2)
        ->and(Chat::unreadTotal($player->fresh()))->toBe(0);   // your own messages are read

    $this->actingAs($organizer)->get(route('chat.room', $room))->assertOk();
    expect(Chat::unreadTotal($organizer->fresh()))->toBe(0);
});

it('closes the chat of cancelled or long-finished matches', function () {
    $organizer = User::factory()->create();
    $room = chatRoom($organizer, [User::factory()->create()]);

    $room->forceFill(['match_date_time' => now()->subDays(Chat::KEEP_DAYS + 1)])->save();
    $this->actingAs($organizer)->get(route('chat.room', $room))->assertForbidden();

    $room->forceFill(['match_date_time' => now()->subDay(), 'status' => 'cancelled'])->save();
    $this->get(route('chat.room', $room))->assertForbidden();
});

it('gives every user a private support thread that only they and support see', function () {
    Event::fake([MessageSent::class]);
    $user = User::factory()->create();
    $other = User::factory()->create();
    $support = User::factory()->create(['is_support' => true]);

    $this->actingAs($user)->get(route('chat.support'))->assertOk()->assertSee('Suport tehnic');
    $this->postJson(route('chat.send', ['support', $user->id]), ['body' => 'Nu pot intra'])->assertCreated();

    // Another user can't read it
    $this->actingAs($other)->get(route('chat.support', $user))->assertForbidden();
    $this->getJson(route('chat.messages', ['support', $user->id]))->assertForbidden();

    // Support sees it in the list, with the unread count, and answers
    expect(Chat::unreadTotal($support))->toBe(1);
    $this->actingAs($support)->get(route('chat.index'))->assertOk()->assertSee($user->name)->assertSee('Nu pot intra');
    $this->postJson(route('chat.send', ['support', $user->id]), ['body' => 'Încercați din nou'])->assertCreated();

    expect(Chat::unreadTotal($user->fresh()))->toBe(1);
    Event::assertDispatched(MessageSent::class, fn ($e) => collect($e->broadcastOn())->pluck('name')
        ->all() === ['private-chat.support.'.$user->id, 'private-chat.support.inbox']);
});

it('authorises the private broadcast channels', function () {
    config(['broadcasting.default' => 'reverb', 'broadcasting.connections.reverb.key' => 'k', 'broadcasting.connections.reverb.secret' => 's', 'broadcasting.connections.reverb.app_id' => '1']);
    app('Illuminate\Broadcasting\BroadcastManager')->forgetDrivers();
    require base_path('routes/channels.php');

    $organizer = User::factory()->create();
    $room = chatRoom($organizer, [User::factory()->create()]);
    $stranger = User::factory()->create();
    $support = User::factory()->create(['is_support' => true]);
    $auth = fn (User $u, string $channel) => $this->actingAs($u)->post('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => 'private-'.$channel]);

    $auth($organizer, 'chat.room.'.$room->id)->assertOk();
    $auth($stranger, 'chat.room.'.$room->id)->assertForbidden();
    $auth($stranger, 'chat.support.'.$stranger->id)->assertOk();
    $auth($stranger, 'chat.support.'.$organizer->id)->assertForbidden();
    $auth($stranger, 'chat.support.inbox')->assertForbidden();
    $auth($support, 'chat.support.inbox')->assertOk();
    $auth($support, 'chat.support.'.$stranger->id)->assertOk();
});

it('shows the chat in Russian', function () {
    $organizer = User::factory()->create();
    $room = chatRoom($organizer, [User::factory()->create()]);

    $this->actingAs($organizer)->withSession(['locale' => 'ru'])->get(route('chat.room', $room))
        ->assertOk()
        ->assertSee('Техподдержка')
        ->assertDontSee('>chat.', false);
});
