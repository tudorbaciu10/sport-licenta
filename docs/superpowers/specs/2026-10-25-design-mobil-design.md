# Design mobil după wireframe-urile Figma (proiectul 1)

Data: 2026-10-25 · Ramura: `design-mobil` · Sursa vizuală: Figma „Sport Booking App – Wireframes” (`xNoFbzgNzaaLQsa52VGQgN`), ecranele 01 Home, 02 Explore, 03 Venue Details, 04 Select Date & Time, 07 My Bookings + pagina Components. Machetele aprobate în discuție sunt în `.superpowers/brainstorm/1759-1792874358/content/` (`home-layout.html` varianta B, `explore-layout.html` varianta 1 + Hartă, `match-detail.html`, `my-matches.html` varianta B, `create-when.html` varianta B).

## 1. De ce

Autorul platformei vrea ca Sport.md, pe telefon, să arate și să fie organizat ca wireframe-urile din Figma: salut și oraș sus, căutare, sporturi ca chips, rânduri compacte, comutator Listă/Hartă, bară de acțiune lipită jos, bandă a săptămânii în loc de calendarul lunii. Desktopul actual îi place și rămâne neschimbat.

Utilizatorul-țintă e „Andrei”: un amator care vrea să găsească repede un meci (cel mai apropiat, la ora potrivită, cu locuri libere), inclusiv în afara orașului lui. Pentru raioane, harta la nivel de țară e o piesă importantă: cine stă în Fălești trebuie să vadă că la Bălți se caută jucători.

Acesta e primul din șase proiecte agreate (ordinea: 1 design mobil, 2 meciuri mai bune: formate/minim+termen/nivel/distanță, 3 terenuri și antreprenori, 4 încredere, 5 notificări/PWA, 6 plata cu cardul). Doar proiectul 1 e descris aici.

## 2. Obiective

- Sub 1024px lățime (telefon și tabletă, zona în care apare deja tab bar-ul), ecranele principale urmează structura din Figma, în culorile, fontul și componentele Sport.md.
- O hartă a meciurilor deschise, care se poate micșora la nivelul întregii țări.
- Toate celelalte pagini (profil, chat, login, legal) primesc același stil, ca aplicația să pară unitară.

## 3. Ce nu face (non-obiective)

- Nu schimbă nimic de la 1024px în sus. Desktopul trebuie să arate pixel cu pixel ca acum.
- Nu adaugă terenuri, rezervări, plăți, formate (5×5, 6×6), nivel pe meci, notificări sau fotografii. Acestea sunt proiectele 2–6. Chips-ul „6×6” din machete nu se implementează acum.
- Nu schimbă logica din backend. Singura excepție aprobată e endpointul hărții (§6.3).

## 4. Anti-obiective (ar fi un eșec chiar dacă „merge”)

- Desktopul se schimbă vizibil (alt spațiu, alt header, alt card).
- Culori, fonturi sau mărimi inventate în afara `public/assets/css/tokens.css`, sau culoarea sportului folosită ca text mic.
- Ținte tactile sub 44px. Figma are butoane-pictogramă de 40px, stepper de 32px și chips de 33–35px: toate se ridică la `--tap`.
- Texte hardcodate. Orice text vizibil nou intră în `lang/ro` **și** `lang/ru`.
- Lista devine lentă pe date mobile din cauza hărții. Leaflet și datele hărții se încarcă doar la prima apăsare pe „Hartă”.

## 5. Constrângeri (din proiect)

- Laravel 13, Blade + componente, Alpine.js, CSS simplu cu variabile, fără build. Componentele din `resources/views/components/` se refolosesc înainte de a scrie markup nou, iar componentele noi apar și în `/styleguide`.
- Regulile din `docs/DESIGN.md` și skill-ul `sportmd-design` au prioritate. D5 rămâne valabil: fără fotografii, doar pictograme pe fundal tint. Locul pozei din Figma îl ia banda în culoarea sportului.
- Pictograme Lucide ca SVG în `resources/views/components/icon/`. Lipsesc `sliders-horizontal`, `map`, `list` și `locate`. `share-2` și `calendar-days` există deja.
- **`docs/DESIGN.md` se actualizează în acest proiect** (primul pas), ca să nu contrazică deciziile de aici. CLAUDE.md spune că DESIGN.md câștigă la conflict. De actualizat: §6.1 (Acasă începe cu salutul pe telefon), §6.3 (chips + detalii în loc de grilă pe telefon), §6.8 (calendarul de pe Acasă e într-o foaie pe telefon), stilul tab bar-ului și o secțiune scurtă „Telefon < 1024px, după Figma”, cu trimitere la acest document.
- `prefers-reduced-motion` respectat (foile care urcă de jos apar fără animație).
- Fiecare ecran se verifică la 360, 390, 768 și 1280px. La 1280px se compară cu starea de dinainte: nu trebuie să existe diferențe.
- Hărțile folosesc deja Leaflet 1.9.4 de pe cdnjs și tile-uri OpenStreetMap (`rooms/show.blade.php`, `public/assets/js/create-match.js`). Se păstrează. Pentru grupare se adaugă `Leaflet.markercluster` (1.5.3, cdnjs).
- `composer test` (Pest) după orice modificare de backend, cu config-ul necache-uit. Commit mic după fiecare pas.

