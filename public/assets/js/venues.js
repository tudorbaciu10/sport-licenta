/* ============================================================
   Sport.md — Facilities marketplace behaviour
   Mirrors landing.js: drag-to-scroll category selector + AJAX
   filtering of rentable venues (category + city/country/surface).
   ============================================================ */
(function () {
    'use strict';

    const track = document.getElementById('venue-track');
    const list = document.getElementById('venues-list');
    const count = document.getElementById('venues-count');
    const form = document.getElementById('venue-filters-form');
    const categoryField = document.getElementById('filter-category');
    const url = list ? list.dataset.venuesUrl : null;

    if (!track || !list || !url) return;

    /* Drag-to-scroll. */
    let isDown = false, startX = 0, startScroll = 0, moved = false;
    track.addEventListener('mousedown', function (e) {
        isDown = true; moved = false; startX = e.pageX; startScroll = track.scrollLeft;
        track.classList.add('is-dragging');
    });
    window.addEventListener('mouseup', function () { isDown = false; track.classList.remove('is-dragging'); });
    track.addEventListener('mouseleave', function () { isDown = false; track.classList.remove('is-dragging'); });
    track.addEventListener('mousemove', function (e) {
        if (!isDown) return;
        const walk = e.pageX - startX;
        if (Math.abs(walk) > 4) moved = true;
        track.scrollLeft = startScroll - walk;
    });
    track.addEventListener('click', function (e) {
        if (moved) { e.preventDefault(); e.stopPropagation(); }
    }, true);

    /* AJAX filtering. */
    const chips = Array.prototype.slice.call(track.querySelectorAll('.sport-chip'));
    let current = (chips.filter(function (c) { return c.classList.contains('is-active'); })[0] || {}).dataset;
    current = current ? (current.category || '') : '';

    function setActive(cat) {
        chips.forEach(function (c) { c.classList.toggle('is-active', (c.dataset.category || '') === cat); });
    }

    function collectParams() {
        const params = new URLSearchParams();
        if (current) params.set('category', current);
        if (form) {
            ['venue_city', 'venue_country', 'surface'].forEach(function (name) {
                const field = form.elements[name];
                if (field && field.value) params.set(name, field.value);
            });
        }
        return params;
    }

    function load() {
        const params = collectParams();
        list.classList.add('is-loading');
        fetch(url + (params.toString() ? '?' + params.toString() : ''), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                list.innerHTML = html;
                if (count) count.textContent = list.querySelectorAll('.venue-card').length;
            })
            .catch(function () { list.innerHTML = '<div class="rooms-empty">⚠️</div>'; })
            .finally(function () { list.classList.remove('is-loading'); });
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function (e) {
            e.preventDefault();
            current = chip.dataset.category || '';
            if (categoryField) categoryField.value = current;
            setActive(current);
            load();
        });
    });

    if (form) {
        form.addEventListener('submit', function (e) { e.preventDefault(); load(); });
        const reset = document.getElementById('btn-venue-filter-reset');
        if (reset) {
            reset.addEventListener('click', function () {
                ['venue_city', 'venue_country', 'surface'].forEach(function (name) {
                    if (form.elements[name]) form.elements[name].value = '';
                });
                load();
            });
        }
    }
})();
