# Sport.md: document de design

## 0. Primii pași (înainte de orice design)

- [x] Commit al stării actuale pe `#Ilie` („Stare înainte de redesign”, `962ed6b`).
- [x] Branch nou: `redesign-ui`.
- Se păstrează baza de date de test (`sport_licenta_ilie`) și contul `demo@sport.md`.

## 1. Ce este Sport.md

Platformă web pentru Moldova unde găsești parteneri de sport: cauți un meci în orașul tău, ocupi un loc sau creezi propriul meci, cu reguli și număr de jucători.

**Problema:** organizarea unui meci amator (ex. fotbal 5×5 sau 12×12) se face în grupuri de WhatsApp/Telegram/Viber, unde informația se pierde, oamenii promit și nu vin, iar cine organizează adună singur lista și banii.

**Public țintă:** amatori de sport de 16–45 de ani din Chișinău și din orașele mari, care folosesc în principal telefonul.

**Promisiune (o frază):** „Găsești un meci în orașul tău în 30 de secunde.”

### 1.1 Starea tehnică actuală

| Zonă | Ce există |
|---|---|
| Backend | Laravel 13.8, PHP 8.3, MySQL 8, Pest (10 teste) |
| Șabloane | Blade + componente (`layouts/app`, `<x-match-search />`) |
| JS | Alpine.js 3.14 (CDN); Three.js și GSAP doar pe pagina principală |
| CSS | CSS simplu; aplicația folosește `public/assets/css/app.css` |
| Rute reale | `/register`, `/login`, `/rooms`, `/rooms/create`, `/rooms/{id}`, `/dashboard` |
| Rute demonstrative | `/` (pagina principală, parțial cu date inventate) |
| DB | `cities` (7), `sports` (8, cu `color` și `icon`), `rooms`, `room_user` |

### 1.2 Concluzii din cercetarea competitorilor

Surse analizate: Spond, GoodRec, Sportly, Joaka, Kickr, Courtica.md, FTogether (Kazahstan), Crossball, Playo.

| Observație | Decizie pentru Sport.md |
|---|---|
| Aplicațiile care obligă la cont înainte să vezi evenimentele pierd utilizatori | Vizitatorul vede lista și detaliile fără cont. Contul se cere doar la „Ocupă un loc”. |
| Listele goale omoară produsul | Lansare cu Chișinău + 2–3 sporturi, nu cu toată țara. |
| Aplicațiile lente sau instabile primesc recenzii proaste (Playo, Spond) | Fiabilitatea și viteza contează mai mult decât numărul de funcții. |
| „Chips” de filtrare rapidă (FTogether, GoodRec) sunt mai rapide decât formularele | Filtre sub formă de chips deasupra listei. |
| Buton „Join” fix jos pe telefon (GoodRec) | Buton principal sticky pe pagina de detalii. |
| Nivel descris în cuvinte, nu doar etichete (Sportly) | „Începător: învăț bazele”, „Mediu: joc constant”, „Avansat: competitiv”. |
| Statistici și clasamente (Crossball, FTogether) | Funcție pentru versiunea 3, dar structura DB să o permită. |
| Competitori locali: Courtica.md (rezervări), Socca Moldova (ligă pe Facebook) | Diferențiere: multi-sport, comunitate, fiabilitatea jucătorilor, RO + RU. |

> ⚠️ FTogether e foarte apropiat ca idee. În teză se spune deschis că problema e validată de piață și se explică ce face diferit Sport.md.

## 2. Decizii de proiect

### 2.1 Decise

- Stil vizual inspirat de Apple: fundal alb, mult spațiu liber, text clar, puține elemente, colțuri rotunjite, culoare doar ca accent.
- Mobile-first: se proiectează întâi pentru ~390px lățime, apoi se extinde.
- Două straturi de culoare: culoarea platformei (fixă, neutră) + culoarea fiecărui sport (accent).
- Font: Inter. Pictograme: Lucide.
- Fără clone: inspirație din principii; nu se copiază logo-uri, fonturi proprietare sau pictograme Apple.

### 2.2 De hotărât