## 6. Ce s-a hotărât, ecran cu ecran (sub 1024px)

### 6.1 Peste tot

- **Antetul de sus (`.app-header`) nu se mai afișează sub 1024px.** Logo-ul, RO/RU, iconița de chat și butoanele de cont dispar de pe telefon. Antetul se ascunde **doar din CSS**: markup-ul rămâne, iar `ChatTest` verifică `class="header-chat"`. Limba se alege din Profil (utilizator logat) sau de pe paginile de login și înregistrare (vizitator). La 1024px+ antetul rămâne exact ca acum.
- **Tab bar-ul** rămâne cu cele 5 butoane actuale (Acasă, Caută, Creează la mijloc, Ale mele, Profil; vizitatorul are „Intră”). Stilul se aliniază la Figma: pictogramă deasupra textului, cel activ în `--text` și semibold.
- Fiecare pagină principală începe cu **titlul mare** (`t-large`/`t-title1`) și, la dreapta, cel mult un buton-pictogramă rotund de 44px.
- Butoanele, chips-urile, comutatorul segmentat și câmpul de căutare au forma din pagina Components din Figma, făcută din clasele existente (`.btn`, `.chip`, `.segmented`, `.input`).

### 6.2 Acasă (varianta B)

De sus în jos:
1. **„Salut, {prenume}”** (vizitator: „Salut!”). Dedesubt, orașul cu pictograma pin și o săgeată în jos, care deschide alegerea orașului (comportamentul actual al `x-dropdown` de oraș). În dreapta: butonul rotund de **chat** cu numărul de necitite (dacă `Navigation::chat()` nu e null). Vizitatorul are acolo un buton „Intră”.
2. **Căutarea** pe toată lățimea, fără butonul „Caută” alături (se trimite cu Enter sau „Caută” de pe tastatură).
3. **„Sporturi” + „Vezi toate”** („Vezi toate” duce la `/rooms` cu orașul ales), apoi un rând de chips care se derulează lateral, cu „Toate” primul. Fiecare chip are punctul în culoarea sportului. Ordinea și link-urile sunt cele de acum (cele mai active sporturi primele).
4. **Bannerul spre calendar**, în stilul bannerului promo din Figma: un titlu scurt, de felul „Când vrei să joci?”, un subtitlu și butonul „Calendar”. Butonul deschide calendarul public existent (`x-calendar`) într-o foaie care urcă de jos. Pe telefon calendarul nu mai stă direct pe pagină.
5. **Meciurile următoare** (titlul și datele de acum: „Urmează în :city”, cel mult 3 pe telefon) cu **cardul mare**: sus o bandă în culoarea sportului (tint) cu pictograma sportului și **ora mare**, apoi titlul, badge-ul de locuri, adresa și prețul de persoană.
6. Mai jos rămân „Cum funcționează” și bannerul final („Joci în seara asta?”), restilizate.

**Distanța** („2,1 km”) apare pe carduri și rânduri doar dacă browserul a dat voie la locație și meciul are coordonate. Se calculează în browser, fără să se trimită locația la server. Fără locație se arată adresa sau orașul. Permisiunea se cere **doar** din butonul „Lângă mine” de pe hartă (§6.3), niciodată la deschiderea paginii. După ce e dată, distanța apare peste tot (permisiunea se verifică cu Permissions API, apoi se folosește `getCurrentPosition`).

### 6.3 Caută (varianta 1) și Hartă

Sus: titlul „Caută” și numărul de meciuri; **căutarea** + un **buton de filtre** pătrat, de 48px, cu numărul filtrelor active. Butonul deschide o foaie de jos cu tot ce e acum în chips-uri și în „Mai multe filtre”: Azi, Mâine, Weekend, Interior, Exterior, Locuri libere, Gratuit, data, ora de la, orașul. Sub căutare: **un singur rând de chips cu sporturile**, apoi comutatorul **Listă / Hartă**.

