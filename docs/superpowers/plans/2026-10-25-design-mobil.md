# Design mobil după Figma — plan de implementare

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Sub 1024px, ecranele principale Sport.md urmează structura wireframe-urilor Figma (Acasă, Caută + Hartă, pagina meciului, Meciurile mele, Creează → Data și ora), iar de la 1024px în sus nimic nu se schimbă.

**Architecture:** Fiecare pagină păstrează markup-ul de desktop. Ce e nou pentru telefon e fie CSS sub `@media (max-width: 1023px)`, fie elemente noi ascunse la ≥1024px (și invers: elementele care pe telefon sunt înlocuite se ascund sub 1024px). Componente noi mici (`x-sheet`, `x-action-bar`, `x-match-row`, `x-week-strip`) sunt refolosite între pagini. Singura schimbare de backend e un endpoint JSON doar de citire pentru hartă.

**Tech Stack:** Laravel 13 / PHP 8.3, Blade + componente, Alpine.js 3.14 (CDN), CSS simplu cu tokens, Leaflet 1.9.4 + Leaflet.markercluster 1.5.3 (cdnjs), Pest.

**Spec:** `docs/superpowers/specs/2026-10-25-design-mobil-design.md` — citește-o întreagă înainte de Task 1. Secțiunile §6.x sunt sursa pentru fiecare ecran. Machetele aprobate: `.superpowers/brainstorm/1759-1792874358/content/` (local, ignorate de git).

## Global Constraints

- „Sub 1024px” = `@media (max-width: 1023px)`; „desktop” = `@media (min-width: 1024px)`. La ≥1024px ieșirea vizuală trebuie să fie identică cu baseline-ul din Task 1.
- Doar variabile din `public/assets/css/tokens.css`. Fără culori, fonturi sau mărimi noi. Culoarea sportului doar ca fundal, bandă, pictogramă sau tint (`color-mix(in srgb, var(--c) var(--tint), transparent)`), niciodată ca text mic.
- Ținte tactile minim `var(--tap)` (44px). Butonul de filtre are 48px.
- Fiecare sport apare cu pictogramă **și** nume, nu doar prin culoare.
- Toate textele vizibile noi intră în `lang/ro/*.php` **și** `lang/ru/*.php`. Nimic hardcodat.
- Pictograme Lucide ca SVG în `resources/views/components/icon/` (formatul fișierelor existente, de exemplu `share-2.blade.php`).
- `prefers-reduced-motion: reduce` → foile apar și dispar fără tranziție.
- Fără fotografii (DESIGN.md D5).
- Backend: singura schimbare permisă e `GET /rooms/map` (Task 5). Restul se face în views, componente, CSS și JS.
- Testele: `composer test` (face `config:clear` înainte). Nu rula niciodată `php artisan test` cu config-ul în cache.
- Commit mic la finalul fiecărui task, cu mesaj în română în stilul istoricului (`git log`). **Fără** linii `Co-Authored-By` / „Generated with Claude Code”.
- Fiecare task care atinge UI se verifică vizual la **360, 390, 768, 1023, 1024 și 1280px** (1023/1024 = granița). La 1280 și 1024 se compară cu baseline-ul.
- Verificarea vizuală: `php artisan serve` (și opțional `php artisan reverb:start`), cu browserul disponibil în sesiune (built-in browser sau Claude in Chrome; încarcă întâi skill-ul lui). Cont demo `demo@sport.md` / `password`. Date: `php artisan db:seed` și `php artisan db:seed --class=ChatDemoSeeder` dacă baza e goală.

## Review Focus

1. **Textele rusești sunt mai lungi** (de exemplu „Присоединиться”, „Мне интересно”) → la 360px nimic nu iese din bara de jos, din rânduri sau din tab bar. Textul se trunchiază cu `…` sau se rupe pe două rânduri, niciodată scroll orizontal. Verificare RU la 360px în Task 2, 7, 8 și 9.
2. **Granița 1023/1024px** → la 1023 e designul de telefon complet (fără antet, cu tab bar), la 1024 desktopul identic cu baseline-ul. Nicio pagină cu ambele variante vizibile sau cu niciuna. Verificare la ambele lățimi în fiecare task cu UI.
3. **Harta fără rezultate sau cu cerere eșuată** (zero meciuri, offline, 429) → mesaj clar peste hartă („Niciun meci cu locuri libere aici” / „Nu am putut încărca harta”, cu „Încearcă din nou”), nu o hartă goală. Test JSON pentru lista goală în Task 5; stări UI în Task 6.
4. **Locația refuzată sau indisponibilă** (permisiune refuzată, browser fără Permissions API, pagină pe HTTP fără TLS) → nicio eroare în consolă, distanța lipsește, iar butonul „Lângă mine” arată mesajul scurt. Verificare în Task 2 (`distance.js`) și Task 6.
5. **Titluri și locații lungi** (60+ caractere, de exemplu „Turneu amical de minifotbal pentru începători la Botanica”) → rândul compact și cardul mare trunchiază la 2 rânduri, iar badge-ul și chevron-ul rămân la locul lor. Test cu titlu lung în `ComponentsTest` (Task 2) și verificare vizuală.

---

### Task 1: Baseline desktop, DESIGN.md și cadrul de telefon (antet, tab bar, limba)