| # | Întrebare | Recomandare | Decizie |
|---|---|---|---|
| D1 | Ce facem cu pagina principală actuală (temă închisă, minge 3D, GSAP)? | Redesenăm întâi aplicația (`/rooms` etc.), pagina principală la urmă. Mingea 3D rămâne doar dacă se potrivește cu stilul deschis; altfel, o secțiune simplă. | **Aplicația întâi.** Pagina principală rămâne neschimbată până în Faza 6; atunci decidem soarta mingii 3D. |
| D2 | Tailwind/Vite sunt instalate dar nefolosite. Le folosim? | CSS simplu cu variabile (tokens), fără pas de build. Scoatem Tailwind/Vite din `package.json` dacă nu le folosim. | **Le scoatem.** CSS simplu cu tokens, fără build; Tailwind/Vite se elimină din proiect. |
| D3 | Temă închisă? | Tokens pregătite acum, implementare în faza finală. | **Tokens acum, implementare la final** (Faza 7). |
| D4 | Limbi | RO + RU prin fișiere de traducere Laravel (`lang/`), de la început. | **RO + RU de la început**, prin `lang/ro` și `lang/ru`, cu selector de limbă. |
| D5 | Fotografii sau doar pictograme? | Pictograme pe fundal pastel. Fotografiile slabe strică tot aspectul. | **Doar pictograme** pe fundal tint; fotografii doar dacă apar poze proprii bune. |

## 3. Principii de design

1. **Claritate înainte de decor.** Fiecare ecran are un singur scop evident.
2. **Densitate controlată.** Platforma afișează multă informație (oră, preț, locuri, nivel). Modelul sunt aplicațiile Apple (Calendar, Maps, Fitness), nu site-ul de marketing apple.com.
3. **Culoarea înseamnă ceva.** Culoarea unui sport apare doar în pictograma sportului, banda cardului și eticheta evenimentului. Restul interfeței e neutru.
4. **Nu ne bazăm doar pe culoare.** Fiecare sport are mereu și pictogramă, și nume (daltonism, contrast).
5. **Degetul mare primul.** Ținte tactile de minim 44×44px, navigare jos pe telefon.
6. **Răspuns imediat.** Skeleton la încărcare, mesaje de eroare care spun ce să faci, ecrane goale cu o acțiune clară.
7. **Mișcare puțină.** Tranziții scurte (150–250ms), doar ca răspuns la acțiunea utilizatorului. Se respectă `prefers-reduced-motion`.
8. **O singură „mare idee” memorabilă:** cardurile de sport colorate și clare. Restul rămâne liniștit.

## 4. Design tokens

Toate într-un singur fișier, `public/assets/css/tokens.css`. Restul CSS-ului folosește numai aceste variabile.

