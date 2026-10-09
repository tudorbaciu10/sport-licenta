<?php

namespace Database\Seeders;

use App\Enums\RoomStatus;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use App\Services\RoomMembership;
use Illuminate\Database\Seeder;

/**
 * Chat demo: demo@sport.md joins two upcoming matches that already have players,
 * with a short conversation in each, and gets a reply in the support thread from
 * suport@sport.md (support team). Safe to run again: php artisan db:seed --class=ChatDemoSeeder
 */
class ChatDemoSeeder extends Seeder
{
    public function run(RoomMembership $membership): void
    {
        $demo = User::where('email', 'demo@sport.md')->first();
        if (! $demo) {
            return;
        }

        $support = User::firstOrCreate(['email' => 'suport@sport.md'], ['name' => 'Echipa Sport.md', 'password' => 'password']);
        $support->forceFill(['is_support' => true])->save();

        $demo->forceFill(['telegram' => 'demo_sportmd'])->save();

        $rooms = Room::upcoming()
            ->where('status', RoomStatus::Open)
            ->where('current_players_count', '>=', 2)
            ->where('user_id', '!=', $demo->id)
            ->orderBy('match_date_time')
            ->take(2)
            ->get();

        $lines = [
            ['Salut! Cine aduce mingea?', 'Eu o aduc, am una nouă.', 'Super. Ne vedem cu 10 minute înainte la intrare?'],
            ['Terenul e rezervat, plătim la fața locului.', 'Ok, vin direct de la serviciu, poate întârzii 5 minute.'],
        ];

        foreach ($rooms as $i => $room) {
            $membership->join($room, $demo);
            if ($room->messages()->exists()) {
                continue;
            }
            $others = $room->players()->where('users.id', '!=', $demo->id)->get();
            foreach ($lines[$i] as $n => $body) {
                Message::forceCreate([
                    'room_id' => $room->id,
                    'user_id' => $others[$n % $others->count()]->id,
                    'body' => $body,
                    'created_at' => now()->subMinutes(60 - $n * 7),
                ]);
            }
        }

        if (! $demo->supportMessages()->exists()) {
            Message::forceCreate(['support_user_id' => $demo->id, 'user_id' => $demo->id, 'body' => 'Bună! Cum schimb orașul din profil?', 'created_at' => now()->subHours(3)]);
            Message::forceCreate(['support_user_id' => $demo->id, 'user_id' => $support->id, 'body' => 'Bună! Din Profil → Editează profilul, câmpul „Orașul tău”.', 'created_at' => now()->subHours(2)]);
        }
    }
}
