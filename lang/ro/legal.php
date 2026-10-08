<?php

// Cookie policy and privacy policy pages (resources/views/legal/).
return [
    'updated' => 'Ultima actualizare: :date',
    'footer_rights' => '© :year Sport.md · Proiect de licență',

    'cookies' => [
        'title' => 'Politica cookie',
        'intro' => 'Cookie-urile sunt fișiere mici pe care site-ul le salvează în browserul tău. Sport.md folosește foarte puține și nu folosește cookie-uri de reclamă sau de urmărire.',
        'table_title' => 'Ce cookie-uri folosim',
        'col_name' => 'Nume',
        'col_category' => 'Categorie',
        'col_purpose' => 'La ce folosește',
        'col_duration' => 'Durată',
        'necessary' => 'Necesar',
        'preferences' => 'Preferințe',
        'rows' => [
            'session' => ['Ține sesiunea deschisă și te recunoaște după autentificare.', ':minutes minute de inactivitate'],
            'xsrf' => ['Protejează formularele împotriva cererilor false (CSRF).', 'Cât sesiunea'],
            'remember' => ['Apare doar dacă bifezi „Ține-mă minte” la autentificare.', 'Până la deconectare'],
            'consent' => ['Reține ce ai ales în fereastra despre cookie-uri.', ':days zile'],
            'locale' => ['Reține limba aleasă (RO sau RU). Doar cu acordul tău.', '1 an'],
        ],
        'manage_title' => 'Cum îți schimbi alegerea',
        'manage_text' => 'Apasă „Setări cookie” jos pe orice pagină. Poți șterge cookie-urile și din setările browserului; dacă ștergi cookie-urile necesare, vei fi deconectat.',
    ],

    'privacy' => [
        'title' => 'Politica de confidențialitate',
        'intro' => 'Aici explicăm ce date personale prelucrează Sport.md, de ce și ce drepturi ai.',
        'sections' => [
            ['Cine prelucrează datele', 'Sport.md este un proiect de licență, o platformă prin care găsești și organizezi meciuri de amatori în orașele din Moldova. Pentru întrebări despre date ne scrii la :email.'],
            ['Ce date colectăm', 'Când îți faci cont: numele, adresa de email și parola (salvată doar criptat, nu o putem citi). Dacă alegi: orașul, poza de profil, sporturile, nivelul și poziția preferată. Când folosești platforma: meciurile pe care le creezi, la care te înscrii sau care te interesează.'],
            ['De ce le folosim', 'Ca să-ți funcționeze contul, ca organizatorii și ceilalți jucători să vadă cine vine la meci și ca să-ți arătăm meciuri din orașul tău. Nu trimitem reclame și nu vindem date.'],
            ['Cine le vede', 'Numele și poza ta apar pe pagina meciurilor la care participi sau care te interesează, ca organizatorul și ceilalți jucători să știe cine vine. Emailul și parola nu sunt afișate nimănui.'],
            ['Cât timp le păstrăm', 'Cât timp ai cont. Dacă ceri ștergerea contului, ștergem datele de profil și înscrierile tale.'],
            ['Drepturile tale', 'Poți cere acces la datele tale, corectarea, ștergerea sau exportul lor și poți retrage oricând acordul pentru cookie-urile opționale. Datele de profil le poți modifica singur din „Profil”.'],
            ['Cookie-uri', 'Detaliile sunt în Politica cookie.'],
        ],
        'legal_note' => 'Prelucrarea respectă legislația Republicii Moldova privind protecția datelor cu caracter personal și principiile Regulamentului (UE) 2016/679 (GDPR).',
    ],
];