> ⚠️ Valorile culorilor Apple se verifică în [HIG: Color → System colors](https://developer.apple.com/design/human-interface-guidelines/color). Cele de mai jos sunt din memorie.

### 4.1 Culori de bază (temă deschisă)

| Token | Valoare | Utilizare |
|---|---|---|
| `--bg` | `#FFFFFF` | fundalul paginii |
| `--bg-subtle` | `#F5F5F7` | fundal secțiuni, câmpuri, carduri secundare |
| `--text` | `#1D1D1F` | text principal |
| `--text-2` | `#6E6E73` | text secundar |
| `--separator` | `#D2D2D7` | linii, borduri fine |
| `--brand` | `#5856D6` (indigo) | butoane principale, linkuri, focus |
| `--brand-hover` | `#4745B8` | hover/apăsat pe `--brand` |
| `--on-brand` | `#FFFFFF` | text pe butoane `--brand` |

### 4.2 Culori de stare

| Token | Valoare | Utilizare |
|---|---|---|
| `--success` | `#34C759` | confirmare (⚠️ identic cu fotbal, vezi nota) |
| `--danger` | `#FF3B30` | erori, „LIVE” |
| `--warning` | `#FF9500` | „aproape plin” |
| `--full` | `#8E8E93` | meci complet |

> Culorile de stare nu au voie să semene cu o culoare de sport. Pentru „confirmare” se folosește pictograma ✓ cu `--text`, nu verdele fotbalului. „LIVE” și erorile folosesc roșul, care nu e atribuit niciunui sport.
>
> ⚠️ De rezolvat: `--warning` (`#FF9500`) e identic cu baschet.

### 4.3 Culoarea fiecărui sport

| Sport | Token | Valoare |
|---|---|---|
| Fotbal | `--sport-fotbal` | `#34C759` |
| Baschet | `--sport-baschet` | `#FF9500` |
| Tenis | `--sport-tenis` | `#FFCC00` |
| Volei | `--sport-volei` | `#A2845E` |
| Handbal | `--sport-handbal` | `#FF2D55` |
| Alergare | `--sport-alergare` | `#007AFF` |
| Tenis de masă | `--sport-tenis-masa` | `#AF52DE` |
| Padel | `--sport-padel` | `#30B0C7` |

Reguli:

- Culoarea sportului = fundal sau bandă, niciodată text mic (galbenul și culorile deschise nu au contrast pe alb).
- Fundal de pictogramă: versiunea „tint”, aceeași culoare la ~14% opacitate.
- Text pe fundal colorat: `--text` (închis) pentru galben/portocaliu/verde/turcoaz, alb pentru restul. Fiecare pereche se testează cu [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/) (țintă 4.5:1 pentru text normal).
- Albastrul (alergare) și indigo-ul brandului sunt apropiate: nu se pun alăturate fără pictogramă.
- Coloana `color` din tabelul `sports` se actualizează (migrare sau seeder), ca baza de date și CSS-ul să aibă o singură sursă de adevăr. Dispare astfel și inconsistența veche la fotbal (`#3FBF7A` vs `#5BE08F`).

### 4.4 Tipografie

- Font: **Inter** (Regular 400, Medium 500, Semibold 600, Bold 700).
- Stivă: `"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`.
- Diacritice și chirilică: Inter le suportă pe ambele (RO și RU).
- Big Shoulders Display, Libre Franklin și DotGothic16 se elimină din aplicație (rămân temporar doar pe pagina principală până la redesign).

| Rol | Mărime / înălțime rând | Greutate |
|---|---|---|
| Titlu mare | 34px / 41px | 700, `letter-spacing: -0.02em` |
| Titlu 1 | 28px / 34px | 700 |
| Titlu 2 | 22px / 28px | 600 |
| Subtitlu | 17px / 22px | 600 |
| Text | 17px / 24px | 400 |
| Text mic | 15px / 20px | 400 |
| Notă | 13px / 18px | 400 |

Lungimea unui rând de text: sub 80 de caractere.

### 4.5 Spațiere, colțuri, umbre

- **Spațiere:** multipli de 4px (4, 8, 12, 16, 24, 32, 48, 64). Margini laterale pe telefon: 16px.
- **Colțuri:** card 20px; câmpuri și butoane 12px; chips și insigne 999px (pastilă); pictogramă sport 14px.
- **Umbre:** foarte subtile (ex. `0 1px 2px rgba(0,0,0,.06)`) sau deloc, doar bordură `--separator`. Nu aceeași umbră peste tot.
- **Tranziții:** 180ms, ease-out.

### 4.6 Temă închisă (faza finală)

| Token | Valoare |
|---|---|
| `--bg` | `#000000` |
| `--bg-subtle` | `#1C1C1E` |
| `--text` | `#F5F5F7` |
| `--text-2` | `#A1A1A6` |
| `--separator` | `#38383A` |

Culorile sporturilor rămân aceleași (se ajustează doar dacă nu au contrast).

## 5. Resurse (toate gratuite)

### 5.1 Fonturi

- Inter: <https://fonts.google.com/specimen/Inter> (SIL Open Font License, liberă și pentru uz comercial).
- Fișierele `.woff2` se servesc din proiect (`public/assets/fonts/`), nu de pe CDN. Alternativ: pachetul npm `@fontsource/inter`.
- SF Pro nu se folosește pe site (licența e legată de platformele Apple). ⚠️ Termenii: <https://developer.apple.com/fonts/>

### 5.2 Pictograme

- **Lucide:** <https://lucide.dev> (licență ISC, linie subțire, apropiat de aspectul Apple). SVG-urile se pun în componente Blade (`resources/views/components/icon/`).
- Alternative: Phosphor (<https://phosphoricons.com>), Heroicons (<https://heroicons.com>).
- Pictograme de sport: Lucide nu acoperă toate sporturile. Se alege o singură familie, iar lipsurile se completează cu SVG-uri simple desenate în același stil.
- ⚠️ De verificat dacă există un pachet Blade pentru Lucide pe Packagist; altfel, SVG inline.
- SF Symbols nu se folosesc pe web.

### 5.3 Imagini

- Fotografii gratuite: <https://unsplash.com>, <https://www.pexels.com> (licența se verifică pentru fiecare poză).
- Cea mai bună variantă: fotografii proprii de pe terenurile din Chișinău.
- Compresie: <https://squoosh.app> (WebP, sub 150KB per imagine).
- Până există poze bune: pictogramă mare pe fundal tint (vezi 4.3).

### 5.4 Culori și contrast

- Apple HIG, culori: <https://developer.apple.com/design/human-interface-guidelines/color>
- Test contrast: <https://webaim.org/resources/contrastchecker/>

### 5.5 Inspirație și reguli

- Apple Human Interface Guidelines: <https://developer.apple.com/design/human-interface-guidelines/> (sursă bună de citat în teză).
- Referințe din cercetare: GoodRec (listă și detalii), FTogether (chips), Sportly (onboarding), Crossball (statistici).

## 6. Ecrane

Aplicație mobile-first. Pe telefon: bară de navigare jos cu 4–5 destinații. Pe laptop (>1024px): același conținut în grilă, navigare sus, conținut în max 1100px lățime.

**Bara de jos:** Acasă · Caută · ➕ Creează · Meciurile mele · Profil

### 6.1 Acasă / Alegere sport (`/` după redesign, sau `/sports`)

- Titlu: „Ce joci azi?”
- Grilă de carduri (2 coloane pe telefon): pictogramă mare pe fundal tint, numele sportului, numărul de meciuri deschise în orașul curent.
- Selector de oraș sus (implicit Chișinău).
- Fără cont necesar.

### 6.2 Lista meciurilor (`/rooms`)

- Sus: selector de oraș + bară de căutare.
- Chips de filtrare: Toate · Astăzi · Mâine · Weekend · Interior · Exterior · Gratuit · Cu locuri libere.
- Meciuri grupate pe zile („Astăzi”, „Mâine”, „Sâmbătă 12 oct.”).
- Card de meci: pictogramă sport (culoare), titlu, ora (accent vizual puternic), locația, formatul (ex. 7×7), preț, locuri ocupate / total cu bară de progres, eticheta de stare (Deschis / Aproape plin / Complet).
- Stare goală: „Niciun meci aici încă. Creează primul.” cu buton.
- Skeleton la încărcare. Paginarea existentă (12 pe pagină) rămâne.

### 6.3 Detalii meci (`/rooms/{id}`)

- Antet: sportul (culoare), titlul, organizatorul.
- Grilă de informații: Când · Unde · Format · Teren (interior/exterior) · Preț · Locuri.
- Notă de la organizator (ex. „Adu o minge, vino cu 10 minute mai devreme”).
- Reguli (existente, max 10).
- Lista de participanți cu avatare + secțiunea „Interesați”.
- Buton principal fix jos: „Ocupă un loc” (sau „Ies din meci”, „Mă interesează”).
- Vizitator fără cont: butonul duce la login, apoi revine aici.
- Buton „Distribuie” (link copiabil, pentru Viber/Telegram/WhatsApp).

### 6.4 Creare meci (`/rooms/create`)

- Formular pe pași scurți: 1) Sport și oraș, 2) Când și unde, 3) Jucători și preț, 4) Reguli și notă.
- Un pas = un grup mic de câmpuri. Indicator de progres sus.
- Câmpurile existente + taxă (suma, cine o colectează) și echipament (cine aduce mingea/vestele), dacă nu sunt încă în DB.
- Previzualizare card înainte de publicare.

