/* ============================================================
   Sport.md — Landing behaviour
   1) drag-to-scroll on the horizontal sport selector
   2) AJAX filtering of rooms: sport chips + date/time/city filter bar
   ============================================================ */
(function () {
    'use strict';

    const track = document.getElementById('sport-track');
    const roomsList = document.getElementById('rooms-list');
    const roomsCount = document.getElementById('rooms-count');
    const form = document.getElementById('room-filters-form');
    const sportField = document.getElementById('filter-sport');
    const roomsUrl = roomsList ? roomsList.dataset.roomsUrl : null;

    /* ---------- 1) Drag-to-scroll ---------- */
    if (track) {
        let isDown = false, startX = 0, startScroll = 0, moved = false;

        track.addEventListener('mousedown', function (e) {
            isDown = true; moved = false;
            startX = e.pageX; startScroll = track.scrollLeft;
            track.classList.add('is-dragging');
        });
        window.addEventListener('mouseup', function () {
            isDown = false; track.classList.remove('is-dragging');
        });
        track.addEventListener('mouseleave', function () {
            isDown = false; track.classList.remove('is-dragging');
        });
        track.addEventListener('mousemove', function (e) {
            if (!isDown) return;
            const walk = e.pageX - startX;
            if (Math.abs(walk) > 4) moved = true;
            track.scrollLeft = startScroll - walk;
        });
        track.addEventListener('click', function (e) {
            if (moved) { e.preventDefault(); e.stopPropagation(); }
        }, true);
    }

    /* ---------- 2) AJAX room filtering (sport + filter bar) ---------- */
    if (!track || !roomsList || !roomsUrl) return;

    const chips = Array.prototype.slice.call(track.querySelectorAll('.sport-chip'));
    let currentSport = (chips.filter(function (c) { return c.classList.contains('is-active'); })[0] || {}).dataset;
    currentSport = currentSport ? (currentSport.sport || '') : '';

    function setActiveChip(sport) {
        chips.forEach(function (c) {
            c.classList.toggle('is-active', (c.dataset.sport || '') === sport);
        });
    }

    function collectParams() {
        const params = new URLSearchParams();
        if (currentSport) params.set('sport', currentSport);
        if (form) {
            ['date_from', 'date_to', 'time_from', 'city'].forEach(function (name) {
                const field = form.elements[name];
                if (field && field.value) params.set(name, field.value);
            });
        }
        return params;
    }

    function loadRooms(push) {
        const params = collectParams();
        const url = roomsUrl + (params.toString() ? '?' + params.toString() : '');

        roomsList.classList.add('is-loading');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                roomsList.innerHTML = html;
                if (roomsCount) {
                    roomsCount.textContent = roomsList.querySelectorAll('.room-card').length;
                }
                if (push) {
                    const pageUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.pushState({}, '', pageUrl);
                }
            })
            .catch(function () {
                roomsList.innerHTML = '<div class="rooms-empty">⚠️</div>';
            })
            .finally(function () {
                roomsList.classList.remove('is-loading');
            });
    }

    // Sport chips.
    chips.forEach(function (chip) {
        chip.addEventListener('click', function (e) {
            e.preventDefault();
            currentSport = chip.dataset.sport || '';
            if (sportField) sportField.value = currentSport;
            setActiveChip(currentSport);
            loadRooms(true);
        });
    });

    // Filter bar: Apply (submit).
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            loadRooms(true);
        });

        // Reset.
        const resetBtn = document.getElementById('btn-filter-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                ['date_from', 'date_to', 'time_from', 'city'].forEach(function (name) {
                    if (form.elements[name]) form.elements[name].value = '';
                });
                loadRooms(true);
            });
        }
    }

    // Allow other scripts (app-shell.js) to refresh the grid after in-place actions.
    window.addEventListener('rooms:refresh', function () { loadRooms(false); });

    // Browser back/forward.
    window.addEventListener('popstate', function () {
        const sp = new URLSearchParams(window.location.search);
        currentSport = sp.get('sport') || '';
        if (sportField) sportField.value = currentSport;
        setActiveChip(currentSport);
        if (form) {
            ['date_from', 'date_to', 'time_from', 'city'].forEach(function (name) {
                if (form.elements[name]) form.elements[name].value = sp.get(name) || '';
            });
        }
        loadRooms(false);
    });
})();
