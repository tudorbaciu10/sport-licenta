/*
 * Sport.md — distanța până la meciuri, calculată doar în browser (locația nu pleacă spre server).
 * Elementele [data-distance data-lat data-lng] primesc „2,1 km” în locul textului lor (orașul sau adresa).
 * Permisiunea nu se cere singură: dacă e deja dată, distanța apare la încărcare; altfel doar după
 * window.sportDistance.request() (butonul „Lângă mine” de pe hartă).
 */
(() => {
    const MAX_AGE_MS = 10 * 60 * 1000;   // o poziție mai veche de 10 minute se cere din nou
    let position = null;

    const lang = document.documentElement.lang || 'ro';
    const format = (km) => new Intl.NumberFormat(lang, {
        style: 'unit', unit: 'kilometer', maximumFractionDigits: km < 10 ? 1 : 0,
    }).format(km);

    // Distanța pe glob (haversine), în km.
    function distanceKm(a, b) {
        const rad = (d) => d * Math.PI / 180;
        const dLat = rad(b.lat - a.lat), dLng = rad(b.lng - a.lng);
        const h = Math.sin(dLat / 2) ** 2 + Math.cos(rad(a.lat)) * Math.cos(rad(b.lat)) * Math.sin(dLng / 2) ** 2;
        return 6371 * 2 * Math.asin(Math.sqrt(h));
    }

    function fill(root = document) {
        if (!position) return;
        root.querySelectorAll('[data-distance]').forEach((el) => {
            const lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
            if (Number.isNaN(lat) || Number.isNaN(lng)) return;
            el.textContent = format(distanceKm(position, { lat, lng }));
            el.dataset.distanceShown = '';
        });
    }

    function request() {
        return new Promise((resolve, reject) => {
            if (!('geolocation' in navigator)) return reject(new Error('unsupported'));
            navigator.geolocation.getCurrentPosition(
                (p) => { position = { lat: p.coords.latitude, lng: p.coords.longitude }; fill(); resolve(position); },
                (err) => reject(err),
                { maximumAge: MAX_AGE_MS, timeout: 10000 },
            );
        });
    }

    window.sportDistance = { request, fill, get position() { return position; } };

    document.addEventListener('DOMContentLoaded', () => {
        if (!navigator.permissions?.query) return;
        navigator.permissions.query({ name: 'geolocation' })
            .then((status) => { if (status.state === 'granted') request().catch(() => {}); })
            .catch(() => {});
    });
})();
