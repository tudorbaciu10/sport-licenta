/*
 * Sport.md — banda săptămânii (componentă Alpine: weekStrip), ca „Date Strip” din Figma.
 * Structura: resources/views/components/week-strip.blade.php · Aspectul: public/assets/css/components.css (.week-strip)
 * Datele: GET /calendar/days?month=YYYY-MM (câte o lună; o săptămână peste sfârșitul lunii cere două).
 */
const WEEK_LOOKAHEAD_MONTHS = 6;   // ca în calendar.js: până la +6 luni de la luna curentă
const WEEK_MAX_DOTS = 3;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('weekStrip', (config) => ({
        today: config.today,
        selected: config.selected,
        mode: config.mode,
        vars: config.sportVars,
        city: config.city,
        start: '',            // prima zi afișată, 'YYYY-MM-DD' (azi + 7·k, ca în Figma: banda începe cu ziua de azi)
        info: {},             // { 'YYYY-MM-DD': { count, sports, mine } }
        _months: {},          // lunile deja cerute: { 'YYYY-MM': Promise }
        _url: '',

        init() {
            this._url = this.$el.dataset.url;   // $el later is the clicked button, not this root
            this.start = this.windowOf(this.selected || this.today);
            this.load();
        },

        // ---------- Date ----------
        parse(iso) { const [y, m, d] = iso.split('-').map(Number); return new Date(y, m - 1, d); },
        iso(d) { const p = (n) => String(n).padStart(2, '0'); return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`; },
        addDays(iso, n) { const d = this.parse(iso); d.setDate(d.getDate() + n); return this.iso(d); },
        // The 7-day window (counted from today) that contains iso; days before today fall in the first one.
        windowOf(iso) {
            const diff = Math.round((this.parse(iso) - this.parse(this.today)) / 864e5);
            return this.addDays(this.today, 7 * Math.max(0, Math.floor(diff / 7)));
        },
        get end() { return this.addDays(this.start, 6); },
        get last() { const d = this.parse(this.today); d.setMonth(d.getMonth() + WEEK_LOOKAHEAD_MONTHS + 1, 0); return this.iso(d); },
        get canPrev() { return this.start > this.today; },
        get canNext() { return this.addDays(this.start, 7) <= this.last; },

        get days() {
            const wd = new Intl.DateTimeFormat(config.locale, { weekday: 'short' });
            const full = new Intl.DateTimeFormat(config.locale, { weekday: 'long', day: 'numeric', month: 'long' });
            return Array.from({ length: 7 }, (_, i) => {
                const iso = this.addDays(this.start, i);
                const d = this.parse(iso);
                const info = this.info[iso] || {};
                const count = info.count || 0;
                return {
                    iso, num: d.getDate(), wd: wd.format(d).replace('.', ''), past: iso < this.today || iso > this.last,
                    count, sports: (info.sports || []).slice(0, WEEK_MAX_DOTS),
                    label: full.format(d) + (count ? ', ' + this.countText(count) : ''),
                };
            });
        },
        get rangeLabel() {
            const f = new Intl.DateTimeFormat(config.locale, { day: 'numeric', month: 'short' });
            return `${f.format(this.parse(this.start))} – ${f.format(this.parse(this.end))}`;
        },
        countText(n) {
            const form = new Intl.PluralRules(config.locale).select(n);
            return (config.t.count[form] ?? config.t.count.other).replace(':n', n);
        },

        // ---------- Acțiuni ----------
        shift(weeks) {
            const next = this.addDays(this.start, 7 * weeks);
            if (weeks < 0 && !this.canPrev) return;
            if (weeks > 0 && !this.canNext) return;
            this.start = next;
            this.load();
        },
        pick(iso) {
            this.selected = iso;
            this.$dispatch('week-day', { date: iso });
        },
        follow(iso) {
            if (!iso) return;
            this.selected = iso;
            if (iso < this.start || iso > this.end) { this.start = this.windowOf(iso); this.load(); }
        },
        setCity(slug) {
            if (slug === this.city) return;
            this.city = slug;
            this.info = {};
            this._months = {};
            this.load();
        },

        // ---------- Datele ----------
        load() {
            const months = [...new Set([this.start.slice(0, 7), this.end.slice(0, 7)])];
            months.forEach((m) => {
                if (this._months[m]) return;
                const params = new URLSearchParams({ month: m });
                if (this.city) params.set('city', this.city);
                if (config.mine) params.set('mine', '1');
                this._months[m] = fetch(`${this._url}?${params}`, { headers: { Accept: 'application/json' } })
                    .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
                    .then((data) => {
                        const add = {};
                        data.days.forEach((d) => { add[d.date] = d; });
                        this.info = { ...this.info, ...add };
                    })
                    .catch(() => { delete this._months[m]; });   // fără puncte; se reîncearcă la următoarea navigare
            });
        },
    }));
});