**Files:**
- Create: `resources/views/components/locale-switch.blade.php`
- Modify: `resources/views/layouts/app.blade.php`, `resources/views/components/bottom-nav.blade.php`, `public/assets/css/base.css`, `resources/views/profile/show.blade.php`, `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, `docs/DESIGN.md`, `lang/ro/profile.php`, `lang/ru/profile.php`
- Test: `tests/Feature/NavigationTest.php`, `tests/Feature/ProfileTest.php`

**Interfaces:**
- Produces:
  - `<x-locale-switch />`: grupul RO/RU de acum, mutat din layout. Același markup și aceleași clase (`locale-switch`), deci desktopul nu se schimbă.
  - Secțiunea Blade `@section('no_tabbar', true)`: o pagină o setează ca să ascundă tab bar-ul sub 1024px. Layout-ul pune atunci clasa `no-tabbar` pe `<body>`. Folosită în Task 7 și 9.
  - Clasa CSS `.m-only` (vizibil doar <1024px) și `.d-only` (vizibil doar ≥1024px) în `base.css`, folosite de toate task-urile următoare.
  - Directorul baseline: `.superpowers/baseline/` cu capturi `{pagina}-1280.png` și `{pagina}-1024.png`.

- [ ] **Step 1: Capturi baseline înainte de orice schimbare.** Cu serverul pornit și logat ca demo, capturează la 1280×900 și 1024×900 paginile: `/`, `/rooms`, `/rooms/{un id}`, `/my-matches`, `/rooms/create`, `/profile`, `/chat`, `/login` (delogat), `/styleguide`. Salvează-le în `.superpowers/baseline/`. Acestea sunt referința pentru toate comparațiile de desktop.

- [ ] **Step 2: Scrie testele care pică.**

```php
// NavigationTest.php
it('keeps the header markup (hidden on phones by CSS) and marks unread chat on the "Ale mele" tab', function () {
    // set up a user with ≥1 unread match message (reuse the pattern from ChatTest::chatRoom)
    $html = $this->actingAs($player)->get('/')->assertOk()->getContent();
    expect($html)->toContain('class="header-chat"')
        ->toContain('bottom-nav__dot');   // dot on the my_matches tab when unread > 0
});

it('has no unread dot when everything is read', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertDontSee('bottom-nav__dot', false);
});

// ProfileTest.php
it('lets the user switch language from the profile', function () {
    $this->actingAs(User::factory()->create())->get('/profile')
        ->assertSee(route('locale', 'ro'), false)
        ->assertSee(route('locale', 'ru'), false);
});

it('offers the language switch on login and register for guests', function () {
    $this->get('/login')->assertSee('class="locale-switch', false);
    $this->get('/register')->assertSee('class="locale-switch', false);
});
```

- [ ] **Step 3: Rulează și confirmă că pică.** `composer test -- --filter="unread chat|no unread dot|switch language|language switch on login"`. Expected: FAIL (`bottom-nav__dot` și `locale-switch` lipsesc din profil și login).

- [ ] **Step 4: Implementează.**
  - Extrage `<x-locale-switch />` din layout și folosește-l în antet, în `profile/show` (un rând nou „Limba” în `list-group`-ul de jos, cu RO/RU ca segmentat) și sus pe login și register (doar `.m-only`).
  - În profil adaugă și rândul „Chat” (`message-circle`), dacă `Navigation::chat()` nu e null.
  - În `bottom-nav` adaugă `<span class="bottom-nav__dot" aria-hidden="true">` pe tab-ul `my_matches` când `Navigation::chat()['unread'] > 0`. Textul pentru cititorul de ecran folosește `chat.nav_unread`.
  - În `base.css`, sub 1023px: `.app-header { display:none }`, `body.no-tabbar .bottom-nav { display:none }` și stilul tab bar-ului ca în Figma (pictograma 24px deasupra textului `--fs-caption`, activul în `--text` și `--fw-semibold`, „Creează” păstrează pastila `--brand`). Plus `.m-only` / `.d-only`. Elimină padding-ul de jos al `app-main` când `no-tabbar`.
  - Actualizează `docs/DESIGN.md` conform spec §5 (ultimul punct): §6.1, §6.3, §6.8, stilul tab bar-ului și o secțiune nouă „Telefon < 1024px, după Figma”, cu trimitere la spec.

- [ ] **Step 5: Rulează testele.** `composer test`. Expected: toate PASS, inclusiv `ChatTest` și `NavigationTest` existente.

- [ ] **Step 6: Verificare vizuală.** Pe `/` și `/profile`: la 360/390/768/1023 nu există antet, tab bar-ul are stilul nou și punctul de necitite. La 1024/1280 capturile coincid cu baseline-ul.

- [ ] **Step 7: Commit.** `git commit -m "Telefon: fără antet sub 1024px, tab bar ca în Figma, limba în profil și la login; DESIGN.md actualizat"`

---

### Task 2: Componente comune — `x-sheet`, `x-action-bar`, `x-match-row`, distanța

**Files:**
- Create: `resources/views/components/sheet.blade.php`, `resources/views/components/action-bar.blade.php`, `resources/views/components/match-row.blade.php`, `public/assets/js/distance.js`, `resources/views/components/icon/sliders-horizontal.blade.php`, `resources/views/components/icon/map.blade.php`, `resources/views/components/icon/list.blade.php`, `resources/views/components/icon/locate.blade.php`
- Modify: `public/assets/css/components.css`, `resources/views/styleguide.blade.php`, `resources/views/layouts/app.blade.php` (include `distance.js` cu `defer`), `lang/{ro,ru}/components.php`, `lang/{ro,ru}/styleguide.php`
- Test: `tests/Feature/ComponentsTest.php`

**Interfaces:**
- Produces:
  - `<x-sheet name="filters" :title="..." inline-desktop>…</x-sheet>`:
    - Sub 1024px: un dialog (`role="dialog"`, `aria-modal="true"`, `aria-labelledby`) cu fundal semi-opac, titlu și buton de închidere. Se deschide cu evenimentul window `open-sheet` (`detail: 'filters'`) și se închide cu Esc, tap pe fundal sau `close-sheet`. Focusul e prins înăuntru și revine la butonul care l-a deschis.
    - Cu `inline-desktop`, la ≥1024px învelișul are `display: contents`, iar antetul, fundalul și butonul de închidere sunt ascunse: conținutul se afișează exact ca înainte.
    - Fără tranziție sub `prefers-reduced-motion`.
  - `<x-action-bar>` cu slot-urile `summary` (opțional) și `actions`: bara lipită jos sub 1024px (`position: sticky; bottom: 0`, sau `fixed` cu `env(safe-area-inset-bottom)`), ascunsă la ≥1024px.
    - Cu un singur buton: `summary` la stânga, butonul la dreapta.
    - Cu două butoane, sau când nu există `summary`: butoanele împart lățimea 50/50.
  - `<x-match-row :room="$room" :role="null|'organizer'|'joined'|'interested'" :unread="0" />`. Așteaptă `sport` și `city` încărcate. Markup-ul e descris în spec §6.3 (lista) și §6.5 (rol, necitite). Distanța e un `<span data-distance data-lat=".." data-lng=".." hidden>` (doar dacă `hasPin()`).
  - `distance.js`: la `DOMContentLoaded`, dacă `navigator.permissions` raportează `geolocation: granted`, apelează `getCurrentPosition` o dată și completează toate `[data-distance]` cu „2,1 km” (`Intl.NumberFormat` în limba paginii, o zecimală sub 10 km), apoi le face vizibile. Expune `window.sportDistance.request()`, care cere permisiunea și completează la fel (o va folosi butonul „Lângă mine” din Task 6). Returnează o promisiune care se rezolvă cu `{lat, lng}` sau se respinge la refuz. În caz de eroare nu aruncă nimic în consolă.

- [ ] **Step 1: Scrie testele care pică.**

```php
it('renders a compact match row with sport icon and name, role and unread count', function () {
    $room = demoRoom(3, 10);
    $html = Blade::render('<x-match-row :room="$room" role="organizer" :unread="2" />', ['room' => $room]);
    expect($html)->toContain('match-row')
        ->toContain($room->sport->label())        // name, not colour only
        ->toContain('match-row__role--organizer')
        ->toContain('>2<')                        // unread count
        ->toContain(route('rooms.show', $room));
});

