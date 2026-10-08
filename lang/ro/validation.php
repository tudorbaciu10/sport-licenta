<?php

// Only the rules Sport.md uses. Anything missing falls back to Laravel's English file.
return [
    'after' => ':Attribute trebuie să fie după :date.',
    'array' => ':Attribute trebuie să fie o listă.',
    'boolean' => ':Attribute trebuie să fie da sau nu.',
    'confirmed' => 'Confirmarea pentru :attribute nu se potrivește.',
    'date' => ':Attribute nu este o dată validă.',
    'date_format' => ':Attribute trebuie să aibă formatul :format.',
    'email' => 'Scrie o adresă de email validă, de exemplu ion@exemplu.md.',
    'exists' => ':Attribute ales nu există.',
    'in' => ':Attribute ales nu este valid.',
    'integer' => ':Attribute trebuie să fie un număr întreg.',
    'max' => [
        'array' => ':Attribute poate avea cel mult :max elemente.',
        'numeric' => ':Attribute poate fi cel mult :max.',
        'string' => ':Attribute poate avea cel mult :max caractere.',
    ],
    'min' => [
        'numeric' => ':Attribute trebuie să fie cel puțin :min.',
        'string' => ':Attribute trebuie să aibă cel puțin :min caractere.',
    ],
    'password' => [
        'min' => 'Parola trebuie să aibă cel puțin :min caractere.',
    ],
    'required' => 'Completează :attribute.',
    'required_unless' => 'Alege :attribute.',
    'string' => ':Attribute trebuie să fie text.',
    'unique' => 'Există deja un cont cu acest :attribute.',

    'custom' => [
        'match_date_time' => ['after' => 'Meciul trebuie să fie în viitor.'],
    ],

    'attributes' => [
        'sport_id' => 'sportul',
        'city_id' => 'orașul',
        'title' => 'titlul',
        'description' => 'nota',
        'location_name' => 'locația',
        'venue_type' => 'tipul terenului',
        'match_date_time' => 'data și ora',
        'max_players' => 'numărul de jucători',
        'price' => 'prețul',
        'price_collector' => 'cine încasează',
        'equipment_by' => 'echipamentul',
        'rules' => 'regulile',
        'rules.*' => 'regula',
        'name' => 'numele',
        'email' => 'emailul',
        'password' => 'parola',
        'date' => 'ziua',
        'time' => 'ora',
        'q' => 'căutarea',
    ],
];
