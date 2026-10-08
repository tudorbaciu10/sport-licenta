<?php

use App\Enums\RoomStatus;
use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

uses(RefreshDatabase::class);

function demoRoom(int $taken, int $max, RoomStatus $status = RoomStatus::Open): Room
{
    $room = Room::factory()->create([
        'user_id' => User::factory(),
        'sport_id' => Sport::firstOrCreate(['slug' => 'fotbal'], ['name' => 'Fotbal']),
        'city_id' => City::factory(),
        'max_players' => $max,
        'match_date_time' => now()->addDay(),
    ]);
    $room->forceFill(['current_players_count' => $taken, 'status' => $status])->save();

    return $room->load('sport', 'city');
}

it('classifies availability as open, almost or full', function () {
    expect(demoRoom(5, 10)->availability())->toBe('open')
        ->and(demoRoom(8, 10)->availability())->toBe('almost')   // 2 left = 20%
        ->and(demoRoom(3, 4)->availability())->toBe('almost')    // at least 1 left counts
        ->and(demoRoom(10, 10)->availability())->toBe('full')
        ->and(demoRoom(4, 10, RoomStatus::Full)->availability())->toBe('full');
});

it('uses correct Romanian and Russian plurals', function () {
    $ro = fn ($n) => trans_choice('components.match.spots_left', $n, ['count' => $n], 'ro');
    $ru = fn ($n) => trans_choice('components.match.spots_left', $n, ['count' => $n], 'ru');

    expect($ro(1))->toBe('1 loc liber')
        ->and($ro(3))->toBe('3 locuri libere')
        ->and($ro(20))->toBe('20 de locuri libere')
        ->and($ru(1))->toBe('1 свободное место')
        ->and($ru(3))->toBe('3 свободных места')
        ->and($ru(7))->toBe('7 свободных мест');
});

it('renders every component', function () {
    $room = demoRoom(8, 10);
    $sport = $room->sport;

    $html = Blade::render(<<<'BLADE'
        <x-button icon="search">Caută</x-button>
        <x-button variant="secondary" href="/rooms">Link</x-button>
        <x-chip href="#" active>Toate</x-chip>
        <x-chip :sport="$sport">Fotbal</x-chip>
        <x-sport-icon :sport="$sport" />
        <x-sport-card :sport="$sport" :count="3" />
        <x-match-card :room="$room" />
        <x-badge status="full" />
        <x-avatar name="Ion Popescu" />
        <x-field name="email" type="email" label="Email" />
        <x-bottom-nav />
        <x-empty-state title="Gol"><x-button>Creează</x-button></x-empty-state>
        <x-skeleton variant="match-card" :count="2" />
        <x-step-form :steps="['A', 'B']" action="/x" submit="Trimite">
            <x-step-form.step :n="1">unu</x-step-form.step>
            <x-step-form.step :n="2">doi</x-step-form.step>
        </x-step-form>
    BLADE, ['sport' => $sport, 'room' => $room]);

    expect($html)
        ->toContain('class="btn btn--primary"')
        ->toContain('href="/rooms"')
        ->toContain('aria-current="true"')
        ->toContain('class="sport-icon sport-icon--md"')
        ->toContain('3 meciuri deschise')
        ->toContain('Aproape plin')
        ->toContain('8 din 10')
        ->toContain('2 locuri libere')
        ->toContain('aria-label="Ion Popescu">IP</span>')
        ->toContain('bottom-nav')
        ->toContain('Pasul 2 din 2')
        ->toContain('data-step="2"')
        ->toContain('name="_token"');
});

it('wires field errors to the input for screen readers', function () {
    $errors = (new ViewErrorBag)->put('default', new MessageBag(['email' => ['Scrie o adresă validă.']]));

    session()->put('errors', $errors);

    $html = Blade::render('<x-field name="email" type="email" label="Email" hint="Ex: ion@exemplu.md" />');

    expect($html)
        ->toContain('aria-invalid="true"')
        ->toContain('aria-describedby="f-email-hint f-email-error"')
        ->toContain('Scrie o adresă validă.')
        ->toContain('field--error');
});
