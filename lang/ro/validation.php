<?php

// Only the rules Sport.md uses. Anything missing falls back to the English file from Laravel.
return array (
  'after' => ':Attribute trebuie să fie după :date.',
  'array' => ':Attribute trebuie să fie o listă.',
  'attributes' => 
  array (
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
    'month' => 'luna',
    'city' => 'orașul',
    'mine' => 'filtrul „ale mele”',
    'latitude' => 'locația pe hartă',
    'longitude' => 'locația pe hartă',
  ),
  'before' => ':Attribute trebuie să fie înainte de :date.',
  'between' => 
  array (
    'numeric' => ':Attribute trebuie să fie între :min și :max.',
  ),
  'boolean' => ':Attribute trebuie să fie da sau nu.',
  'confirmed' => 'Confirmarea pentru :attribute nu se potrivește.',
  'custom' => 
  array (
    'match_date_time' => 
    array (
      'after' => 'Meciul trebuie să fie în viitor.',
      'required' => 'Alege ziua și ora meciului.',
      'before' => 'Poți programa un meci cel mult 6 luni înainte.',
    ),
    'sport_id' => 
    array (
      'required' => 'Alege sportul.',
    ),
    'city_id' => 
    array (
      'required' => 'Alege orașul.',
    ),
    'title' => 
    array (
      'required' => 'Scrie un titlu pentru meci.',
    ),
    'location_name' => 
    array (
      'required' => 'Scrie unde se joacă (teren, adresă).',
    ),
    'max_players' => 
    array (
      'required' => 'Scrie câți jucători sunt, cu tine.',
    ),
    'price_collector' => 
    array (
      'required_unless' => 'Alege cine încasează banii.',
    ),
    'latitude' => 
    array (
      'between' => 'Punctul de pe hartă nu e valid.',
      'required_with' => 'Punctul de pe hartă nu e valid.',
    ),
    'longitude' => 
    array (
      'between' => 'Punctul de pe hartă nu e valid.',
      'required_with' => 'Punctul de pe hartă nu e valid.',
    ),
  ),
  'date' => ':Attribute nu este o dată validă.',
  'date_format' => ':Attribute trebuie să aibă formatul :format.',
  'email' => 'Scrie o adresă de email validă, de exemplu ion@exemplu.md.',
  'exists' => ':Attribute ales nu există.',
  'in' => ':Attribute ales nu este valid.',
  'integer' => ':Attribute trebuie să fie un număr întreg.',
  'max' => 
  array (
    'array' => ':Attribute poate avea cel mult :max elemente.',
    'numeric' => ':Attribute poate fi cel mult :max.',
    'string' => ':Attribute poate avea cel mult :max caractere.',
  ),
  'min' => 
  array (
    'numeric' => ':Attribute trebuie să fie cel puțin :min.',
    'string' => ':Attribute trebuie să aibă cel puțin :min caractere.',
  ),
  'numeric' => ':Attribute trebuie să fie un număr.',
  'password' => 
  array (
    'min' => 'Parola trebuie să aibă cel puțin :min caractere.',
  ),
  'required' => 'Completează :attribute.',
  'required_unless' => 'Alege :attribute.',
  'required_with' => 'Completează :attribute.',
  'string' => ':Attribute trebuie să fie text.',
  'unique' => 'Există deja un cont cu acest :attribute.',
);
