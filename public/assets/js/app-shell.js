/* ============================================================
   Sport.md — App shell: slide-over panel + in-place actions
   - Event detail panels are deep-linkable (/rooms/{id}) via the
     History API; sharing the URL opens the app with the panel open.
   - Create-event / edit-profile panels are forms (no history).
   Protocol: every panel endpoint returns HTML.
   - response has [data-success] → close panel + toast (+ refresh rooms)
   - otherwise → inject HTML into the panel body
   ============================================================ */
(function () {
    'use strict';

    const panel = document.getElementById('slideover');
    const overlay = document.getElementById('slideover-overlay');
    const body = document.getElementById('slideover-body');
    const toast = document.getElementById('toast');
    if (!panel || !overlay || !body) return;

    let currentRoomUrl = null;   // the shareable /rooms/{id} currently shown (or null for forms/closed)
    let baseUrl = null;          // where to return on close (preserves home filters)

    /* ---------- low-level open / close ---------- */
    function loadInto(url) {
        body.innerHTML = '<div class="panel-loading">…</div>';
        show();
        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.text(); })
            .then(function (html) { body.innerHTML = html; })
            .catch(function () { body.innerHTML = '<div class="panel-loading">⚠️</div>'; });
    }

    function show() {
        panel.hidden = false; overlay.hidden = false;
        requestAnimationFrame(function () {
            panel.classList.add('is-open');
            overlay.classList.add('is-open');
            panel.setAttribute('aria-hidden', 'false');
            document.body.classList.add('has-panel');
        });
        document.body.style.overflow = 'hidden';
    }

    function hide() {
        panel.classList.remove('is-open');
        overlay.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('has-panel');
        document.body.style.overflow = '';
        setTimeout(function () {
            if (!panel.classList.contains('is-open')) { panel.hidden = true; overlay.hidden = true; body.innerHTML = ''; }
        }, 250);
    }

    /* ---------- room (deep-linkable) vs form (no history) ---------- */
    function openRoom(url, push) {
        if (push) {
            if (baseUrl === null) baseUrl = window.location.href;
            window.history.pushState({ room: url }, '', url);
        }
        currentRoomUrl = url;
        loadInto(url);
    }

    function openForm(url) {
        loadInto(url);   // no history change; not shareable
    }

    function closePanel(fromPopstate) {
        hide();
        if (currentRoomUrl && !fromPopstate) {
            window.history.pushState({}, '', baseUrl || '/');
        }
        currentRoomUrl = null;
    }

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        toast.classList.add('is-open');
        setTimeout(function () {
            toast.classList.remove('is-open');
            setTimeout(function () { toast.hidden = true; }, 300);
        }, 2500);
    }

    function refreshRooms() { window.dispatchEvent(new Event('rooms:refresh')); }

    /* ---------- open triggers ---------- */
    document.addEventListener('click', function (e) {
        // Form panels (create event / edit profile) — explicit URL, no history.
        const formTrigger = e.target.closest('[data-panel-url]');
        if (formTrigger) { e.preventDefault(); openForm(formTrigger.dataset.panelUrl); return; }

        // Room cards → deep-linkable event detail.
        const card = e.target.closest('.room-card[data-detail-url]');
        if (card) { e.preventDefault(); openRoom(card.dataset.detailUrl, true); return; }

        // Close affordances.
        if (e.target.closest('[data-panel-close]') || e.target === overlay) { closePanel(false); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.classList.contains('is-open')) { closePanel(false); return; }
        if ((e.key === 'Enter' || e.key === ' ') && e.target.closest) {
            const card = e.target.closest('.room-card[data-detail-url]');
            if (card) { e.preventDefault(); openRoom(card.dataset.detailUrl, true); }
        }
    });

    /* ---------- browser back / forward ---------- */
    window.addEventListener('popstate', function () {
        const m = window.location.pathname.match(/^\/rooms\/(\d+)\/?$/);
        if (m) {
            const url = window.location.pathname;
            if (currentRoomUrl !== url) openRoom(url, false);
        } else if (panel.classList.contains('is-open') && currentRoomUrl) {
            closePanel(true);
        }
    });

    /* ---------- panel form submits ---------- */
    body.addEventListener('submit', function (e) {
        const form = e.target.closest('form.js-panel-form');
        if (!form) return;
        e.preventDefault();

        const btn = form.querySelector('[type="submit"]');
        if (btn) btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                const success = tmp.querySelector('[data-success]');

                if (success) {
                    showToast(success.textContent.trim());
                    if (success.dataset.refresh === 'rooms') refreshRooms();
                    closePanel(false);
                } else {
                    body.innerHTML = html;   // validation errors, or updated join-state detail
                    refreshRooms();           // keep the grid behind in sync (join/leave counts)
                }
            })
            .catch(function () { if (btn) btn.disabled = false; });
    });

    /* ---------- user menu ---------- */
    const menuTrigger = document.getElementById('user-menu-trigger');
    const menuDropdown = document.getElementById('user-menu-dropdown');
    if (menuTrigger && menuDropdown) {
        menuTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = menuDropdown.classList.toggle('is-open');
            menuTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function () { menuDropdown.classList.remove('is-open'); });
    }

    /* ---------- init from a server-pre-opened deep link ---------- */
    if (panel.dataset.openRoom) {
        currentRoomUrl = window.location.pathname;   // e.g. /rooms/12
        baseUrl = '/';                               // close/back returns to the home landing
        document.body.style.overflow = 'hidden';     // panel already rendered open by the server
    }
})();
