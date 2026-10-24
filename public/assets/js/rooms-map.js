/*
 * Sport.md — harta meciurilor pe telefon (componentă Alpine: roomsMap), /rooms?view=map.
 * Structura: resources/views/rooms/index.blade.php · Aspectul: public/assets/css/map.css
 * Datele: GET /rooms/map (RoomMapController) — meciurile deschise cu locuri libere, din toată țara.
 * Leaflet și gruparea pinilor se încarcă doar aici, la deschiderea hărții.
 */
const MAP_LEAFLET = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/';
const MAP_CLUSTER = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/';
const MAP_TILES = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const CITY_ZOOM = 12;        // zoom la deschidere (un oraș întreg)
const NEAR_ZOOM = 13;        // zoom după „Lângă mine”
const CLUSTER_RADIUS = 50;   // px: pinii mai apropiați de atât se strâng într-un cerc cu număr

function mapScript(src) {
    return new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = src; s.onload = resolve; s.onerror = () => reject(new Error(src));
        document.head.append(s);
    });
}
function mapStyle(href) {
    const l = document.createElement('link');
    l.rel = 'stylesheet'; l.href = href;
    document.head.append(l);
}
let mapLibs = null;
function loadMapLibs() {
    mapLibs ??= (async () => {
        if (!window.L) { mapStyle(MAP_LEAFLET + 'leaflet.min.css'); await mapScript(MAP_LEAFLET + 'leaflet.min.js'); }
        if (!window.L.markerClusterGroup) { mapStyle(MAP_CLUSTER + 'MarkerCluster.min.css'); await mapScript(MAP_CLUSTER + 'leaflet.markercluster.min.js'); }
        return window.L;
    })().catch((e) => { mapLibs = null; throw e; });
    return mapLibs;
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('roomsMap', (config) => ({
        state: 'loading',     // loading | ready | empty | error
        truncated: false,
        peek: null,           // meciul al cărui card e deschis jos
        geoError: false,
        locating: false,
        _map: null,
        _cluster: null,
        _me: null,
        _active: null,

        async init() {
            try {
                const L = await loadMapLibs();
                this._map = L.map(this.$refs.canvas, { zoomControl: false, attributionControl: true })
                    .setView(window.sportDistance?.position ? [window.sportDistance.position.lat, window.sportDistance.position.lng] : config.center, CITY_ZOOM);
                L.tileLayer(MAP_TILES, { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' }).addTo(this._map);
                L.control.zoom({ position: 'bottomright' }).addTo(this._map);
                this._map.on('click', () => this.close());
                this._cluster = L.markerClusterGroup({
                    showCoverageOnHover: false, maxClusterRadius: CLUSTER_RADIUS,
                    iconCreateFunction: (c) => L.divIcon({ className: '', html: `<span class="map-cluster">${c.getChildCount()}</span>`, iconSize: [44, 44] }),
                }).addTo(this._map);
                await this.load();
            } catch (e) {
                this.state = 'error';
            }
        },

        async load() {
            this.state = 'loading';
            this.close();
            try {
                const res = await fetch(this.$el.dataset.url, { headers: { Accept: 'application/json' } });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                this.truncated = data.truncated;
                this.draw(data.rooms);
                this.state = data.rooms.length ? 'ready' : 'empty';
            } catch (e) {
                this.state = 'error';
            }
        },

        draw(rooms) {
            const L = window.L;
            this._cluster.clearLayers();
            this._cluster.addLayers(rooms.map((room) => {
                const marker = L.marker([room.lat, room.lng], {
                    keyboard: true, title: `${room.title}, ${room.day} ${room.time}`,
                    icon: L.divIcon({
                        className: '', iconSize: null,
                        html: `<span class="map-pin${room.availability === 'almost' ? ' map-pin--almost' : ''}" style="--c: var(${room.sport.var})">`
                            + `<span class="map-pin__icon">${config.icons[room.sport.slug] || ''}</span><span class="map-pin__time">${room.time}</span></span>`,
                    }),
                });
                marker.on('click', (e) => { L.DomEvent.stopPropagation(e); this.open(room, marker); });
                return marker;
            }));
        },

        open(room, marker) {
            this._active?.getElement()?.querySelector('.map-pin')?.classList.remove('is-active');
            this._active = marker;
            marker.getElement()?.querySelector('.map-pin')?.classList.add('is-active');
            this.peek = room;
            this.$nextTick(() => window.sportDistance?.fill(this.$refs.peek));
        },

        close() {
            this._active?.getElement()?.querySelector('.map-pin')?.classList.remove('is-active');
            this._active = null;
            this.peek = null;
        },

        // „Lângă mine”: abia aici browserul cere voie la locație.
        async locate() {
            if (!window.sportDistance) return;
            this.geoError = false;
            this.locating = true;
            try {
                const p = await window.sportDistance.request();
                const L = window.L;
                this._map.setView([p.lat, p.lng], NEAR_ZOOM);
                this._me ??= L.circleMarker([p.lat, p.lng], { radius: 8, weight: 3, color: '#fff', fillOpacity: 1, className: 'map-me' }).addTo(this._map);
                this._me.setLatLng([p.lat, p.lng]);
            } catch (e) {
                this.geoError = true;
            } finally {
                this.locating = false;
            }
        },

        // Texte pentru cardul de jos (din lang/, prin config)
        price(room) { return room.price ? config.t.price.replace(':price', room.price) : config.t.free; },
        spots(room) { return config.t.spots[room.spots_left] ?? String(room.spots_left); },
        icon(room) { return config.icons[room.sport.slug] || ''; },
    }));
});
