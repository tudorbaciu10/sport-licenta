<?php

namespace App\Support;

/** Main destinations, shared by the top nav (laptop) and the bottom nav (phone). */
class Navigation
{
    /** @return array<int, array{key: string, url: string, icon: string, active: bool}> */
    public static function items(): array
    {
        return [
            ['key' => 'home', 'url' => url('/'), 'icon' => 'house', 'active' => request()->is('/')],
            ['key' => 'search', 'url' => route('rooms.index'), 'icon' => 'search', 'active' => request()->routeIs('rooms.index', 'rooms.show')],
            ['key' => 'create', 'url' => route('rooms.create'), 'icon' => 'circle-plus', 'active' => request()->routeIs('rooms.create')],
            ['key' => 'my_matches', 'url' => route('my-matches'), 'icon' => 'calendar-days', 'active' => request()->routeIs('my-matches')],
            // Guests land on login (and come back to their profile after signing in).
            ['key' => 'profile', 'url' => route('profile'), 'icon' => 'user-round', 'active' => request()->routeIs('profile', 'profile.*', 'login', 'register')],
        ];
    }
}