it('omits the distance hook for a match without a pin and adds it for one with a pin', function () {
    $room = demoRoom(1, 10);
    expect(Blade::render('<x-match-row :room="$room" />', ['room' => $room]))->not->toContain('data-distance');
    $room->forceFill(['latitude' => 47.0105, 'longitude' => 28.8638])->save();
    expect(Blade::render('<x-match-row :room="$room" />', ['room' => $room]))->toContain('data-lat="47.0105000"');
});

it('keeps a very long title inside the row', function () {
    $room = demoRoom(1, 10);
    $room->title = str_repeat('Turneu amical de minifotbal ', 4);
    expect(Blade::render('<x-match-row :room="$room" />', ['room' => $room]))->toContain('match-row__title');   // class carries the 2-line clamp
});

it('renders a sheet as an accessible dialog', function () {
    $html = Blade::render('<x-sheet name="t" title="Filtre">x</x-sheet>');
    expect($html)->toContain('role="dialog"')->toContain('aria-modal="true"')->toContain('aria-labelledby=');
});
```

- [ ] **Step 2: Rulează și confirmă că pică.** `composer test -- --filter="compact match row|distance hook|long title|accessible dialog"`. Expected: FAIL (componentele nu există).

- [ ] **Step 3: Implementează componentele, CSS-ul, pictogramele, `distance.js` și secțiunile din `/styleguide`** (câte o secțiune `sg-section` pe componentă, după `x-match-card`, cu exemplele din `$samples` existent). Titlul din `match-row` folosește `-webkit-line-clamp: 2`.

- [ ] **Step 4: Rulează testele.** `composer test`. Expected: PASS.

- [ ] **Step 5: Verificare vizuală.**
  - Pe `/styleguide` la 360px, în RO și RU: rândul compact cu titlu lung, bara de acțiune cu unul și cu două butoane, foaia deschisă (Esc o închide, focusul revine).
  - Cu locația refuzată în browser: nicio eroare în consolă.
  - La 1024/1280: `/styleguide` diferă de baseline doar prin secțiunile noi.

- [ ] **Step 6: Commit.** `git commit -m "Componente: foaie de jos, bară de acțiune, rând compact de meci și distanța în browser"`

---

### Task 3: Acasă pe telefon (spec §6.2)

**Files:**
- Modify: `resources/views/home.blade.php`, `resources/views/components/match-card.blade.php`, `public/assets/css/components.css`, `lang/{ro,ru}/home.php`
- Test: `tests/Feature/HomeTest.php`

**Interfaces:**
- Consumes: `.m-only` / `.d-only` (Task 1), `<x-sheet inline-desktop>` și evenimentul `open-sheet` (Task 2), `x-calendar`, `x-dropdown`, `x-chip` (existente).
- Produces: `x-match-card` primește prop-ul `hero` (bool). Doar sub 1024px, cardul devine varianta cu bandă și oră mare. La ≥1024px aspectul e cel de acum.

- [ ] **Step 1: Scrie testele care pică.**

```php
it('greets the player by first name and a guest with a plain hello', function () {
    $user = User::factory()->create(['name' => 'Andrei Popescu']);
    $this->actingAs($user)->get('/')->assertSee('Salut, Andrei');
    auth()->logout();
    $this->get('/')->assertSee('Salut!');
});

it('shows sports as chips linking to the list with the chosen city, plus a calendar banner', function () {
    // one open football match in Chișinău (reuse this file's setup)
    $this->get('/')->assertOk()
        ->assertSee(route('rooms.index', ['city' => 'chisinau', 'sport' => 'fotbal']), false)
        ->assertSee("\$dispatch('open-sheet', 'home-calendar')", false);
});