### 6.5 Autentificare (`/login`, `/register`)

- Ecran curat, un singur formular, mesaje de eroare clare în română și rusă.
- Pe telefon, tastatura potrivită (`type="email"`, `autocomplete`).

### 6.6 Profil și „Meciurile mele” (nou, înlocuiește `/dashboard`)

- Poză, nume, oraș, nivel per sport (cu descrierile din 1.2), poziție preferată.
- „Meciurile mele”: Viitoare / Trecute.
- Scor de fiabilitate (versiunea 2).

### 6.7 Pagina principală de prezentare (faza finală)

Un singur mesaj clar + căutarea + „Cum funcționează” în 3 pași + meciuri reale din DB (nu date inventate) + apel la acțiune.

## 7. Componente Blade (în această ordine)

> ⚠️ Două rânduri din tabelul original s-au trunchiat la copiere (variantele pentru `x-button` și `x-badge`). Valorile de mai jos sunt reconstruite; de confirmat.

| Componentă | Folosită în |
|---|---|
| `<x-button variant="primary\|secondary\|…">` | toate ecranele |
| `<x-chip :active>` | filtre |
| `<x-sport-icon :sport>` | carduri, etichete |
| `<x-sport-card :sport>` | alegere sport |
| `<x-match-card :room>` | listă |
| `<x-badge status="open\|almost\|full">` | carduri și detalii meci |
| `<x-avatar :user size>` | participanți, profil |
| `<x-field>` (input + label + eroare) | toate formularele |
| `<x-bottom-nav>` | layout mobil |
| `<x-empty-state>` | liste goale |
| `<x-skeleton>` | încărcare |
| `<x-step-form>` | creare meci |

