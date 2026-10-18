/* ==========================================================================
   BriefMedia: move between pages without a full reload (Livewire navigate)

   Every internal link (and GET form, like search) loads the next page in the
   background and swaps it in, with a thin progress bar. The browser only does a
   real reload when the visitor refreshes, first arrives, or moves between the
   public site and the admin panel (they use different designs).

   Loaded once by both layouts with data-navigate-once. The shared site/admin scripts
   (also loaded once) set up each page with BM.onPage(fn), which runs on first load
   and after every navigation. Scripts pushed by a single page (@push('scripts')) run
   by themselves each time that page is shown, so they don't use BM.onPage; they use
   BM.loadScript(src) to wait for a library such as Chart.js or CKEditor.
   ========================================================================== */
(function () {
    'use strict';

    if (window.BM && window.BM.navigateReady) { return; }

    const BM = window.BM = window.BM || {};
    BM.navigateReady = true;

    /* ---------- Page set-up hooks ---------- */
    const pageCallbacks = [];
    let firstPage = true;

    /** Run fn for the current page now, and again for every page navigated to. */
    BM.onPage = function (fn) {
        pageCallbacks.push(fn);
        if (document.readyState !== 'loading' && document.body && document.body.dataset.bmPageReady) {
            fn({ firstPage });
        }
    };

    function runPage() {
        if (!document.body || document.body.dataset.bmPageReady) { return; }
        document.body.dataset.bmPageReady = '1';
        pageCallbacks.forEach((fn) => {
            try { fn({ firstPage }); } catch (error) { console.error(error); }
        });
        firstPage = false;
    }

    /** Load a script once (Chart.js, CKEditor…) and resolve when it is ready. */
    const loadedScripts = {};
    BM.loadScript = function (src) {
        if (!loadedScripts[src]) {
            loadedScripts[src] = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = () => { delete loadedScripts[src]; reject(new Error('Could not load ' + src)); };
                document.head.appendChild(script);
            });
        }
        return loadedScripts[src];
    };

    /* ---------- Which area a page belongs to ---------- */
    const areaOf = (pathname) => (pathname === '/admin' || pathname.startsWith('/admin/') ? 'admin' : 'site');
    const pageArea = () => (document.body && document.body.classList.contains('ad') ? 'admin'
        : document.body && document.body.classList.contains('bm') ? 'site' : 'other');
    const startArea = pageArea();

    // Never handled here: files, feeds, the service worker and other non-page addresses
    const skipPaths = /^\/(logout|livewire|storage|theme|profile-photo|OneSignalSDKWorker\.js|sitemap\.xml|news-sitemap\.xml|robots\.txt|newsletter\/unsubscribe)(\/|$)/i;
    const fileLike = /\.(pdf|jpe?g|png|gif|webp|svg|zip|xml|txt|csv|xlsx?|docx?|mp3|mp4)$/i;

    function canNavigateTo(url) {
        if (url.origin !== location.origin) { return false; }
        if (skipPaths.test(url.pathname) || fileLike.test(url.pathname)) { return false; }
        if (areaOf(url.pathname) !== areaOf(location.pathname)) { return false; }

        return true;
    }

    function navigate(url) {
        if (window.Livewire && window.Livewire.navigate) {
            window.Livewire.navigate(url);
        } else {
            location.href = url;
        }
    }

    /* ---------- Links ---------- */
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) { return; }

        const link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('download') || link.hasAttribute('wire:navigate') || link.hasAttribute('data-no-navigate')) { return; }
        if (link.target && link.target !== '_self') { return; }
        if (link.closest('[data-no-navigate]')) { return; }

        const url = new URL(link.href, location.href);
        if (!/^https?:$/.test(url.protocol)) { return; }

        // Same page, different #section: let the browser scroll
        if (url.pathname === location.pathname && url.search === location.search && url.hash) { return; }

        if (!canNavigateTo(url)) { return; }

        event.preventDefault();
        navigate(url.href);
    });

    /* ---------- GET forms (search) ---------- */
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (event.defaultPrevented || !(form instanceof HTMLFormElement)) { return; }
        if ((form.getAttribute('method') || 'get').toLowerCase() !== 'get') { return; }
        if (form.hasAttribute('wire:submit') || form.hasAttribute('data-no-navigate') || (form.target && form.target !== '_self')) { return; }

        const url = new URL(form.getAttribute('action') || location.href, location.href);
        if (!canNavigateTo(url)) { return; }

        url.search = new URLSearchParams(new FormData(form)).toString();
        event.preventDefault();
        navigate(url.href);
    });

    /* ---------- Keep dark mode while moving between pages ---------- */
    // Livewire copies the new page's <html data-theme="light"> over the current one; put the
    // visitor's own theme back straight away (before the browser paints).
    const themeKey = startArea === 'admin' ? 'bm-admin-theme' : 'bm-theme';
    const wantedTheme = () => {
        let theme = null;
        try { theme = localStorage.getItem(themeKey); } catch (e) { /* storage unavailable */ }
        return theme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    };
    let restoringTheme = false;
    document.addEventListener('livewire:navigating', () => { restoringTheme = true; });
    new MutationObserver(() => {
        if (!restoringTheme) { return; }
        const theme = wantedTheme();
        if (document.documentElement.getAttribute('data-theme') !== theme) {
            document.documentElement.setAttribute('data-theme', theme);
        }
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    /* ---------- After each navigation ---------- */
    document.addEventListener('livewire:navigated', () => {
        // Ended up on a page with the other design (e.g. sent to log in) or an error page: load it properly
        if (pageArea() !== startArea) {
            location.reload();
            return;
        }

        restoringTheme = false;
        if (document.documentElement.getAttribute('data-theme') !== wantedTheme()) {
            document.documentElement.setAttribute('data-theme', wantedTheme());
        }

        if (!firstPage && typeof window.gtag === 'function') {
            window.gtag('event', 'page_view', { page_location: location.href, page_title: document.title });
        }

        runPage();
    });

    // Once the visitor moves to another page, the full-screen page loader isn't needed
    document.addEventListener('livewire:navigating', () => {
        if (!document.getElementById('bm-navigate-style')) {
            const style = document.createElement('style');
            style.id = 'bm-navigate-style';
            style.textContent = '.bm-loader{display:none!important}';
            document.head.appendChild(style);
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runPage);
    } else {
        runPage();
    }
})();