**Lista:** grupată pe zile, ca acum, cu **rândul compact** (Venue Row din Figma), care are:
- un pătrat de 64px în tint-ul sportului, cu ziua scurtă și ora;
- titlul;
- un rând cu **pictograma mică a sportului și numele lui** (regula „culoare + pictogramă + nume”), urmate de distanță (dacă e disponibilă) sau oraș și de prețul de persoană;
- badge-ul de locuri și chevron-ul. Paginarea, skeleton-ul și starea goală rămân.

**Harta:** ocupă spațiul sub comutator.
- Arată **toate meciurile viitoare deschise care mai au locuri** și au coordonate, din toată țara. Filtrele alese (sport, zi, interior/exterior, gratuit, text) se aplică. Orașul **nu** se aplică: harta însăși e zona.
- Pornește centrată pe orașul ales (`City::CENTERS`) sau pe Chișinău. Dacă browserul are deja voie la locație, pornește centrată pe utilizator.
- Are un buton **„Lângă mine”** (44px, colțul hărții) care cere locația, centrează harta pe utilizator și îi arată punctul. Dacă locația e refuzată, apare un mesaj scurt („Nu avem acces la locație…”) și harta rămâne unde era.
- Pinii au pictograma sportului pe fondul culorii sportului și ora. Pinul unui meci aproape plin are contur `--warning`.
- La zoom mic, pinii se **grupează** în cercuri cu număr (markercluster). Atingi cercul și harta se apropie.
- Atingi un pin: jos apare un **card compact** (același rând compact), iar atingerea cardului deschide meciul.
- Leaflet, markercluster și datele se încarcă la prima apăsare pe „Hartă”. Alegerea Listă/Hartă se ține în URL (`?view=map`), ca butonul înapoi să păstreze modul.

**Endpointul hărții (singura schimbare de backend, aprobată):**
- `GET /rooms/map`, public, `throttle:60,1`, JSON. Acceptă aceiași parametri ca `/rooms`, mai puțin `city` și `page`.
- Interogarea: `Room::upcoming()->filter($filtre)`, cu `status = open`, cu locuri libere și cu `latitude`/`longitude` nenule.
- Fiecare meci trimite doar ce trebuie pentru pin și card: `id`, `url`, `title`, `sport` (nume, slug, variabila CSS, pictograma), `starts_at` (ISO) plus ziua și ora deja formatate în limba paginii, `location`, `city`, `price`, `spots_left`, `availability`, `lat`, `lng`.
- Plafon rezonabil (de exemplu 500 de meciuri, cele mai apropiate în timp). Fără date personale.
- Teste Pest noi: doar meciurile deschise, viitoare, cu locuri și cu pin. Filtrele se aplică, parametrul `city` e ignorat, forma JSON e cea de mai sus, iar throttle-ul există.

### 6.4 Pagina meciului (machetă aprobată)

- **Sus:** bandă înaltă (aproximativ 200px) în tint-ul sportului, cu pictograma mare a sportului la stânga-jos și, la dreapta-jos, ziua plus **ora mare**. Peste ea, butoanele rotunde **înapoi** (stânga) și **distribuie** (dreapta). Distribuie folosește Web Share API; fără el, copiază linkul și arată un mesaj scurt.
- **Dedesubt:** titlul, apoi organizatorul (avatar + nume) și badge-ul de locuri, apoi adresa cu distanța. Urmează **chips informative** cu datele existente: sportul, interior/exterior, echipamentul.
- **Nu dispare nimic din conținut.** Ce nu încape în chips (cine încasează banii, „X din Y · N locuri”) rămâne ca rânduri scurte de detalii sub chips.
- **Separator, apoi:** „Jucători” cu „X din Y”, avatarele suprapuse (cu un cerc „+” punctat pentru locurile libere) și bara de progres, ca rezumat. Sub ele rămâne **lista cu nume** existentă (cu „Organizator” / „Tu”). Apoi harta mică, nota, regulile și cei interesați, ca acum.
- **Bara de jos lipită de ecran** (pe această pagină tab bar-ul se ascunde sub 1024px). Regula de așezare:
  - **o singură acțiune:** stânga „Preț de persoană” + suma (sau „Gratuit”), dreapta butonul;
  - **două acțiuni:** cele două butoane împart bara 50/50, iar prețul apare ca chip sus, între chips-urile informative. Textele în rusă sunt mai lungi și nu încap trei elemente pe 360px.

  Butoanele, pe stări. Logica de acum (`RoomMembership`, `Chat::canChat`) nu se schimbă, doar așezarea:

  | Starea | Butonul principal | Butonul secundar |
  |---|---|---|
  | Vizitator | „Intră ca să te alături” | — |
  | Logat, nu e în meci, meci deschis | „Mă alătur” | „Mă interesează” |
  | Logat, marcat „interesat” | „Mă alătur” | „Nu mă mai interesează” |
  | Logat, meci complet | „Complet”, dezactivat | „Mă interesează” / „Nu mă mai interesează”, ca acum |
  | Jucător, chat-ul există (`Chat::canChat`) | „Chat-ul echipei” | „Renunț” |
  | Jucător, chat-ul nu există încă (sub 2 jucători) | „Invită jucători” (distribuie) | „Renunț” |
  | Organizator | „Invită jucători” (distribuie, ca acum) | „Chat-ul echipei”, dacă există |

  Butonul rotund „distribuie” din bandă rămâne pentru toată lumea.