it('renders the greeting and banner in Russian', function () {
    $this->withSession(['locale' => 'ru'])->get('/')->assertOk()->assertSee(__('home.greeting_guest', [], 'ru'));
});
```

- [ ] **Step 2: Rulează și confirmă că pică.** `composer test -- --filter=HomeTest`. Expected: FAIL pe testele noi.

- [ ] **Step 3: Implementează.** Structura e cea din spec §6.2.
  - Blocul salut + oraș + chat/„Intră” (`.m-only`).
  - Butonul „Caută” de lângă căutare ascuns sub 1024px.
  - Rândul de chips de sport (`.m-only`); șina de plăci actuală devine `.d-only`.
  - Bannerul „Calendar” (`.m-only`).
  - `<x-calendar>` învelit în `<x-sheet name="home-calendar" inline-desktop>`, deci pe desktop rămâne la locul lui.
  - Cardurile cu `hero`.
  - Chei noi în `home.php`: `greeting` („Salut, :name”), `greeting_guest`, `sports_title`, `sports_all`, `banner_title`, `banner_text`, `banner_action`. Prenumele e primul cuvânt din `name`.

- [ ] **Step 4: Rulează testele.** `composer test`. Expected: PASS, inclusiv testele `HomeTest` existente.

- [ ] **Step 5: Verificare vizuală** la toate lățimile, ca utilizator logat și ca vizitator. Foaia cu calendarul se deschide și se închide. La 1024/1280 pagina e identică cu baseline-ul.

- [ ] **Step 6: Commit.** `git commit -m "Acasă pe telefon: salut și oraș, sporturi ca chips, calendarul într-o foaie, carduri mari"`

---

### Task 4: Caută pe telefon — antet, filtre în foaie, rânduri compacte (spec §6.3, lista)

**Files:**
- Modify: `resources/views/rooms/index.blade.php`, `lang/{ro,ru}/rooms.php`
- Test: `tests/Feature/RoomsListTest.php`

**Interfaces:**
- Consumes: `x-sheet inline-desktop`, `x-match-row`, `.m-only` / `.d-only`, pictograma `sliders-horizontal`.
- Produces:
  - Parametrul de query `view` (`list`, implicit, sau `map`), citit în view cu `request('view') === 'map'`. Toate link-urile de filtre și de paginare îl păstrează.
  - Comutatorul segmentat Listă/Hartă cu link-uri `?view=list|map`.
  - Containerul `<div id="rooms-map" class="m-only" …>`, desenat doar când `view=map`. Îl completează Task 6.

- [ ] **Step 1: Scrie testele care pică.**

```php
it('shows the number of active filters on the phone filter button', function () {
    listRoom();
    $this->get('/rooms?venue=indoor&date='.today()->toDateString())
        ->assertSee('rooms-filter-btn', false)
        ->assertSeeInOrder(['rooms-filter-btn', '>2<'], false);
});

it('lists matches as compact rows for phones and keeps the desktop cards', function () {
    $room = listRoom(['title' => 'Meci compact']);
    $html = $this->get('/rooms')->getContent();
    expect($html)->toContain('match-row')->toContain('match-card');
});

it('keeps the list/map choice in filter links', function () {
    listRoom();
    $this->get('/rooms?view=map')
        ->assertSee('view=map&amp;venue=indoor', false)    // or the equivalent order produced by $url()
        ->assertSee('id="rooms-map"', false);
});
```

- [ ] **Step 2: Rulează și confirmă că pică.** `composer test -- --filter=RoomsListTest`. Expected: FAIL pe testele noi.

- [ ] **Step 3: Implementează.**
  - Antetul de telefon: titlu, număr de meciuri, căutare + `rooms-filter-btn` de 48px cu insigna numărului de filtre active. Se numără `date`, `time`, `venue`, `free`, `when`, `price` și `city`, ignorând `sport` și `q`.
  - Chips-urile de filtre, „Mai multe filtre” și alegerea orașului intră în `<x-sheet name="rooms-filters" inline-desktop>`. Pe desktop rămân exact unde sunt. În foaie, chips-urile se pun pe mai multe rânduri, iar `x-dropdown` de oraș stă în foaie.
  - Rândul cu chips de sport rămâne în afara foii.
  - Comutatorul Listă/Hartă (`.m-only`).
  - În listă, fiecare zi desenează `x-match-row` (`.m-only`) **și** `x-match-card` (`.d-only`).
  - Helper-ul `$url()` primește și `view`, cu `list` eliminat ca valoare implicită.
  - Chei noi în `rooms.php`: `filters_button`, `filters_title`, `view_list`, `view_map`.

- [ ] **Step 4: Rulează testele.** `composer test`. Expected: PASS.

- [ ] **Step 5: Verificare vizuală** la toate lățimile, cu și fără filtre active, inclusiv în RU la 360px. La 1024/1280, identic cu baseline-ul.

- [ ] **Step 6: Commit.** `git commit -m "Caută pe telefon: filtrele într-o foaie, rânduri compacte, comutator Listă/Hartă"`

---

### Task 5: Endpointul hărții `GET /rooms/map` (backend aprobat, spec §6.3)

**Files:**
- Create: `app/Http/Controllers/RoomMapController.php`, `app/Http/Requests/RoomMapRequest.php`
- Modify: `routes/web.php` (în grupul public, cu `throttle:60,1`, înainte de `/rooms/{room}`)
- Test: `tests/Feature/RoomMapTest.php`

**Interfaces:**
- Produces: `GET /rooms/map` → `route('rooms.map')`, invokable `RoomMapController::__invoke(RoomMapRequest $request): JsonResponse`.
  - `RoomMapRequest::rules()`: aceleași reguli ca `RoomController::index` pentru `sport`, `date`, `time`, `venue`, `free`, `q`, `when`, `price`. **Fără** `city` (un `city` trimis e ignorat, nu respins).
  - Răspunsul: `{"rooms": [ { "id": int, "url": string, "title": string, "sport": {"name","slug","var","icon"}, "starts_at": ISO-8601, "day": "dum., 26 oct.", "time": "10:00", "location": string, "city": string, "price": int, "spots_left": int, "availability": "open"|"almost", "lat": float, "lng": float } ], "truncated": bool }`.
  - `day` și `time` vin formatate în limba paginii, ca în `x-match-card` (`isoFormat('ddd, D MMM')` / `format('H:i')`).
  - Interogarea: `Room::upcoming()->filter($filtre)->where('status', RoomStatus::Open)->whereColumn('current_players_count', '<', 'max_players')->whereNotNull('latitude')->whereNotNull('longitude')`, cu `sport` și `city` încărcate, ordonată după `match_date_time`, `limit(500)`. `truncated` e true dacă existau mai multe.

- [ ] **Step 1: Scrie testele care pică** (helper propriu `mapRoom(array $attrs)`, cu `Room::factory()`, `latitude` 47.0105 și `longitude` 28.8638 implicite).

```php
it('returns only upcoming open matches with free spots and a pin, from every city', function () {
    $ok = mapRoom(['title' => 'Bun']);
    $other = mapRoom(['title' => 'Bălți', 'city_id' => City::factory()->create(['slug' => 'balti'])->id]);
    mapRoom(['title' => 'Plin'])->forceFill(['current_players_count' => 10, 'max_players' => 10])->save();
    mapRoom(['title' => 'Anulat'])->forceFill(['status' => RoomStatus::Cancelled])->save();
    mapRoom(['title' => 'Trecut', 'match_date_time' => now()->subHour()]);
    mapRoom(['title' => 'Fără pin', 'latitude' => null, 'longitude' => null]);

    $titles = collect($this->getJson('/rooms/map?city=chisinau')->assertOk()->json('rooms'))->pluck('title');

    expect($titles->all())->toEqualCanonicalizing(['Bun', 'Bălți']);   // city ignored
});

