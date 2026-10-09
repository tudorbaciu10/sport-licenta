/*
 * Sport.md — formularul „Creează un meci” (componentă Alpine: createMatch).
 * Structura: resources/views/rooms/create.blade.php (+ rooms/partials/when-picker, day-matches, preview)
 * Aspectul:  public/assets/css/create.css
 * Conține:   calendarul și ora (în locul câmpului nativ datetime-local), meciurile din ziua aleasă,
 *            harta (Leaflet + OpenStreetMap, încărcată doar când o deschizi) și verificările pasului 2.
 * Pașii și mesajele de eroare: componenta <x-step-form> (stepForm) — aici doar adăugăm checkStep().
 */

// ---------- Setări pe care le poți schimba ----------
const WEEK_STARTS_ON = 1;                    // 1 = luni, 0 = duminică
const SLOT_MINUTES = 30;                     // pasul orelor propuse
const DAY_PARTS = {                          // intervalele de ore propuse, pe părți ale zilei
    morning: ['07:00', '11:30'],
    afternoon: ['12:00', '16:30'],
    evening: ['17:00', '22:30'],
};
const LEAFLET = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/';
const TILES = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const GEOCODER = 'https://nominatim.openstreetmap.org/';   // căutarea adreselor (OpenStreetMap)

const pad = (n) => String(n).padStart(2, '0');
const iso = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
const parse = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };

// Leaflet se încarcă o singură dată, la prima deschidere a hărții (pagina rămâne ușoară pe telefon).
let leafletPromise = null;
function loadLeaflet() {
    if (window.L) return Promise.resolve(window.L);
    leafletPromise ??= new Promise((resolve, reject) => {
        const css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = LEAFLET + 'leaflet.min.css';
        document.head.append(css);
        const js = document.createElement('script');
        js.src = LEAFLET + 'leaflet.min.js';
        js.onload = () => resolve(window.L);
        js.onerror = () => { leafletPromise = null; reject(new Error('leaflet')); };
        document.head.append(js);
    });
    return leafletPromise;
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('createMatch', (init) => ({
        // ---------- Datele formularului ----------
        sport: init.sport,
        city: init.city,
        title: init.title,
        date: init.when ? init.when.slice(0, 10) : '',
        time: init.when ? init.when.slice(11, 16) : '',
        location: init.location,
        lat: init.lat,
        lng: init.lng,
        venue: init.venue,
        players: init.players,
        price: init.price,
        collector: init.collector,
        rules: init.rules,
        sports: init.sports,
        cities: init.cities,
        t: init.t,
        current: 1,                       // pasul deschis (vine din stepForm prin evenimentul step-changed)

        // ---------- Calendar ----------
        viewY: 0,
        viewM: 0,
        days: {},                         // { 'YYYY-MM-DD': { count, sports } } pentru orașul ales
        part: 'evening',
        dayRooms: [],
        dayLoading: false,

        // ---------- Hartă ----------
        mapOpen: false,
        mapLoading: false,
        mapError: false,
        mapQuery: '',
        mapResults: [],
        mapSearching: false,
        mapNone: false,
        geoError: false,
        _map: null,
        _pin: null,

        init() {
            const start = this.date ? parse(this.date) : parse(init.today);
            this.viewY = start.getFullYear();
            this.viewM = start.getMonth();
            if (this.time) this.part = this.partOf(this.time);
            this.loadMonth();
            this.loadDay();
            this.$watch('city', () => {
                this.loadMonth();
                this.loadDay();
                if (this._map && !this.hasPin) this._map.setView(this.center(), 13);
            });
            this.$watch('current', (s) => { if (s === 2 && this._map) this.$nextTick(() => this._map.invalidateSize()); });
            if (this.hasPin) this.mapOpen = false;   // harta se deschide la cerere
        },

        get when() { return this.date && this.time ? `${this.date}T${this.time}` : ''; },
        get citySlug() { return this.cities[this.city]?.slug || ''; },
        get hasPin() { return this.lat !== '' && this.lat !== null && this.lng !== '' && this.lng !== null; },

        // ---------- Calendar: grila lunii ----------
        get monthLabel() {
            const s = new Intl.DateTimeFormat(init.locale, { month: 'long', year: 'numeric' }).format(new Date(this.viewY, this.viewM, 1));
            return s.charAt(0).toUpperCase() + s.slice(1);
        },
        get weekdays() {
            const f = new Intl.DateTimeFormat(init.locale, { weekday: 'short' });
            const l = new Intl.DateTimeFormat(init.locale, { weekday: 'long' });
            return [...Array(7)].map((_, i) => {
                const d = new Date(2024, 0, 7 + ((i + WEEK_STARTS_ON) % 7));   // 7 ian. 2024 = duminică
                return { short: f.format(d).replace('.', '').slice(0, 2), long: l.format(d) };
            });
        },
        get cells() {
            const first = new Date(this.viewY, this.viewM, 1);
            const offset = (first.getDay() - WEEK_STARTS_ON + 7) % 7;
            const count = Math.ceil((offset + new Date(this.viewY, this.viewM + 1, 0).getDate()) / 7) * 7;
            const focus = this.focusDate;
            return [...Array(count)].map((_, i) => {
                const d = new Date(this.viewY, this.viewM, 1 - offset + i);
                const date = iso(d);
                const info = this.days[date];
                return {
                    date,
                    day: d.getDate(),
                    out: d.getMonth() !== this.viewM,
                    disabled: date < init.today || date > init.maxDate,
                    today: date === init.today,
                    selected: date === this.date,
                    count: info?.count || 0,
                    sports: (info?.sports || []).slice(0, 3),
                    focus: date === focus,
                    label: new Intl.DateTimeFormat(init.locale, { weekday: 'long', day: 'numeric', month: 'long' }).format(d),
                };
            });
        },
        // Ziua care primește Tab (una singură în grilă): cea aleasă, azi sau prima zi a lunii.
        get focusDate() {
            const inView = (s) => s && parse(s).getMonth() === this.viewM && parse(s).getFullYear() === this.viewY;
            if (inView(this.date)) return this.date;
            if (inView(init.today)) return init.today;
            return iso(new Date(this.viewY, this.viewM, 1));
        },
        get canPrev() { return this.viewY * 12 + this.viewM > Number(init.today.slice(0, 4)) * 12 + Number(init.today.slice(5, 7)) - 1; },
        get canNext() { return this.viewY * 12 + this.viewM < Number(init.maxDate.slice(0, 4)) * 12 + Number(init.maxDate.slice(5, 7)) - 1; },
        shift(months) {
            const d = new Date(this.viewY, this.viewM + months, 1);
            this.viewY = d.getFullYear();
            this.viewM = d.getMonth();
            this.loadMonth();
        },
        dotColor(slug) {
            const s = Object.values(this.sports).find((x) => x.slug === slug);
            return s ? `var(${s.var})` : 'var(--text-2)';
        },

        pickDate(date) {
            if (date < init.today || date > init.maxDate) return;
            this.date = date;
            if (this.time && this.slotDisabled(this.time)) this.time = '';
            const d = parse(date);
            if (d.getMonth() !== this.viewM) { this.viewY = d.getFullYear(); this.viewM = d.getMonth(); this.loadMonth(); }
            this.loadDay();
            this.touch();
        },

        // Tastatura în grilă: săgeți (zi/săptămână), Home/End (săptămâna), PageUp/PageDown (luna).
        onKey(e, date) {
            const moves = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
            const d = parse(date);
            if (e.key in moves) d.setDate(d.getDate() + moves[e.key]);
            else if (e.key === 'Home') d.setDate(d.getDate() - ((d.getDay() - WEEK_STARTS_ON + 7) % 7));
            else if (e.key === 'End') d.setDate(d.getDate() + 6 - ((d.getDay() - WEEK_STARTS_ON + 7) % 7));
            else if (e.key === 'PageUp') d.setMonth(d.getMonth() - 1);
            else if (e.key === 'PageDown') d.setMonth(d.getMonth() + 1);
            else return;
            e.preventDefault();
            let target = iso(d);
            if (target < init.today) target = init.today;
            if (target > init.maxDate) target = init.maxDate;
            const t = parse(target);
            if (t.getMonth() !== this.viewM || t.getFullYear() !== this.viewY) { this.viewY = t.getFullYear(); this.viewM = t.getMonth(); this.loadMonth(); }
            const grid = e.target.closest('.mcal');
            this.$nextTick(() => grid.querySelector(`[data-date="${target}"]`)?.focus());
        },

        async loadMonth() {
            const key = `${this.viewY}-${pad(this.viewM + 1)}`;
            if (!this.citySlug) { this.days = {}; return; }
            try {
                const res = await fetch(`${init.daysUrl}?month=${key}&city=${this.citySlug}`, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                if (data.month !== key) return;
                this.days = Object.fromEntries(data.days.map((d) => [d.date, d]));
            } catch (e) { this.days = {}; }   // fără puncte: calendarul merge și așa
        },

        // ---------- Ora ----------
        get slots() {
            const [from, to] = DAY_PARTS[this.part];
            const out = [];
            for (let m = this.minutes(from); m <= this.minutes(to); m += SLOT_MINUTES) out.push(`${pad(Math.floor(m / 60))}:${pad(m % 60)}`);
            return out;
        },
        minutes(t) { const [h, m] = t.split(':').map(Number); return h * 60 + m; },
        partOf(t) {
            const m = this.minutes(t);
            if (m < this.minutes('12:00')) return 'morning';
            return m < this.minutes('17:00') ? 'afternoon' : 'evening';
        },
        slotDisabled(t) { return this.date === init.today && t <= init.now; },
        pickTime(t) {
            if (this.slotDisabled(t)) return;
            this.time = t;
            this.touch();
        },
        // „Altă oră”: ora și minutele din cele două liste (lipsă → :00 / ora 18).
        setOther(h, m) {
            const t = `${h ?? (this.time.slice(0, 2) || '18')}:${m ?? (this.time.slice(3, 5) || '00')}`;
            if (this.slotDisabled(t)) { this.time = ''; this.touch(); return; }
            this.time = t;
            this.part = this.partOf(t);
            this.touch();
        },
        // Câmpul de dată e verificat din nou după alegere: eroarea dispare.
        touch() {
            this.$nextTick(() => {
                const input = this.$root.querySelector('input[name="match_date_time"]');
                input?.dispatchEvent(new Event('change', { bubbles: true }));
            });
        },
        get whenLabel() {
            if (!this.date) return '';
            const s = new Intl.DateTimeFormat(init.locale, { weekday: 'long', day: 'numeric', month: 'long' }).format(parse(this.date));
            return s.charAt(0).toUpperCase() + s.slice(1) + (this.time ? ` · ${this.time}` : '');
        },

        // ---------- Meciurile din ziua aleasă ----------
        async loadDay() {
            if (!this.date || !this.citySlug) { this.dayRooms = []; return; }
            const date = this.date;
            this.dayLoading = true;
            try {
                const res = await fetch(`${init.dayUrl}?date=${date}&city=${this.citySlug}`, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                if (data.date === this.date) this.dayRooms = data.rooms;
            } catch (e) { this.dayRooms = []; }
            this.dayLoading = false;
        },
        get daySportSlug() { return this.sports[this.sport]?.slug; },

        // ---------- Verificările pasului 2 (mesajele vin din lang/{ro,ru}/validation.php) ----------
        checkStep(step) {
            if (step !== 2) return [];
            if (!this.date || !this.time) return [{ name: 'match_date_time', message: this.t.whenRequired }];
            if (this.date === init.today && this.time <= init.now) return [{ name: 'match_date_time', message: this.t.whenPast }];
            return [];
        },

        // ---------- Harta ----------
        center() {
            if (this.hasPin) return [Number(this.lat), Number(this.lng)];
            return this.cities[this.city]?.center || init.defaultCenter;
        },
        async toggleMap() {
            this.mapOpen = !this.mapOpen;
            if (!this.mapOpen) return;
            if (this._map) { this.$nextTick(() => this._map.invalidateSize()); return; }
            this.mapLoading = true;
            this.mapError = false;
            try {
                const L = await loadLeaflet();
                await this.$nextTick();
                this._map = L.map(this.$refs.map, { scrollWheelZoom: false }).setView(this.center(), this.hasPin ? 16 : 13);
                L.tileLayer(TILES, { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' }).addTo(this._map);
                this._map.on('click', (e) => this.setPoint(e.latlng.lat, e.latlng.lng, true));
                if (this.hasPin) this.drawPin();
                setTimeout(() => this._map.invalidateSize(), 50);
            } catch (e) {
                this.mapError = true;
            }
            this.mapLoading = false;
        },
        drawPin() {
            const L = window.L;
            const pos = [Number(this.lat), Number(this.lng)];
            if (this._pin) { this._pin.setLatLng(pos); return; }
            const icon = L.divIcon({ className: 'map-pin', html: '<span></span>', iconSize: [32, 40], iconAnchor: [16, 38] });
            this._pin = L.marker(pos, { icon, draggable: true, keyboard: true, title: this.t.mapPin }).addTo(this._map);
            this._pin.on('dragend', () => { const p = this._pin.getLatLng(); this.setPoint(p.lat, p.lng, true); });
        },
        setPoint(lat, lng, describe = false) {
            this.lat = Number(lat).toFixed(6);
            this.lng = Number(lng).toFixed(6);
            this.drawPin();
            if (describe && !this.location.trim()) this.reverse();
        },
        clearPin() {
            this.lat = '';
            this.lng = '';
            if (this._pin) { this._pin.remove(); this._pin = null; }
        },
        async search() {
            const q = this.mapQuery.trim();
            if (q.length < 3) return;
            this.mapSearching = true;
            this.mapNone = false;
            try {
                const c = this.center();
                // Rezultatele din jurul orașului ales vin primele (viewbox), dar nu sunt excluse cele din alte locuri.
                const box = [c[1] - 0.3, c[0] + 0.2, c[1] + 0.3, c[0] - 0.2].join(',');
                const url = `${GEOCODER}search?format=jsonv2&limit=5&countrycodes=md,ro&accept-language=${init.locale}&viewbox=${box}&q=${encodeURIComponent(q)}`;
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                this.mapResults = (await res.json()).map((r) => ({ id: r.place_id, name: r.name || r.display_name.split(',')[0], address: r.display_name, lat: r.lat, lng: r.lon }));
                this.mapNone = this.mapResults.length === 0;
            } catch (e) {
                this.mapResults = [];
                this.mapNone = true;
            }
            this.mapSearching = false;
        },
        pickResult(r) {
            this.setPoint(r.lat, r.lng);
            this._map?.setView([Number(r.lat), Number(r.lng)], 17);
            if (!this.location.trim()) this.location = r.name.slice(0, 160);
            this.mapResults = [];
        },
        async reverse() {
            try {
                const res = await fetch(`${GEOCODER}reverse?format=jsonv2&zoom=18&accept-language=${init.locale}&lat=${this.lat}&lon=${this.lng}`, { headers: { Accept: 'application/json' } });
                const r = await res.json();
                const a = r.address || {};
                const name = r.name || [a.road, a.house_number].filter(Boolean).join(' ') || a.suburb || '';
                if (name && !this.location.trim()) this.location = name.slice(0, 160);
            } catch (e) { /* utilizatorul poate scrie singur locația */ }
        },
        locate() {
            this.geoError = false;
            if (!navigator.geolocation) { this.geoError = true; return; }
            navigator.geolocation.getCurrentPosition(
                (p) => { this.setPoint(p.coords.latitude, p.coords.longitude, true); this._map?.setView([p.coords.latitude, p.coords.longitude], 16); },
                () => { this.geoError = true; },
                { enableHighAccuracy: true, timeout: 10000 },
            );
        },

        // ---------- Previzualizare și pași ----------
        previewTime() { return this.time || '--:--'; },
        subline() {
            const parts = [];
            if (this.date) parts.push(new Intl.DateTimeFormat(init.locale, { weekday: 'short', day: 'numeric', month: 'short' }).format(parse(this.date)));
            if (this.sports[this.sport]) parts.push(this.sports[this.sport].name);
            parts.push(this.price > 0 ? this.t.lei.replace('__P__', this.price) : this.t.free);
            return parts.join(' · ');
        },
        get done() {
            return [
                Boolean(this.sport && this.city && this.title.trim()),
                Boolean(this.date && this.time && this.location.trim()),
                this.players >= 2 && (!(this.price > 0) || Boolean(this.collector)),
                Boolean(this.sport && this.when),
            ];
        },
        goTo(n) { window.dispatchEvent(new CustomEvent('goto-step', { detail: n })); },
    }));
});