## 8. Configurare Claude Code

- `CLAUDE.md` (rădăcina proiectului): regulile generale ale proiectului.
- `.claude/skills/sportmd-design/SKILL.md`: regulile de design, aplicate la fiecare sesiune.
- `frontend-design` (Anthropic): calitatea estetică și procesul de planificare.
- În caz de conflict, **`sportmd-design` și acest document au prioritate**. `frontend-design` împinge spre alegeri „distinctive”; aici direcția e fixată (stil Apple), iar skill-ul însuși spune că brief-ul are prioritate.
- Opționale (de verificat în documentația oficială înainte de instalare): `web-design-guidelines` (Vercel Labs, terț), Context7 MCP, Playwright MCP (capturi de ecran la diferite lățimi).

## 9. Planul de lucru

Fiecare fază se termină cu commit și cu verificare în browser (telefon real sau modul de dispozitiv din Chrome).

**Faza 0: pregătire**
- [x] Commit al stării actuale, branch `redesign-ui`
- [x] Acest fișier în `docs/DESIGN.md`
- [x] `CLAUDE.md` și skill-ul `sportmd-design`
- [x] `frontend-design` instalat (disponibil ca `frontend-design:frontend-design`)
- [x] Deciziile D1–D5 completate în 2.2
- [x] Inter (`.woff2`) și pictogramele Lucide descărcate

**Faza 1: fundația.** `tokens.css`, `base.css` (reset, tipografie, butoane, câmpuri), Inter local, `layouts/app` nou cu bară jos (telefon) și sus (laptop), aliniere `sports.color` cu 4.3, pagină `/styleguide`.