### 6.5 Meciurile mele (varianta B)

1. Titlul „Meciurile mele” și, la dreapta, butonul rotund 📅 (`calendar-days`), care deschide calendarul personal existent (`x-calendar personal mine`) într-o foaie de jos.
2. **Banda săptămânii:** 7 zile, cu săptămâna curentă la început. Se derulează lateral spre săptămânile următoare. Zilele cu meciurile tale au un punct în culoarea sportului.
   - Datele vin din `/calendar/days?mine=1&month=YYYY-MM`, care merge **câte o lună**: o săptămână care trece peste sfârșitul lunii cere două cereri.
   - Endpointul trimite slug-uri de sport, nu variabile CSS. Maparea slug → variabilă se construiește ca în `x-calendar` (`sportVars`).
   - Atingerea unei zile deschide foaia calendarului pe acea zi.
3. **Viitoare / Trecute**, comutatorul segmentat existent. Numărul apare doar pe tab-ul activ („Viitoare (3)”, din totalul paginatorului), ca să nu fie nevoie de o interogare în plus.
4. **Rânduri** în stilul My Bookings din Figma:
   - pătratul cu ziua scurtă și data, titlul, ora și locul, plus pictograma și numele sportului;
   - eticheta de **rol**: Organizezi (plin, `--text`), Joci (gri), Te interesează (contur punctat);
   - badge-ul de locuri;
   - la dreapta, deasupra chevron-ului, numărul de **mesaje necitite** din chat. Se calculează cu `Chat::unreadByRoom`, primind **doar** id-urile meciurilor din `Chat::rooms($user)`. Funcția nu verifică singură accesul, deci un meci în care ești doar interesat sau al cărui chat e închis nu are cifră.
5. Sub listă, bannerul punctat **„Îți lipsesc jucători?”** cu butonul „Organizează un meci”. Starea goală și paginarea rămân.

### 6.6 Creează un meci (pasul „Data și ora”, varianta B)

- Pe telefon, formularul are o **bară de sus**: înapoi, titlul „Organizează un meci” și indicatorul de pași (4 segmente). Tab bar-ul se ascunde pe această pagină sub 1024px, ca pe pagina meciului. Pașii, validările și restul câmpurilor rămân cum sunt.
- **`rooms/partials/when-picker.blade.php` e un singur partial** cu calendarul lunii **și** grila de ore. E desenat de două ori: în pasul 2 și în coloana din dreapta de pe desktop. Pentru telefon se împarte în două bucăți (calendarul lunii și grila de ore), iar coloana de desktop trebuie să rămână identică. Starea rămâne în `createMatch` (`public/assets/js/create-match.js`).
- În pasul 2, **ziua** se alege dintr-o **bandă a săptămânii** (aceeași componentă ca la §6.5), cu luna afișată și 📅, care deschide calendarul lunii într-o foaie. Sub fiecare zi, o cifră mică arată câte meciuri sunt deja în orașul ales (`/calendar/days?city=&month=`, câte o lună, ca la §6.5).
- Dedesubt: grila de ore (Dimineața/Ziua/Seara) cu forma Time Slot din Figma, apoi „Tot în ziua asta” (partialul existent `day-matches`) imediat sub ore, nu la final.
- **Bara de jos lipită de ecran:** stânga ziua și ora alese (sau „Nu ai ales încă ziua și ora”), dreapta „Continuă”. Pe ceilalți pași, bara are „Înapoi” / „Continuă” / „Publică”, ca navigarea actuală a `x-step-form`.
- La 1024px+ rămâne coloana din dreapta cu `when-picker` și calendarul lunii, ca acum.

