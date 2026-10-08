/*
 * Sport.md — calendar lunar de meciuri (componentă Alpine: sportCalendar).
 * Structura: resources/views/components/calendar.blade.php
 * Aspectul:  public/assets/css/calendar.css
 * Datele:    GET /calendar/days și GET /calendar/day (CalendarController)
 */

// ---------- Setări pe care le poți schimba ----------
const WEEK_STARTS_ON = 1;    // prima zi a săptămânii: 1 = luni, 0 = duminică
const MAX_DOTS = 3;          // câte puncte (sporturi) apar sub o zi; dacă sunt mai multe, ultimul devine „+”
const LOOKAHEAD_MONTHS = 6;  // câte luni înainte se poate naviga față de luna curentă
const ANIMATION_MS = 180;    // durata tranziției listei zilei (0 dacă utilizatorul cere mișcare redusă)

document.addEventListener('alpine:init', () => {
    window.Alpine.data('sportCalendar', (config) => ({
        // ---------- Stare ----------
        today: config.today,              // „azi” vine de la server (fusul orar al aplicației)
        year: 0,
        month: 0,                         // 0 = ianuarie
        days: {},                         // { 'YYYY-MM-DD': { count, sports: [slug], mine } }
        loading: true,
        firstLoad: true,                  // skeleton-ul apare doar la prima încărcare; apoi grila rămâne (pentru focus)
        error: false,
        selected: null,                   // ziua aleasă, 'YYYY-MM-DD'
        focusDate: null,                  // ziua care primește focusul (tabindex 0)
        rooms: [],
        dayLoading: false,
        dayError: false,
        actionError: '',
        busy: null,                       // id-ul meciului pentru care rulează o acțiune
        onlyMine: !!config.mine,
        reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        _monthRequest: null,
        _dayRequest: null,
        _root: null,

        init() {
            // Reținem rădăcina: magicul $root se calculează din elementul care a declanșat evenimentul,
            // iar butonul zilei vechi dispare când se schimbă luna.
            this._root = this.$el;
            const [y, m] = this.today.split('-').map(Number);
            this.year = y;
            this.month = m - 1;
            this.focusDate = this.today;
            this.loadMonth();
        },

        // ---------- Date și texte ----------
        iso(date) {
            const pad = (n) => String(n).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        },
        parse(iso) {
            const [y, m, d] = iso.split('-').map(Number);
            return new Date(y, m - 1, d);
        },
        addDays(iso, n) {
            const d = this.parse(iso);
            d.setDate(d.getDate() + n);
            return this.iso(d);
        },
        get monthLabel() {
            const label = new Intl.DateTimeFormat(config.locale, { month: 'long', year: 'numeric' }).format(new Date(this.year, this.month, 1));
            return label.charAt(0).toUpperCase() + label.slice(1);
        },
        get weekdays() {
            // 1 ianuarie 2024 a fost luni: de acolo generăm numele zilelor în limba paginii.
            const short = new Intl.DateTimeFormat(config.locale, { weekday: 'short' });
            const long = new Intl.DateTimeFormat(config.locale, { weekday: 'long' });
            return Array.from({ length: 7 }, (_, i) => {
                const d = new Date(2024, 0, 1 + ((i + WEEK_STARTS_ON - 1 + 7) % 7));
                return { short: short.format(d).replace('.', ''), long: long.format(d) };
            });
        },
        fullDate(iso) {
            return new Intl.DateTimeFormat(config.locale, { weekday: 'long', day: 'numeric', month: 'long' }).format(this.parse(iso));
        },
        countText(n) {
            const form = new Intl.PluralRules(config.locale).select(n);
            return (config.t.count[form] ?? config.t.count.other).replace(':n', n);
        },

        // ---------- Grila lunii: săptămâni de câte 7 celule (null = zi din altă lună) ----------
        get weeks() {
            const first = new Date(this.year, this.month, 1);
            const lead = (first.getDay() - WEEK_STARTS_ON + 7) % 7;
            const total = new Date(this.year, this.month + 1, 0).getDate();
            const cells = Array(lead).fill(null);
            for (let d = 1; d <= total; d++) {
                const iso = this.iso(new Date(this.year, this.month, d));
                cells.push({ iso, day: d, past: iso < this.today, info: this.days[iso] ?? null });
            }
            while (cells.length % 7) cells.push(null);
            return Array.from({ length: cells.length / 7 }, (_, i) => cells.slice(i * 7, i * 7 + 7));
        },
        dots(info) {
            // Un punct pe sport, maxim MAX_DOTS; dacă sunt mai multe, ultimul devine „+”.
            if (!info) return [];
            const list = info.sports.map((slug) => ({ slug, color: `var(${config.sportVars[slug] ?? '--text-2'})` }));
            return list.length > MAX_DOTS ? [...list.slice(0, MAX_DOTS - 1), { slug: '+', plus: true }] : list;
        },
        dayLabel(cell) {
            // Exemplu: „vineri, 16 octombrie, 3 meciuri: Fotbal, Tenis”
            let label = this.fullDate(cell.iso);
            if (cell.info) {
                label += `, ${this.countText(cell.info.count)}: ${cell.info.sports.map((s) => config.sportNames[s] ?? s).join(', ')}`;
                if (cell.info.mine) label += `, ${config.t.mine_day}`;
            } else {
                label += `, ${config.t.no_matches}`;
            }
            if (cell.past) label += `, ${config.t.past}`;
            return label;
        },

        // ---------- Navigare între luni (nu înainte de luna curentă, maxim LOOKAHEAD_MONTHS) ----------
        monthIndex(y, m) { return y * 12 + m; },
        get todayIndex() { const [y, m] = this.today.split('-').map(Number); return this.monthIndex(y, m - 1); },
        get canPrev() { return this.monthIndex(this.year, this.month) > this.todayIndex; },
        get canNext() { return this.monthIndex(this.year, this.month) < this.todayIndex + LOOKAHEAD_MONTHS; },
        get isCurrentMonth() { return this.monthIndex(this.year, this.month) === this.todayIndex; },
        shiftMonth(delta) {
            const target = this.monthIndex(this.year, this.month) + delta;
            if (target < this.todayIndex || target > this.todayIndex + LOOKAHEAD_MONTHS) return false;
            this.year = Math.floor(target / 12);
            this.month = target % 12;
            this.days = {};                       // punctele lunii vechi nu rămân pe luna nouă
            this.loadMonth();
            return true;
        },
        goToday() {
            const [y, m] = this.today.split('-').map(Number);
            if (!this.isCurrentMonth) { this.year = y; this.month = m - 1; this.loadMonth(); }
            this.focusDate = this.today;
            this.select(this.today);
            this.$nextTick(() => this.focusDay(this.today));
        },

        // ---------- Încărcarea datelor ----------
        query(extra) {
            const params = new URLSearchParams(extra);
            // „Doar ale mele” arată meciurile tale din orice oraș; altfel orașul paginii.
            if (config.city && !this.onlyMine) params.set('city', config.city);
            if (this.onlyMine) params.set('mine', '1');
            return params.toString();
        },
        async fetchJson(url, slot) {
            this[slot]?.abort();
            this[slot] = new AbortController();
            const res = await fetch(url, { headers: { Accept: 'application/json' }, signal: this[slot].signal });
            if (!res.ok) throw new Error(res.status);
            return res.json();
        },
        async loadMonth() {
            const month = `${this.year}-${String(this.month + 1).padStart(2, '0')}`;
            this.loading = true;
            this.error = false;
            try {
                const data = await this.fetchJson(`${config.daysUrl}?${this.query({ month })}`, '_monthRequest');
                this.days = Object.fromEntries(data.days.map((d) => [d.date, d]));
                this.loading = false;
                this.firstLoad = false;
            } catch (e) {
                if (e.name === 'AbortError') return;
                this.error = true;
                this.loading = false;
            }
            // Focusul rămâne într-o zi vizibilă din luna afișată.
            if (!this.focusDate || !this.focusDate.startsWith(month)) {
                this.focusDate = this.isCurrentMonth ? this.today : `${month}-01`;
            }
        },
        async loadDay() {
            if (!this.selected) return;
            this.dayLoading = true;
            this.dayError = false;
            this.actionError = '';
            try {
                const data = await this.fetchJson(`${config.dayUrl}?${this.query({ date: this.selected })}`, '_dayRequest');
                this.rooms = data.rooms;
                this.dayLoading = false;
            } catch (e) {
                if (e.name === 'AbortError') return;
                this.dayError = true;
                this.dayLoading = false;
            }
        },
        setOnlyMine(value) {
            if (this.onlyMine === value) return;
            this.onlyMine = value;
            this.loadMonth();
            this.loadDay();
        },

        // ---------- Selectarea unei zile ----------
        select(iso) {
            if (iso < this.today) return;           // zilele trecute nu se pot alege
            this.selected = iso;
            this.focusDate = iso;
            this.loadDay();
        },
        get createUrl() { return this.selected ? `${config.createUrl}?date=${this.selected}` : config.createUrl; },
        get seeAllUrl() {
            const params = new URLSearchParams({ date: this.selected ?? '' });
            if (config.city) params.set('city', config.city);
            return `${config.roomsUrl}?${params}`;
        },

        // ---------- Tastatura: săgeți, Home/End, PageUp/PageDown, Enter/Spațiu ----------
        onKey(event, iso) {
            const date = this.parse(iso);
            const weekday = (date.getDay() - WEEK_STARTS_ON + 7) % 7;
            const moves = {
                ArrowLeft: () => this.addDays(iso, -1),
                ArrowRight: () => this.addDays(iso, 1),
                ArrowUp: () => this.addDays(iso, -7),
                ArrowDown: () => this.addDays(iso, 7),
                Home: () => this.addDays(iso, -weekday),
                End: () => this.addDays(iso, 6 - weekday),
                PageUp: () => this.sameDayOtherMonth(date, -1),
                PageDown: () => this.sameDayOtherMonth(date, 1),
            };
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.select(iso);
                return;
            }
            if (!moves[event.key]) return;
            event.preventDefault();
            this.moveFocus(moves[event.key]());
        },
        sameDayOtherMonth(date, delta) {
            const target = new Date(date.getFullYear(), date.getMonth() + delta, 1);
            const last = new Date(target.getFullYear(), target.getMonth() + 1, 0).getDate();
            target.setDate(Math.min(date.getDate(), last));
            return this.iso(target);
        },
        moveFocus(iso) {
            const [y, m] = iso.split('-').map(Number);
            const delta = this.monthIndex(y, m - 1) - this.monthIndex(this.year, this.month);
            if (delta !== 0 && !this.shiftMonth(delta)) return;   // în afara lunilor permise: rămâne pe loc
            this.focusDate = iso;
            this.$nextTick(() => this.focusDay(iso));
        },
        focusDay(iso, tries = 10) {
            // După schimbarea lunii, butoanele zilelor apar abia după randare: reîncercăm câteva cadre.
            const el = this._root.querySelector(`[data-date="${iso}"]`);
            if (el) el.focus();
            else if (tries > 0) setTimeout(() => this.focusDay(iso, tries - 1), 30);
        },

        // ---------- Acțiuni rapide (doar în calendarul personal) ----------
        async act(room, kind) {
            const url = room.actions?.[kind];
            if (!url || this.busy) return;
            this.busy = room.id;
            this.actionError = '';
            const body = new FormData();
            if (kind === 'leave') body.append('_method', 'DELETE');
            try {
                // Rutele existente răspund cu redirect; „manual” = îl tratăm ca reușită fără să încărcăm pagina.
                const res = await fetch(url, {
                    method: 'POST',
                    body,
                    redirect: 'manual',
                    headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.type !== 'opaqueredirect' && !res.ok) {
                    const data = await res.json().catch(() => ({}));
                    this.actionError = data.errors?.room?.[0] ?? config.t.action_error;
                }
            } catch (e) {
                this.actionError = config.t.action_error;
            }
            this.busy = null;
            this.loadDay();
            this.loadMonth();
        },

        // Durata tranziției, 0 când utilizatorul cere mișcare redusă.
        get transitionMs() { return this.reduceMotion ? 0 : ANIMATION_MS; },
    }));
});