it('applies the list filters', function () {
    mapRoom(['title' => 'Interior', 'venue_type' => 'indoor']);
    mapRoom(['title' => 'Exterior', 'venue_type' => 'outdoor']);
    expect($this->getJson('/rooms/map?venue=indoor')->json('rooms.*.title'))->toBe(['Interior']);
});

it('sends only what the pin and card need', function () {
    mapRoom();
    $room = $this->getJson('/rooms/map')->json('rooms.0');
    expect(array_keys($room))->toEqualCanonicalizing(['id','url','title','sport','starts_at','day','time','location','city','price','spots_left','availability','lat','lng'])
        ->and(array_keys($room['sport']))->toEqualCanonicalizing(['name','slug','var','icon']);
});

it('returns an empty list, not an error, when nothing matches', function () {
    $this->getJson('/rooms/map?q=nimic')->assertOk()->assertExactJson(['rooms' => [], 'truncated' => false]);
});

it('rejects invalid filters and is rate limited', function () {
    $this->getJson('/rooms/map?venue=spatiu')->assertStatus(422);
    foreach (range(1, 60) as $i) { $this->getJson('/rooms/map'); }
    $this->getJson('/rooms/map')->assertStatus(429);
});

it('formats day and time in the page language', function () {
    mapRoom(['match_date_time' => now()->next(Carbon::SUNDAY)->setTime(10, 0)]);
    expect($this->withSession(['locale' => 'ru'])->getJson('/rooms/map')->json('rooms.0.time'))->toBe('10:00');
});
```

- [ ] **Step 2: Rulează și confirmă că pică.** `composer test -- --filter=RoomMapTest`. Expected: FAIL (404).

- [ ] **Step 3: Implementează** `RoomMapRequest`, `RoomMapController` și ruta `rooms.map`.

- [ ] **Step 4: Rulează testele.** `composer test`. Expected: toate PASS.

- [ ] **Step 5: Commit.** `git commit -m "Hartă: endpoint JSON cu meciurile deschise care mai caută jucători"`

---

### Task 6: Harta pe telefon (spec §6.3, Hartă)

**Files:**
- Create: `public/assets/js/rooms-map.js`, `public/assets/css/map.css`
- Modify: `resources/views/rooms/index.blade.php`, `lang/{ro,ru}/rooms.php`
- Test: `tests/Feature/RoomsListTest.php`

**Interfaces:**
- Consumes: `route('rooms.map')` (Task 5), containerul `#rooms-map` și `view=map` (Task 4), `window.sportDistance.request()` (Task 2), `City::center()`.
- Produces: componenta Alpine `roomsMap(config)`, unde `config` are:
  - `url`: `route('rooms.map')` cu filtrele curente, fără `city`;
  - `center`: `[lat, lng]` al orașului ales sau al Chișinăului;
  - `icons`: `{slug: '<svg…>'}`, randat pe server din `x-dynamic-component` pentru fiecare sport;
  - `t`: textele: gol, eroare, reîncearcă, lângă mine, locație refuzată, rezultate trunchiate;
  - `locale`.

- [ ] **Step 1: Scrie testul care pică.**

```php
it('configures the map with the filtered data url, without the city, only in map view', function () {
    listRoom();
    $html = $this->get('/rooms?view=map&city=chisinau&sport=fotbal')->getContent();
    expect($html)->toContain('roomsMap(')
        ->toContain(e(route('rooms.map', ['sport' => 'fotbal'])))
        ->not->toContain(e(route('rooms.map', ['city' => 'chisinau', 'sport' => 'fotbal'])));
    expect($this->get('/rooms')->getContent())->not->toContain('roomsMap(');
});
```

- [ ] **Step 2: Rulează și confirmă că pică.** `composer test -- --filter="configures the map"`. Expected: FAIL.

