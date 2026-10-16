/* ==========================================================================
   BriefMedia — public site interactions (no dependencies)
   ========================================================================== */
(function () {
    'use strict';

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    /* ---------- Toast ---------- */
    let toastTimer;
    function toast(message, duration = 2200) {
        let el = $('.bm-toast');
        if (!el) {
            el = document.createElement('div');
            el.className = 'bm-toast';
            el.setAttribute('role', 'status');
            document.body.appendChild(el);
        }
        el.textContent = message;
        el.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => el.classList.remove('is-visible'), duration);
    }
    window.bmToast = toast;

    /* ---------- Theme ---------- */
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem('bm-theme', theme); } catch (e) { /* storage unavailable */ }
    }
    $$('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            setTheme(current === 'dark' ? 'light' : 'dark');
        });
    });

    /* ---------- Dropdowns & share popovers ---------- */
    function closeAll(except) {
        $$('.bm-dropdown.is-open, .bm-share.is-open').forEach((el) => {
            if (el !== except) {
                el.classList.remove('is-open');
                const trigger = $('[aria-expanded]', el);
                if (trigger) { trigger.setAttribute('aria-expanded', 'false'); }
            }
        });
    }

    document.addEventListener('click', (event) => {
        const dropdownTrigger = event.target.closest('[data-dropdown-toggle]');
        const shareTrigger = event.target.closest('[data-share-toggle]');
        const trigger = dropdownTrigger || shareTrigger;

        if (trigger) {
            event.preventDefault();
            const wrapper = trigger.closest(dropdownTrigger ? '.bm-dropdown' : '.bm-share');

            if (shareTrigger && navigator.share && window.matchMedia('(pointer: coarse)').matches) {
                navigator.share({ title: wrapper.dataset.title, url: wrapper.dataset.url }).catch(() => {});
                return;
            }

            const willOpen = !wrapper.classList.contains('is-open');
            closeAll(wrapper);
            wrapper.classList.toggle('is-open', willOpen);
            trigger.setAttribute('aria-expanded', String(willOpen));
            return;
        }

        const copyButton = event.target.closest('[data-copy-link]');
        if (copyButton) {
            event.preventDefault();
            const url = copyButton.dataset.copyLink;
            const done = () => { toast('Link copied to clipboard'); closeAll(); };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(done).catch(() => fallbackCopy(url, done));
            } else {
                fallbackCopy(url, done);
            }
            return;
        }

        if (!event.target.closest('.bm-dropdown__menu, .bm-share__menu')) {
            closeAll();
        }
    });

    function fallbackCopy(text, done) {
        const input = document.createElement('textarea');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        try { document.execCommand('copy'); done(); } catch (e) { toast('Could not copy link'); }
        input.remove();
    }

    /* ---------- Drawer ---------- */
    const drawer = $('#bm-drawer');
    function toggleDrawer(open) {
        if (!drawer) { return; }
        drawer.classList.toggle('is-open', open);
        drawer.setAttribute('aria-hidden', String(!open));
        document.body.style.overflow = open ? 'hidden' : '';
    }
    $$('[data-drawer-open]').forEach((el) => el.addEventListener('click', () => toggleDrawer(true)));
    $$('[data-drawer-close]').forEach((el) => el.addEventListener('click', () => toggleDrawer(false)));

    /* ---------- Search overlay ---------- */
    const search = $('#bm-search');
    function toggleSearch(open) {
        if (!search) { return; }
        search.classList.toggle('is-open', open);
        search.setAttribute('aria-hidden', String(!open));
        if (open) {
            toggleDrawer(false);
            setTimeout(() => $('input', search).focus(), 60);
        }
    }
    $$('[data-search-open]').forEach((el) => el.addEventListener('click', (event) => {
        event.preventDefault();
        toggleSearch(true);
    }));
    if (search) {
        search.addEventListener('click', (event) => {
            if (event.target === search) { toggleSearch(false); }
        });
    }

    document.addEventListener('keydown', (event) => {
        const typing = /input|textarea|select/i.test(event.target.tagName) || event.target.isContentEditable;
        if ((event.key === 'k' && (event.ctrlKey || event.metaKey)) || (event.key === '/' && !typing)) {
            event.preventDefault();
            toggleSearch(true);
        }
        if (event.key === 'Escape') {
            toggleSearch(false);
            toggleDrawer(false);
            closeAll();
        }
    });

    /* ---------- Hero rotator ---------- */
    const hero = $('[data-hero]');
    if (hero) {
        const slides = $$('.bm-hero__slide', hero);
        const items = $$('[data-hero-go]', hero);
        const dots = $$('.bm-hero__dot', hero);
        const delay = parseInt(hero.dataset.delay || '6000', 10);
        let index = 0;
        let timer;

        hero.style.setProperty('--bm-hero-delay', delay + 'ms');

        const show = (next) => {
            index = (next + slides.length) % slides.length;
            [slides, items, dots].forEach((group) => group.forEach((el, i) => {
                el.classList.toggle('is-active', i === index);
                if (el.hasAttribute('aria-selected')) { el.setAttribute('aria-selected', String(i === index)); }
            }));
            // Restart the progress-bar animation on the active item.
            items.forEach((el) => {
                const bar = $('.bm-hero__progress', el);
                if (bar) { bar.style.animation = 'none'; void bar.offsetWidth; bar.style.animation = ''; }
            });
        };
        const play = () => {
            clearInterval(timer);
            if (slides.length > 1) { timer = setInterval(() => show(index + 1), delay); }
        };

        [...items, ...dots].forEach((el) => el.addEventListener('click', () => {
            show(parseInt(el.dataset.heroGo, 10));
            play();
        }));
        hero.addEventListener('mouseenter', () => { clearInterval(timer); hero.classList.add('is-paused'); });
        hero.addEventListener('mouseleave', () => { hero.classList.remove('is-paused'); play(); });

        let touchX = null;
        hero.addEventListener('touchstart', (event) => { touchX = event.touches[0].clientX; }, { passive: true });
        hero.addEventListener('touchend', (event) => {
            if (touchX === null) { return; }
            const delta = event.changedTouches[0].clientX - touchX;
            if (Math.abs(delta) > 40) { show(index + (delta < 0 ? 1 : -1)); play(); }
            touchX = null;
        });

        show(0);
        play();
    }

    /* ---------- Back to top ---------- */
    const toTop = $('#bm-to-top');
    if (toTop) {
        const onScroll = () => toTop.classList.toggle('is-visible', window.scrollY > 500);
        window.addEventListener('scroll', onScroll, { passive: true });
        toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        onScroll();
    }

    /* ---------- Reveal on scroll ---------- */
    const reveals = $$('.bm-reveal');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -60px 0px' });
        reveals.forEach((el) => observer.observe(el));
    } else {
        reveals.forEach((el) => el.classList.add('is-in'));
    }

    /* ---------- Page loader ---------- */
    const loader = $('.bm-loader');
    if (loader) {
        const hide = () => {
            loader.classList.add('is-hidden');
            setTimeout(() => loader.remove(), 500);
        };
        if (document.readyState === 'complete') { hide(); } else { window.addEventListener('load', hide); }
        setTimeout(hide, 2500);
    }
})();
