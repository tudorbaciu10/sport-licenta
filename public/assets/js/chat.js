/*
 * Sport.md — chat (componentă Alpine: chatRoom).
 * Structura: resources/views/chat/show.blade.php
 * Aspectul:  public/assets/css/chat.css
 * Datele:    ChatController (GET/POST /chat/{rooms|support}/{id}/messages, POST …/read)
 * Timp real: Laravel Reverb prin laravel-echo + pusher-js (CDN). Dacă WebSocket-ul nu e
 *            conectat (Reverb oprit), mesajele noi se cer de la server la fiecare POLL_MS.
 */

// ---------- Setări pe care le poți schimba ----------
const POLL_MS = 10000;        // cât de des se cer mesaje noi fără WebSocket
const NEAR_BOTTOM_PX = 120;   // sub această distanță de capăt, un mesaj nou derulează lista în jos
const READ_DELAY_MS = 800;    // grupează marcarea „citit” când sosesc mai multe mesaje deodată

document.addEventListener('alpine:init', () => {
    window.Alpine.data('chatRoom', (config) => ({
        // ---------- Stare ----------
        messages: config.messages,      // [{ id, body, user_id, author, initials, avatar, support, day, time }]
        hasOlder: config.hasOlder,
        loadingOlder: false,
        text: '',
        error: '',
        offline: false,                 // true când nu există WebSocket: arătăm că mesajele vin cu întârziere
        t: config.t,
        _echo: null,
        _poll: null,
        _readTimer: null,
        _tempId: 0,

        init() {
            this.$nextTick(() => this.scrollToBottom());
            this.connect();
            this._poll = setInterval(() => { if (this.offline) this.fetchNewer(); }, POLL_MS);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) this.fetchNewer(); });
        },

        destroy() {
            clearInterval(this._poll);
            this._echo?.leave(config.channel);
        },

        // ---------- Rânduri afișate: separatoare de zi + mesaje grupate pe autor ----------
        get rows() {
            const rows = [];
            let prev = null;
            for (const m of this.messages) {
                if (!prev || prev.day !== m.day) {
                    rows.push({ type: 'day', key: 'd' + m.day, label: this.dayLabel(m.day) });
                    prev = null;
                }
                rows.push({
                    ...m,
                    type: 'msg',
                    key: 'm' + m.id,
                    mine: m.user_id === config.me,
                    first: !prev || prev.user_id !== m.user_id,
                });
                prev = m;
            }
            return rows;
        },

        dayLabel(day) {
            if (day === config.today) return this.t.today;
            if (day === config.yesterday) return this.t.yesterday;
            const [y, mo, d] = day.split('-').map(Number);
            const opts = { day: 'numeric', month: 'long' };
            if (y !== Number(config.today.slice(0, 4))) opts.year = 'numeric';
            return new Intl.DateTimeFormat(config.locale, opts).format(new Date(y, mo - 1, d));
        },

        // ---------- Timp real ----------
        connect() {
            const r = config.reverb;
            if (!r || !r.key || !window.Echo || !window.Pusher) {
                this.offline = true;
                return;
            }
            try {
                this._echo = new window.Echo.default({
                    broadcaster: 'reverb',
                    key: r.key,
                    wsHost: r.host || window.location.hostname,
                    wsPort: r.port,
                    wssPort: r.port,
                    forceTLS: r.scheme === 'https',
                    enabledTransports: ['ws', 'wss'],
                    authEndpoint: '/broadcasting/auth',
                });
                this._echo.private(config.channel).listen('.message.sent', (m) => this.receive([m]));

                const connection = this._echo.connector.pusher.connection;
                this.offline = connection.state !== 'connected';
                connection.bind('state_change', ({ current }) => {
                    const wasOffline = this.offline;
                    this.offline = current !== 'connected';
                    if (wasOffline && !this.offline) this.fetchNewer();   // recuperează ce s-a pierdut între timp
                });
            } catch (e) {
                this.offline = true;
            }
        },

        async fetchNewer() {
            const last = this.messages.filter((m) => typeof m.id === 'number').at(-1);
            try {
                const res = await this.request(config.messagesUrl + '?after=' + (last ? last.id : 0));
                this.receive(res.messages);
            } catch (e) { /* încercăm din nou la următorul interval */ }
        },

        receive(list) {
            const fresh = list.filter((m) => !this.messages.some((x) => x.id === m.id));
            if (!fresh.length) return;
            const stick = this.nearBottom() || fresh.some((m) => m.user_id === config.me);
            this.messages = [...this.messages, ...fresh].sort((a, b) => this.order(a) - this.order(b));
            if (stick) this.$nextTick(() => this.scrollToBottom());
            this.markRead();
        },

        // Mesajele în curs de trimitere (id temporar „t…”) stau la final.
        order(m) {
            return typeof m.id === 'number' ? m.id : Number.MAX_SAFE_INTEGER - 1000 + m.seq;
        },

        // ---------- Trimitere ----------
        onEnter(event) {
            if (event.shiftKey || event.isComposing) return;   // Shift+Enter = rând nou
            event.preventDefault();
            this.send();
        },

        send() {
            const body = this.text.trim();
            if (!body) return;
            if (body.length > config.max) {
                this.error = this.t.tooLong;
                return;
            }
            this.error = '';
            const seq = ++this._tempId;
            const now = new Date();
            this.messages.push({
                id: 't' + seq, seq, body, user_id: config.me, author: this.t.you, pending: true, failed: false,
                day: config.today, time: now.toTimeString().slice(0, 5),
            });
            this.text = '';
            this.$nextTick(() => { this.grow(); this.scrollToBottom(); });
            this.post('t' + seq, body);
        },

        async post(tempId, body) {
            const index = () => this.messages.findIndex((m) => m.id === tempId);
            try {
                const res = await this.request(config.sendUrl, { body });
                const i = index();
                if (i === -1) return;
                // Dacă mesajul a sosit deja prin polling, scoatem doar copia temporară.
                if (this.messages.some((m) => m.id === res.message.id)) this.messages.splice(i, 1);
                else this.messages.splice(i, 1, res.message);
            } catch (e) {
                const i = index();
                if (i !== -1) this.messages.splice(i, 1, { ...this.messages[i], failed: true });
                if (e.status === 422 || e.status === 429) this.error = e.message || this.t.tooLong;
            }
        },

        retry(key) {
            const m = this.messages.find((x) => 'm' + x.id === key);
            if (!m) return;
            m.failed = false;
            this.post(m.id, m.body);
        },

        // ---------- Istoric ----------
        async loadOlder() {
            const first = this.messages.find((m) => typeof m.id === 'number');
            if (!first || this.loadingOlder) return;
            this.loadingOlder = true;
            const log = this.$refs.log;
            const fromBottom = log.scrollHeight - log.scrollTop;
            try {
                const res = await this.request(config.messagesUrl + '?before=' + first.id);
                this.hasOlder = res.messages.length >= 50;
                this.messages = [...res.messages, ...this.messages];
                this.$nextTick(() => { log.scrollTop = log.scrollHeight - fromBottom; });   // rămâi la același mesaj
            } catch (e) { /* butonul rămâne, se poate apăsa din nou */ }
            this.loadingOlder = false;
        },

        // ---------- Citit ----------
        markRead() {
            if (document.hidden) return;
            clearTimeout(this._readTimer);
            this._readTimer = setTimeout(() => {
                const last = this.messages.filter((m) => typeof m.id === 'number').at(-1);
                if (last) this.request(config.readUrl, { id: last.id }).catch(() => {});
            }, READ_DELAY_MS);
        },

        // ---------- Ajutoare ----------
        onScroll() {
            if (this.$refs.log.scrollTop < 40 && this.hasOlder) this.loadOlder();
        },

        nearBottom() {
            const log = this.$refs.log;
            return log.scrollHeight - log.scrollTop - log.clientHeight < NEAR_BOTTOM_PX;
        },

        scrollToBottom() {
            const log = this.$refs.log;
            log.scrollTop = log.scrollHeight;
        },

        // Câmpul crește odată cu textul, până la înălțimea maximă din CSS.
        grow() {
            const el = this.$refs.input;
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 2 + 'px';
        },

        async request(url, body = null) {
            const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
            if (body) {
                headers['Content-Type'] = 'application/json';
                headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
                const socket = this._echo?.socketId();
                if (socket) headers['X-Socket-ID'] = socket;   // cine trimite nu-și primește propriul mesaj înapoi
            }
            const res = await fetch(url, { method: body ? 'POST' : 'GET', headers, body: body ? JSON.stringify(body) : null, credentials: 'same-origin' });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const err = new Error(data.errors?.body?.[0] || data.message || '');
                err.status = res.status;
                throw err;
            }
            return data;
        },
    }));
});