- [ ] **Step 3: Implementează `rooms-map.js` și `map.css`.**
  - **Încărcare:** Leaflet 1.9.4 și Leaflet.markercluster 1.5.3 (`leaflet.markercluster.js`, `MarkerCluster.css`, `MarkerCluster.Default.css`) se încarcă de pe cdnjs doar la `init` (după modelul din `create-match.js`). Tile-urile vin de la OpenStreetMap, cu atribuire.
  - **Pinii:** `L.divIcon` cu pictograma sportului pe `var(--c)` și ora. Pentru `availability === 'almost'`, contur `--warning`. Gruparea se face cu `markerClusterGroup`.
  - **Cardul:** tap pe pin → card jos, cu același markup și aceleași clase ca `x-match-row` (prin `<template>` în Blade, ca textele să rămână în `lang/`). Tap pe card → `url`.
  - **„Lângă mine”:** butonul cheamă `sportDistance.request()`, apoi `map.setView(…, 13)` și pune punctul utilizatorului. La refuz arată mesajul.
  - **Stări:** încărcare (`x-skeleton`), gol, eroare cu „Încearcă din nou”, `truncated` → notă discretă.
  - **Înălțimea** hărții: viewport minus antetul paginii și tab bar-ul.

- [ ] **Step 4: Rulează testele.** `composer test`. Expected: PASS.

- [ ] **Step 5: Verificare vizuală** la 360/390/768/1023:
  - zoom de la Chișinău la nivel de țară, cu grupuri care se desfac;
  - tap pe pin → card → pagina meciului; butonul înapoi revine în modul hartă;
  - filtru fără rezultate → mesajul „gol”;
  - rețea oprită (DevTools offline) → eroare și reîncercare;
  - locație refuzată → mesajul scurt, fără erori în consolă.

  La 1024/1280 `?view=map` arată lista de desktop (harta e `.m-only`).

- [ ] **Step 6: Commit.** `git commit -m "Harta meciurilor pe telefon: pini pe sport, grupare la nivel de țară, Lângă mine"`

---

### Task 7: Pagina meciului pe telefon (spec §6.4)

**Files:**
- Modify: `resources/views/rooms/show.blade.php`, `public/assets/css/components.css` (sau stilurile paginii din `@push('styles')`, după cum e acum), `lang/{ro,ru}/match.php`
- Test: `tests/Feature/MatchDetailAndCreateTest.php`

**Interfaces:**
- Consumes: `x-action-bar` (Task 2), `@section('no_tabbar', true)` (Task 1), `Chat::canChat`, `$myStatus`, `$room->isJoinable()`, rutele existente `rooms.join`, `rooms.interest`, `rooms.leave`, `chat.room`.
- Produces: nimic folosit de alte task-uri.

- [ ] **Step 1: Scrie testele care pică.** Un singur test parametrizat, care verifică în `x-action-bar` (selector: conținutul dintre `class="action-bar-m` și închiderea lui) butoanele din tabelul spec §6.4.

```php
it('shows the right phone action bar for each viewer', function (string $who, array $see, array $dontSee) {
    // build: guest | member | interested | full (member, match full) | player-with-chat (2 joined) | player-alone | organizer
    $bar = phoneActionBar($this->get(...)->getContent());   // helper extracting the x-action-bar markup
    foreach ($see as $text) expect($bar)->toContain($text);
    foreach ($dontSee as $text) expect($bar)->not->toContain($text);
})->with([
    'guest'            => ['guest',            ['Intră'],                          ['Renunț']],
    'member'           => ['member',           ['Mă alătur', 'Mă interesează'],    ['Chat']],
    'interested'       => ['interested',       ['Mă alătur', 'Nu mă mai interesează'], []],
    'full'             => ['full',             ['Complet', 'disabled'],            []],
    'player with chat' => ['player-chat',      ['Chat-ul echipei', 'Renunț'],      ['Invită']],
    'player alone'     => ['player-alone',     ['Invită jucători', 'Renunț'],      ['Chat-ul echipei']],
    'organizer'        => ['organizer',        ['Invită jucători'],                ['Renunț']],
]);   // use the exact strings from lang/ro/match.php; add keys where a label is new

it('hides the tab bar on the match page and keeps the named player list', function () { /* body has no-tabbar; people list still rendered */ });

it('puts the price in the bar for one action and among the chips for two', function () { /* guest: price inside action-bar-m; member: price chip present, not in bar */ });
```

- [ ] **Step 2: Rulează și confirmă că pică.** `composer test -- --filter=MatchDetailAndCreateTest`. Expected: FAIL pe testele noi, cele vechi PASS.

- [ ] **Step 3: Implementează.**
  - **Sus (`.m-only`):** banda-hero, cu butoanele înapoi și distribuie. Distribuie folosește `navigator.share`, cu fallback `navigator.clipboard.writeText` și mesajul scurt.
  - **Conținutul (`.m-only`):** chips-urile informative, rândurile de detalii și rezumatul cu avatare suprapuse și cercul „+”.
  - **Bara de jos:** `x-action-bar` cu stările din spec §6.4. Formularele trimit la aceleași rute ca acum.
  - **Ce rămâne `.d-only`:** antetul actual, `info-grid`, `aside.action-bar`. Restul secțiunilor (hartă, notă, reguli, jucători, interesați) rămân comune.
  - **`@section('no_tabbar', true)`.**

- [ ] **Step 4: Rulează testele.** `composer test`. Expected: PASS (inclusiv `ChatTest` „links the match page to its chat…”).

- [ ] **Step 5: Verificare vizuală.** Toate cele 7 stări, la 360px în RO și RU. Bara nu acoperă ultimul conținut (padding de jos egal cu înălțimea barei). La 1024/1280, identic cu baseline-ul.

- [ ] **Step 6: Commit.** `git commit -m "Pagina meciului pe telefon: bandă sus, chips, avatare, bară de acțiune pe stări"`