### 6.7 Celelalte pagini (doar stil comun)

Profil, editare profil, chat (listă și conversație), login, înregistrare, paginile legale și styleguide primesc același limbaj vizual: titlu mare, rânduri rotunjite (`list-group`/`list-row`), ținte de 44px, bară de acțiune jos unde e un buton principal. Ordinea conținutului **nu** se schimbă. Adăugiri concrete:
- **Profil:** un rând „Limba” cu RO/RU (folosește ruta existentă `locale`) și un rând „Chat”, dacă `Navigation::chat()` există. „Ieși” și „Suport” există deja.
- **Login / Înregistrare:** comutatorul RO/RU sus, pentru că antetul nu mai apare pe telefon.

## 7. Componente noi sau extinse

(Fiecare intră și în `/styleguide`.)
- `x-match-row`: rândul compact din §6.3, refolosit în Caută, în cardul de pe hartă și, cu eticheta de rol și necitite, în Meciurile mele.
- `x-match-card`: primește varianta „mare” cu bandă și oră mare pentru Acasă sub 1024px. Desktopul păstrează cardul actual.
- `x-week-strip`: banda săptămânii (§6.5, §6.6), cu datele de la `/calendar/days`, punctele colorate sau cifra mică și evenimentul de alegere a zilei.
- `x-sheet`: foaia care urcă de jos (filtre, calendar, alegerea orașului). Accesibilă: `role="dialog"`, focus prins înăuntru, se închide cu Esc, fundal semi-opac, fără animație sub `prefers-reduced-motion`.
- `x-action-bar`: bara de jos lipită de ecran, cu rezumat la stânga și butonul la dreapta (pagina meciului, creare).

## 8. Ce rămâne la alegerea programatorului

- Cum se separă markup-ul telefon/desktop: elemente noi ascunse la 1024px+, CSS sub `@media (max-width: 1023px)` etc. Regula fermă e doar că la 1024px+ nimic nu se schimbă.
- Între 768 și 1023px (tabletă): același design ca pe telefon, cu coloana de conținut centrată și limitată (sugestie: 720px), iar cardurile mari de pe Acasă pot fi două pe rând.
- Pictogramele exacte Lucide, textele exacte în RO și RU (scurte, în tonul celor existente), dimensiunile interne ale pinilor.
- Ordinea pașilor de implementare, cu condiția ca fiecare pas să fie un commit mic și verificat la cele 4 lățimi.
- Cum se testează vizual desktopul: capturi înainte/după la 1280px pe aceleași pagini.

## 9. Decizii luate de Claude (de verificat la citire)

- **Fără „Sortare” pe lista din Caută** în acest proiect. Sortarea după distanță ar cere locația utilizatorului pe server, adică o altă schimbare de backend. Lista rămâne în ordinea orei, grupată pe zile. Ordonarea după distanță vine cu proiectul 2 („distanță”).
- Pe Acasă, titlul secțiunii rămâne „Urmează în :city”, nu „Meciuri lângă tine”, pentru că fără locație nu e adevărat că sunt „lângă tine”.
- Chat-ul e accesibil pe telefon din butonul de pe Acasă, din rândul din Profil și din pagina meciului. Numărul de necitite apare și ca punct pe tab-ul „Ale mele”.
- Banda săptămânii deschide calendarul existent pe ziua aleasă, în loc să filtreze lista de pe Meciurile mele (lista nu are filtru pe zi, iar adăugarea lui ar însemna backend).
- Numărul de necitite pe rândurile din Meciurile mele se citește în view cu `Chat::unreadByRoom`, cum face deja layout-ul cu `Navigation::chat()`, fără schimbare în controller.
- Bara de jos a paginii meciului: tabelul de stări din §6.4. Când e un singur buton, prețul stă în bară; când sunt două, prețul urcă între chips. Jucătorul care încă nu are chat primește „Invită jucători”.
- Permisiunea de locație se cere doar din butonul „Lângă mine” de pe hartă.
- În rândul compact, sportul apare și cu pictogramă și nume, nu doar prin culoarea pătratului.
- Pe pagina meciului nu dispare nicio informație: avatarele suprapuse sunt un rezumat deasupra listei cu nume, iar detaliile care nu încap în chips rămân ca rânduri.
- „Viitoare (N)” arată numărul doar pe tab-ul deschis.
- `docs/DESIGN.md` se actualizează ca prim pas.
