/*
 * Sport.md — foaie de jos (componentă Alpine: sheet).
 * Structura: resources/views/components/sheet.blade.php · Aspectul: public/assets/css/components.css (.sheet)
 * Se deschide cu $dispatch('open-sheet', '<nume>'); Esc, fundalul sau butonul X o închid.
 * Cu inline-desktop, de la 1024px conținutul stă în pagină: atunci nu e dialog (fără role/aria-modal).
 */
const SHEET_DESKTOP = window.matchMedia('(min-width: 1024px)');
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('sheet', (name, inlineDesktop) => ({
        open: false,
        _returnTo: null,

        init() {
            if (!inlineDesktop) return;
            const sync = () => {
                const panel = this.$refs.panel;
                if (SHEET_DESKTOP.matches) {
                    this.close(false);
                    panel.removeAttribute('role');
                    panel.removeAttribute('aria-modal');
                    panel.removeAttribute('tabindex');
                } else {
                    panel.setAttribute('role', 'dialog');
                    panel.setAttribute('aria-modal', 'true');
                    panel.setAttribute('tabindex', '-1');
                }
            };
            sync();
            SHEET_DESKTOP.addEventListener('change', sync);
        },

        openIf(detail) {
            if (detail !== name || (inlineDesktop && SHEET_DESKTOP.matches)) return;
            this._returnTo = document.activeElement;
            this.open = true;
            document.body.classList.add('sheet-open');
            this.$nextTick(() => {
                const first = this.$refs.panel.querySelector('.sheet__body ' + FOCUSABLE) || this.$refs.panel;
                first.focus({ preventScroll: true });
            });
        },

        close(restoreFocus = true) {
            if (!this.open) return;
            this.open = false;
            if (!document.querySelector('.sheet.is-open')) document.body.classList.remove('sheet-open');
            if (restoreFocus && this._returnTo?.focus) this._returnTo.focus({ preventScroll: true });
        },

        // Keeps Tab inside the open sheet.
        trap(e) {
            if (!this.open) return;
            const items = [...this.$refs.panel.querySelectorAll(FOCUSABLE)].filter(el => el.offsetParent !== null);
            if (!items.length) return;
            const first = items[0], last = items[items.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        },
    }));
});
