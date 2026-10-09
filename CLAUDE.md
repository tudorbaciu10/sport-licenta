# Sport.md

## Stack
Laravel 13, PHP 8.3, MySQL 8, Blade + componente Blade, Alpine.js, CSS simplu cu variabile.
Fără Tailwind. Fără pas de build pentru CSS.

Baza de date locală pentru acest branch: `sport_licenta_ilie` (în `.env`). Cont demo: `demo@sport.md` / `password`; cont suport tehnic: `suport@sport.md` / `password`.

Chat în timp real (Laravel Reverb): pornește și `php artisan reverb:start` lângă `php artisan serve`. Fără Reverb chat-ul merge în continuare, cu mesaje noi la 10 s. Date demo pentru chat: `php artisan db:seed --class=ChatDemoSeeder`.

## Design (OBLIGATORIU)
Citește docs/DESIGN.md înainte de orice modificare de interfață.
Folosește skill-ul `sportmd-design`. Dacă intră în conflict cu `frontend-design`, câștigă `sportmd-design` și docs/DESIGN.md.
- Stil: inspirat de Apple (alb, spațiu, text clar, accent colorat). Mobile-first.
- Folosește DOAR variabilele din public/assets/css/tokens.css. Nu inventa culori sau fonturi.
- Font: Inter. Pictograme: Lucide (SVG în componente Blade, `resources/views/components/icon/`).
- Layout: `layouts/app`. Componentele din `resources/views/components/` (vezi `/styleguide`) se folosesc înainte de a scrie markup nou.
- Culoarea sportului apare doar ca fundal/bandă/pictogramă, niciodată ca text mic.
- Ținte tactile minim 44px. Respectă prefers-reduced-motion.
- Toate textele vizibile în fișiere lang/ (RO și RU), nu hardcodate.

## Reguli
- Nu schimba logica din backend fără să întrebi.
- Rulează testele (Pest) după orice modificare de backend cu `composer test` (curăță întâi cache-ul de configurare). Nu rula testele cu config în cache: ar șterge baza MySQL reală (tests/TestCase.php oprește acum asta).
- Fă commit mic după fiecare pas finalizat.
- După fiecare ecran, verifică la 360px, 390px, 768px și 1280px lățime.
