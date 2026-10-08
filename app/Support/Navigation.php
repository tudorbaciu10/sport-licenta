<?php

namespace App\Support;

/**
 * Main destinations, shared by the top nav (laptop) and the bottom nav (phone).
 * "Meciurile mele" and "Profil" exist only for logged-in users; guests get a single
 * "Intră" entry instead (bottom nav), and login / register buttons in the header.
 */
class Navigation
{
    /** @return array<int, array{key: string, url: string, icon: string, active: bool}> */
    public static function items(): array
    {
        $items = [
            ['key' => 'home', 'url' => url('/'), 'icon' => 'house', 'active' => request()->is('/')],
            ['key' => 'search', 'url' => route('rooms.index'), 'icon' => 'search', 'active' => request()->routeIs('rooms.index', 'rooms.show')],
            ['key' => 'create', 'url' => route('rooms.create'), 'icon' => 'circle-plus', 'active' => request()->routeIs('rooms.create')],
        ];

        if (auth()->check()) {
            $items[] = ['key' => 'my_matches', 'url' => route('my-matches'), 'icon' => 'calendar-days', 'active' => request()->routeIs('my-matches')];
            $items[] = ['key' => 'profile', 'url' => route('profile'), 'icon' => 'user-round', 'active' => request()->routeIs('profile', 'profile.*')];
        } else {
            $items[] = ['key' => 'login', 'url' => route('login'), 'icon' => 'user-round', 'active' => request()->routeIs('login', 'register')];
        }

        return $items;
    }
}
