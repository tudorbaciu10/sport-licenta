<?php

// Match detail page, create form, price and equipment labels, flash messages.
return array (
  'price' => 
  array (
    'free' => 'Gratuit',
    'amount' => ':price lei',
    'per_player' => ':price lei de persoană',
    'collector' => 
    array (
      'organizer' => 'Plătești organizatorului',
      'venue' => 'Plătești la teren',
    ),
  ),
  'equipment' => 
  array (
    'organizer' => 'Organizatorul aduce mingea și vestele',
    'players' => 'Fiecare își aduce echipamentul',
    'venue' => 'Echipamentul e la teren',
  ),
  'show' => 
  array (
    'back' => 'Toate meciurile',
    'organized_by' => 'Organizează :name',
    'when' => 'Când',
    'where' => 'Unde',
    'venue' => 'Teren',
    'price' => 'Preț',
    'spots' => 'Locuri',
    'equipment' => 'Echipament',
    'note' => 'Nota organizatorului',
    'rules' => 'Reguli',
    'players' => 'Jucători',
    'interested' => 'Interesați',
    'organizer' => 'organizator',
    'you' => 'tu',
    'no_players' => 'Încă nu s-a înscris nimeni.',
    'join' => 'Ocupă un loc',
    'join_guest' => 'Intră în cont ca să ocupi un loc',
    'interest' => 'Mă interesează',
    'uninterest' => 'Nu mă mai interesează',
    'leave' => 'Ies din meci',
    'full' => 'Meci complet',
    'not_joinable' => 'Înscrierile sunt închise',
    'you_play' => 'Ești în echipă',
    'you_organize' => 'Tu organizezi acest meci',
    'share' => 'Distribuie',
    'copied' => 'Link copiat',
    'share_text' => ':title, :when. Hai la joc!',
    'venue_any' => 'Nespecificat',
  ),
  'create' => 
  array (
    'title' => 'Creează un meci',
    'intro' => 'Tu ești primul jucător. Ceilalți se alătură până se umplu locurile.',
    'steps' => 
    array (
      0 => 'Sport și oraș',
      1 => 'Când și unde',
      2 => 'Jucători și preț',
      3 => 'Reguli și notă',
    ),
    'sport' => 'Sport',
    'city' => 'Oraș',
    'choose' => 'Alege',
    'match_title' => 'Titlu',
    'match_title_ph' => 'De exemplu: Minifotbal de joi seara',
    'when' => 'Data și ora',
    'where' => 'Locația',
    'where_ph' => 'Teren sintetic Botanica',
    'venue' => 'Tipul terenului',
    'venue_any' => 'Nu contează',
    'players' => 'Câți jucători, cu tine',
    'price' => 'Preț de persoană (lei)',
    'price_hint' => '0 înseamnă gratuit.',
    'collector' => 'Cine încasează banii',
    'equipment' => 'Cine aduce mingea și vestele',
    'equipment_none' => 'Nu e cazul',
    'note' => 'Notă pentru jucători',
    'note_ph' => 'De exemplu: vino cu 10 minute mai devreme, avem vestiar.',
    'rules' => 'Reguli',
    'rule_ph' => 'De exemplu: Fără tackling',
    'add_rule' => 'Adaugă o regulă',
    'remove_rule' => 'Șterge regula',
    'preview' => 'Așa va arăta meciul în listă',
    'submit' => 'Publică meciul',
  ),
  'flash' => 
  array (
    'created' => 'Meciul a fost creat. Ești primul jucător din listă.',
    'joined' => 'Ți-ai ocupat locul. Ne vedem pe teren!',
    'interested' => 'Te-am trecut la interesați.',
    'left' => 'Ai ieșit din meci.',
    'full' => 'Nu mai sunt locuri libere în acest meci.',
    'inactive' => 'Meciul nu mai este activ.',
    'organizer_leave' => 'Organizatorul nu poate părăsi propriul meci.',
  ),
);