---

### Task 8: Banda săptămânii și Meciurile mele pe telefon (spec §6.5)

**Files:**
- Create: `resources/views/components/week-strip.blade.php`, `public/assets/js/week-strip.js`
- Modify: `resources/views/my-matches.blade.php`, `public/assets/js/calendar.js` (ascultă `calendar-open-day`), `resources/views/styleguide.blade.php`, `lang/{ro,ru}/profile.php`, `lang/{ro,ru}/components.php`
- Test: `tests/Feature/ProfileTest.php` (sau fișierul care testează deja `my-matches`; caută `my-matches` în `tests/`), `tests/Feature/ComponentsTest.php`

**Interfaces:**
- Consumes: `/calendar/days` (`month=YYYY-MM`, `city`, `mine=1`), `x-sheet inline-desktop`, `x-match-row` cu `role` și `unread`, `Chat::rooms($user)`, `Chat::unreadByRoom($user, $ids)`.
- Produces:
  - `<x-week-strip mode="dots|count" :city="$city?->slug" :mine="true|false" :selected="'YYYY-MM-DD'|null" />` → Alpine `weekStrip(config)`, cu `config` = `today`, `locale`, `daysUrl`, `city`, `mine`, `mode`, `selected`, `sportVars` (construit ca în `x-calendar`).
    - Afișează 7 zile pe pagină, începând cu luni. Zilele din trecut sunt dezactivate. Navighează câte o săptămână înainte și înapoi, fără să coboare sub săptămâna curentă și fără să treacă de 6 luni.
    - Încarcă luna sau lunile atinse de săptămână (una sau două cereri) și le ține în cache.
    - La tap pe o zi: `$dispatch('week-day', { date })`.
    - Ascultă `date-picked.window` (`detail: { date }`) ca să-și mute selecția.
  - `calendar.js`: `sportCalendar` ascultă `calendar-open-day.window` (`detail: { date }`), sare la luna zilei, o încarcă și apelează `select(date)`.

- [ ] **Step 1: Scrie testele care pică.**

```php
it('renders the week strip with its data url and mode', function () {
    $html = Blade::render('<x-week-strip mode="dots" mine />');
    expect($html)->toContain('weekStrip(')->toContain(e(route('calendar.days')))->toContain('"mode":"dots"');
});

it('lists my matches as rows with my role and unread counts only for open chats', function () {
    // organizer + 1 joined player in room A with 2 unread messages for $me; $me only "interested" in room B that has messages
    $html = $this->actingAs($me)->get('/my-matches')->getContent();
    expect($html)->toContain('match-row__role--joined')
        ->toContain('match-row__role--interested')
        ->toContain('match-row__unread">2<')
        ->and(substr_count($html, 'match-row__unread'))->toBe(1);
});

it('shows the upcoming count only on the open tab and the organise banner', function () {
    $this->actingAs($me)->get('/my-matches')->assertSee('(1)')->assertSee(route('rooms.create'), false);
    $this->actingAs($me)->get('/my-matches?tab=past')->assertDontSee('(1)');
});
```

- [ ] **Step 2: Rulează și confirmă că pică.** Expected: FAIL.

- [ ] **Step 3: Implementează.**
  - `x-week-strip` + `week-strip.js`, cu stilul zilelor ca banda „Date Strip” din Figma: 7 coloane, celula de 44px sau mai lată, ziua aleasă cu fond `--text`.
  - Listener-ul din `calendar.js`.
  - Pe `my-matches`:
    - antetul de telefon cu butonul 📅 → `open-sheet` `my-calendar`;
    - `x-calendar personal mine` învelit în `<x-sheet name="my-calendar" inline-desktop>`;
    - `x-week-strip mode="dots" mine`, cu `week-day`, care deschide foaia și trimite `calendar-open-day`;
    - numărul doar pe tab-ul activ (`$rooms->total()`);
    - rândurile `x-match-row` (`.m-only`) lângă cardurile actuale (`.d-only`). `unread` se calculează cu `Chat::unreadByRoom($user, $rooms->pluck('id')->intersect(Chat::rooms($user)->pluck('id')))`;
    - bannerul punctat (`.m-only`).
  - Secțiunea `x-week-strip` în `/styleguide`.

- [ ] **Step 4: Rulează testele.** `composer test`. Expected: PASS.

- [ ] **Step 5: Verificare vizuală.**
  - Banda în săptămâna curentă și în una care trece peste sfârșitul lunii: punctele corecte în ambele luni.
  - Tap pe o zi → foaia se deschide pe acea zi.
  - RU la 360px.
  - La 1024/1280, identic cu baseline-ul (calendarul la locul lui, fără bandă).

- [ ] **Step 6: Commit.** `git commit -m "Meciurile mele pe telefon: banda săptămânii, rânduri cu rol și necitite, calendarul într-o foaie"`

---

### Task 9: Creează un meci pe telefon — bara de sus, banda săptămânii, bara de jos (spec §6.6)

**Files:**
- Create: `resources/views/rooms/partials/when-calendar.blade.php`, `resources/views/rooms/partials/when-slots.blade.php`
- Modify: `resources/views/rooms/partials/when-picker.blade.php` (devine `@include` al celor două), `resources/views/rooms/create.blade.php`, `resources/views/components/step-form.blade.php` (slot opțional `navSummary`), `public/assets/js/create-match.js` (`pickDate` trimite `date-picked`), `public/assets/css/create.css`, `lang/{ro,ru}/match.php`
- Test: `tests/Feature/MatchDetailAndCreateTest.php`

