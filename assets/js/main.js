/* ============================================================
   SPEC9 Flooring — Theme Toggle
   ------------------------------------------------------------
   Powers the dark/light switcher in the header.

   How it works:
     1. A tiny inline script in <head> already set
        data-theme="light|dark" on <html> before first paint.
     2. This file finishes the job:
        - keeps the toggle button in sync (icon, aria state)
        - persists the visitor's choice to localStorage
        - falls back to the OS preference when nothing is saved

   Storage key: "spec9-theme"  (values: "light" | "dark")
   When porting to WordPress/ACF this can be swapped for a
   cookie so the choice can also survive authenticated logins.
   ============================================================ */
(function () {
    'use strict';

    var STORAGE_KEY = 'spec9-theme';
    var root = document.documentElement;
    var btn = document.getElementById('theme-toggle');

    /* Current theme from the <html> data-theme attribute. */
    function currentTheme() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    /* Detect the operating system dark-mode preference. */
    function osPreference() {
        try {
            return window.matchMedia &&
                window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        } catch (e) {
            return 'light';
        }
    }

    /* Apply a theme; persist only on explicit user actions so we
       keep the "auto / follow OS" behaviour until they choose. */
    function applyTheme(theme, persist) {
        root.setAttribute('data-theme', theme);
        if (persist) {
            try {
                localStorage.setItem(STORAGE_KEY, theme);
            } catch (e) { /* private browsing / storage blocked */ }
        }
        sync(theme);
    }

    /* Keep the toggle button matching the active theme. */
    function sync(theme) {
        var isDark = theme === 'dark';
        if (btn) {
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
            btn.title = isDark ? 'Switch to light mode' : 'Switch to dark mode';
        }
    }

    /* On load: honour a saved choice, otherwise follow the OS. */
    function init() {
        var saved = null;
        try {
            saved = localStorage.getItem(STORAGE_KEY);
        } catch (e) { /* ignore */ }
        if (saved === 'light' || saved === 'dark') {
            applyTheme(saved, false);
        } else {
            applyTheme(osPreference(), false);
        }
    }

    if (btn) {
        btn.addEventListener('click', function () {
            applyTheme(currentTheme() === 'dark' ? 'light' : 'dark', true);
        });
    }

    init();
})();

/* ============================================================
   Quote modal
   ------------------------------------------------------------
   Every link that points at #quote opens the dialog: the header
   and hero "Get a Quote" buttons, the CTA band, and any menu link
   ending in #quote (so /#quote works from other pages too).

   Accessible: ESC / overlay click / ✕ close it, focus is trapped
   while the dialog is open, and focus returns to the trigger.

   Submission belongs to Contact Form 7 — it validates, sends and
   prints its response inside the dialog — so there is no submit
   handler here. The static site's mailto fallback, "thank you"
   panel and honeypot are gone with it.
   ============================================================ */
(function () {
    'use strict';

    var backdrop = document.getElementById('quote');
    var lastTrigger = null;

    if (!backdrop) { return; }

    /* ---------- open / close ---------- */
    function openModal(trigger) {
        lastTrigger = trigger || null;
        backdrop.classList.add('open');
        backdrop.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('modal-open');
        resetForm();
        var first = backdrop.querySelector('input, select, textarea');
        if (first) { first.focus(); }
    }

    function closeModal() {
        backdrop.classList.remove('open');
        backdrop.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('modal-open');
        if (lastTrigger && typeof lastTrigger.focus === 'function') {
            lastTrigger.focus();
        }
    }

    /* Contact Form 7 swaps the form for its response after a
       successful send. Put the empty form back on reopen so a
       visitor never comes back to a stale "thank you". */
    function resetForm() {
        var form = backdrop.querySelector('form.wpcf7-form');
        if (!form || form.className.indexOf('sent') === -1) { return; }
        var api = window.wpcf7 || {};
        var reset = api.reset || api.resetForm;
        if (typeof reset === 'function') { reset(form); }
    }

    /* ---------- triggers ---------- */
    Array.prototype.forEach.call(
        document.querySelectorAll('a[href$="#quote"]'),
        function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                openModal(el);
            });
        }
    );

    /* close: ✕ button, overlay click, Escape */
    Array.prototype.forEach.call(
        backdrop.querySelectorAll('[data-modal-close]'),
        function (el) { el.addEventListener('click', closeModal); }
    );

    backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) { closeModal(); }
    });

    document.addEventListener('keydown', function (e) {
        if (!backdrop.classList.contains('open')) { return; }
        if (e.key === 'Escape') { closeModal(); return; }
        if (e.key === 'Tab') { trapFocus(e); }
    });

    /* keep Tab/Tab+Shift inside the dialog */
    function trapFocus(e) {
        var nodes = backdrop.querySelectorAll(
            'a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        var focusables = Array.prototype.filter.call(nodes, function (el) {
            return !el.disabled && el.getAttribute('tabindex') !== '-1';
        });
        if (!focusables.length) { return; }
        var first = focusables[0];
        var last = focusables[focusables.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }
})();