> **Făcut (Faza 1):**
> - `public/assets/css/tokens.css` și `base.css`; Inter variabil local (`public/assets/fonts/`, latin, latin-ext, chirilic); pictograme Lucide în `resources/views/components/icon/` (`<x-icon.house />`).
> - Layout nou `layouts/shell` (nu `layouts/app`, ca paginile încă neredesenate să nu se strice); devine `layouts/app` după Faza 5.
> - RO + RU: `lang/ro`, `lang/ru`, middleware `SetLocale`, ruta `/locale/{ro|ru}`. Limba implicită `ro`; limba de rezervă rămâne `en`, pentru mesajele de validare standard ale Laravel.
> - `sports.color` = `Sport::COLORS` = tokens (migrare de date + seeder + test care le compară).
> - `/styleguide` citește valorile direct din `tokens.css` și calculează contrastul.
>
> **De confirmat:**
> - Token nou propus `--fs-caption` (11/13px, Apple „Caption 2”), doar pentru etichetele barei de jos.
> - În bara de jos, „Meciurile mele” apare scurtat „Ale mele” (textul complet rămâne pentru cititoarele de ecran și pe laptop).
> - `--warning` (`#FF9500`) e identic cu baschet și `--success` cu fotbal: de ales alte valori sau de folosit doar cu pictogramă.
>
> **Contrast verificat** (text pe culoarea plină, țintă 4.5:1): fotbal 7.58, baschet 7.65, tenis 11.13, volei 4.81, handbal 4.62, padel 6.54 (toate cu text închis); alergare 4.19 și tenis de masă 4.13: doar text mare sau pictograme. `--danger` (3.55) și `--full` (3.26) nu se folosesc ca text mic.

**Faza 2: componente.** Componentele din secțiunea 7, în ordine, toate afișate pe `/styleguide`.

**Faza 3: lista de meciuri.** Redesign `/rooms`: chips, carduri grupate pe zile, skeleton, stare goală. Filtrele existente rămân funcționale.

**Faza 4: detalii și creare meci.** `/rooms/{id}` cu buton fix jos; `/rooms/create` pe pași, cu previzualizare; câmpuri noi (taxă, echipament) dacă se decide.

**Faza 5: autentificare și profil.** `/login`, `/register` redesenate; profil + „Meciurile mele” în locul `/dashboard`.

**Faza 6: pagina principală.** Redesign conform D1; secțiunile demonstrative conectate la date reale.

**Faza 7: finisare și calitate.** Lighthouse (Performance și Accessibility peste 90 pe mobil), test pe un Android de gamă medie, tastatură și contrast, traduceri RU complete, temă închisă (D3) dacă rămâne timp.

**Faza 8 (după licență, v2–v3).** Scor de fiabilitate, recenzii după meci, link de distribuire îmbunătățit, notificări, evenimente recurente, statistici și clasamente, echipe echilibrate.

## 10. Listă de verificare pentru fiecare ecran

- [ ] Arată bine la 360, 390, 768 și 1280px
- [ ] Folosește doar variabile din `tokens.css`
- [ ] Butoane și ținte tactile ≥ 44px
- [ ] Contrast text ≥ 4.5:1
- [ ] Are stări: încărcare, gol, eroare
- [ ] Focus vizibil, navigabil cu tastatura
- [ ] Textul e în `lang/` (RO și RU)
- [ ] Imaginile sunt comprimate (WebP, sub 150KB)
- [ ] Nu s-a stricat niciun test (Pest)

## 11. Riscuri și lucruri de știut

- Logica din backend nu se atinge în redesign.
- Pași mici: un ecran pe rând, cu commit între ele.
- Designul se judecă vizual: capturi de ecran la fiecare pas.
- Nu se inventează culori sau fonturi în afara `tokens.css`.
- Orice număr inventat pe pagina principală se înlocuiește cu date din DB sau se scoate.
- Licențe: Inter și Lucide sunt libere. SF Pro, SF Symbols și logo-urile Apple nu se folosesc.
- Piață cu competiție (Courtica.md, FTogether, Sportly): execuția și lansarea cu o comunitate reală contează mai mult decât designul.

## 12. Pentru teza de licență

- **Problema și cercetarea:** secțiunea 1.2.
- **Justificarea designului:** Apple HIG pentru culori, tipografie și dimensiunile țintelor tactile.
- **Sistemul de design:** tokens și componente (`/styleguide` ca anexă).
- **Accesibilitate:** rezultatele de contrast și Lighthouse.
- **Testare cu utilizatori:** 5–10 persoane din sport încearcă să găsească și să ocupe un loc; se notează unde se blochează.
- **Limite și dezvoltare viitoare:** Faza 8.