**Interfaces:**
- Consumes: `x-week-strip mode="count"` cu `:city` legat de orașul ales (Task 8), evenimentele `week-day` / `date-picked`, `x-sheet` (fără `inline-desktop`: pe desktop coloana din dreapta rămâne), `@section('no_tabbar', true)`.
- Produces:
  - Partialele `when-calendar` (calendarul lunii) și `when-slots` (Dimineața/Ziua/Seara + grila + „altă oră”). Ambele primesc `$id`, ca acum.
  - `when-picker` = `when-calendar` + `when-slots`. Coloana de desktop folosește în continuare `when-picker`, deci rămâne identică.
  - `x-step-form` acceptă `<x-slot:navSummary>`, randat la stânga în `.step-form__nav` doar sub 1024px.

- [ ] **Step 1: Scrie testele care pică.**

```php
it('keeps the desktop planner whole and gives phones a week strip, the slots and a summary bar', function () {
    $html = $this->actingAs(User::factory()->create())->get('/rooms/create')->assertOk()->getContent();
    expect($html)->toContain('weekStrip(')
        ->toContain('"mode":"count"')
        ->toContain('step-form__summary')
        ->toContain('class="mcal"')        // month calendar still present (desktop column + phone sheet)
        ->toContain('slots__grid')
        ->toContain('no-tabbar');
});

it('still validates and stores a match created through the phone step 2', function () {
    // POST the same payload as the existing "stores price, collector and equipment" test → redirect to rooms.show
});
```

- [ ] **Step 2: Rulează și confirmă că pică.** Expected: FAIL pe primul test.

- [ ] **Step 3: Implementează.**
  - Împarte `when-picker` în cele două partiale, fără să schimbi markup-ul rezultat.
  - În pasul 2, pe telefon: `x-week-strip mode="count"` (`:city` din `createMatch.city` prin `x-bind`, actualizat la schimbarea orașului), 📅 → `x-sheet name="when-month"` cu `when-calendar`, apoi `when-slots`, apoi `day-matches` imediat sub ore. Varianta inline actuală din pasul 2 devine `.d-only`, dacă era vizibilă pe desktop; verifică asta pe baseline.
  - Bara de sus: înapoi, titlul și indicatorul de pași 4 segmente, refolosind `.step-form__bar`.
  - `.step-form__nav` lipită jos sub 1024px, cu `navSummary` = ziua și ora alese sau `match.create.when_none`.
  - `pickDate` trimite `date-picked`.
  - `@section('no_tabbar', true)`.

- [ ] **Step 4: Rulează testele.** `composer test`. Expected: PASS, inclusiv „renders the planner…” și testele de validare existente.

- [ ] **Step 5: Verificare vizuală.**
  - Un meci se creează cap-coadă pe 360px.
  - Schimbarea orașului actualizează cifrele din bandă.
  - O săptămână peste sfârșitul lunii.
  - RU la 360px.
  - La 1024/1280, identic cu baseline-ul, cu coloana din dreapta neschimbată.

- [ ] **Step 6: Commit.** `git commit -m "Creează pe telefon: banda săptămânii, orele sub zi, bara de jos cu alegerea"`

---

### Task 10: Stil comun pe celelalte pagini (spec §6.7) și verificarea finală

**Files:**
- Modify: `resources/views/profile/show.blade.php`, `resources/views/profile/edit.blade.php`, `resources/views/chat/show.blade.php`, `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, `resources/views/legal/cookies.blade.php`, `resources/views/legal/privacy.blade.php`, `public/assets/css/base.css` / `components.css` / `chat.css` (doar sub 1023px)
- Test: suita existentă (fără teste noi: fără schimbări de comportament)

- [ ] **Step 1: Aliniază stilul sub 1024px**, fără să reordonezi conținutul: titlul mare la începutul paginii, `list-group` / `list-row` rotunjite, ținte de 44px, butonul principal al formularelor de profil și auth în `x-action-bar` (sau stilul ei). Chat-ul ocupă înălțimea dintre titlu și tab bar.
- [ ] **Step 2: Rulează testele.** `composer test`. Expected: toate PASS.
- [ ] **Step 3: Verificarea finală.**
  - Toate paginile din baseline, la 360, 390, 768, 1023, 1024 și 1280, în RO, plus RU la 360.
  - La 1024 și 1280, fiecare captură coincide cu baseline-ul. Orice diferență e un defect de reparat înainte de commit.
  - Parcurge cele 5 puncte din „Review Focus” și notează rezultatul.
- [ ] **Step 4: Commit.** `git commit -m "Telefon: același stil pe profil, chat, autentificare și paginile legale"`

---

## Self-review (făcut)

- **Acoperirea spec-ului.** Fiecare punct are un task:
  - §6.1 → Task 1;
  - §6.2 → Task 3;
  - §6.3 → Task 4, 5 și 6;
  - §6.4 → Task 7;
  - §6.5 → Task 8;
  - §6.6 → Task 9;
  - §6.7 → Task 1 (profil, login) și Task 10;
  - §7 → Task 2 și 8;
  - §5 (DESIGN.md, pictograme, Leaflet, verificări) → Task 1, 2, 6 și Global Constraints;
  - §9 → tabelul din Task 7, `distance.js` (Task 2), `x-match-row` (Task 2), numărul din Task 8, `unreadByRoom` filtrat (Task 8), fără sortare (nicio sarcină o adaugă).
- **Nume consecvente.** `open-sheet` / `close-sheet`, `week-day` / `date-picked` / `calendar-open-day`, `.m-only` / `.d-only`, `no_tabbar` → `no-tabbar`, `rooms.map`, `x-match-row` cu `role` (`organizer`, `joined`, `interested`) și `unread` sunt folosite la fel peste tot.
- **Ce acoperă testele.** Cele 5 riscuri din Review Focus fără test automat (lungimea textelor rusești, granița 1024px, stările hărții, locația refuzată, titlurile lungi) sunt pași de verificare în task-urile care le dețin. Pentru lista goală a hărții și titlul lung există și teste.
