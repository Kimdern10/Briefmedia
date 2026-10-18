/* ==========================================================================
   BriefMedia — public site interactions (no dependencies)

   Loaded once (data-navigate-once). Click and key handling is attached to the
   document once; anything tied to elements on a page (slider, reveal, back to top,
   loader) is set up for each page through BM.onPage (see bm-navigate.js).
   ========================================================================== */
(function () {
    'use strict';

    if (window.__bmSiteReady) { return; }
    window.__bmSiteReady = true;

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
    const onPage = (fn) => (window.BM && window.BM.onPage ? window.BM.onPage(fn) : document.addEventListener('DOMContentLoaded', () => fn({ firstPage: true })));

    /* ---------- Toast ---------- */
    let toastTimer;
    function toast(message, duration = 2200) {
        // SweetAlert version from bm-alerts.js when available
        if (window.Swal && window.bmToast !== toast) {
            window.bmToast(message, 'success', Math.max(duration, 2500));
            return;
        }
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
    if (!window.bmToast) { window.bmToast = toast; }

    /* ---------- Theme ---------- */
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem('bm-theme', theme); } catch (e) { /* storage unavailable */ }
    }

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

    /* ---------- Drawer ---------- */
    function toggleDrawer(open) {
        const drawer = $('#bm-drawer');
        if (!drawer) { return; }
        drawer.classList.toggle('is-open', open);
        drawer.setAttribute('aria-hidden', String(!open));
        document.body.style.overflow = open ? 'hidden' : '';
    }

    /* ---------- Search overlay ---------- */
    function toggleSearch(open) {
        const search = $('#bm-search');
        if (!search) { return; }
        search.classList.toggle('is-open', open);
        search.setAttribute('aria-hidden', String(!open));
        if (open) {
            toggleDrawer(false);
            setTimeout(() => { const input = $('input', search); input && input.focus(); }, 60);
        }
    }

    // One click handler for the whole site (works on every page after navigation too)
    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-theme-toggle]')) {
            const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            setTheme(current === 'dark' ? 'light' : 'dark');
            return;
        }

        if (event.target.closest('[data-drawer-open]')) { toggleDrawer(true); return; }
        if (event.target.closest('[data-drawer-close]')) { toggleDrawer(false); }

        if (event.target.closest('[data-search-open]')) {
            event.preventDefault();
            toggleSearch(true);
            return;
        }
        if (event.target.id === 'bm-search') { toggleSearch(false); return; }

        if (event.target.closest('#bm-to-top')) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

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

            if (shareTrigger) {
                fillShareMenu(wrapper);
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

    // Cards send an empty share menu to keep pages small; build its links on first open
    function fillShareMenu(wrapper) {
        const menu = $('.bm-share__menu[data-share-lazy]', wrapper);
        if (!menu || menu.childElementCount) { return; }

        const url = encodeURIComponent(wrapper.dataset.url);
        const title = encodeURIComponent(wrapper.dataset.title);
        const links = [
            ['facebook', 'Facebook', `https://www.facebook.com/sharer/sharer.php?u=${url}`],
            ['twitter-x', 'X (Twitter)', `https://twitter.com/intent/tweet?url=${url}&text=${title}`],
            ['linkedin', 'LinkedIn', `https://www.linkedin.com/sharing/share-offsite?url=${url}`],
            ['whatsapp', 'WhatsApp', `https://wa.me/?text=${title}%20${url}`],
        ];

        links.forEach(([icon, label, href]) => {
            const link = document.createElement('a');
            link.className = 'bm-share__option';
            link.target = '_blank';
            link.rel = 'noopener';
            link.href = href;
            link.innerHTML = `<i class="bi bi-${icon}"></i> `;
            link.append(label);
            menu.append(link);
        });

        const copy = document.createElement('button');
        copy.type = 'button';
        copy.className = 'bm-share__option';
        copy.dataset.copyLink = wrapper.dataset.url;
        copy.innerHTML = '<i class="bi bi-link-45deg"></i> ';
        copy.append('Copy link');
        menu.append(copy);
    }

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

    /* ---------- Photo viewer (story pages) ---------- */
    // Tap a photo in a story to see it full-screen and uncropped; arrows or swipe for the
    // story's other photos; ✕, Esc or tapping outside the photo closes it.
    const viewerPhotos = '.bm-article__hero img, .bm-figure img, .bm-gallery img, .bm-prose img';
    let viewer = null;

    function storyPhotos() {
        return $$(viewerPhotos).filter((img) => {
            const link = img.closest('a[href]');
            // Photos that link somewhere else (not to an image) keep their link
            return img.src && !img.src.includes('bm-placeholder') && (!link || /\.(jpe?g|png|gif|webp)(\?|$)/i.test(link.href));
        });
    }

    function captionFor(img) {
        if (img.closest('.bm-article__hero')) {
            const caption = $('.bm-photo-caption');
            if (caption) {
                // Caption and photo credit, e.g. "Governor in Awka · Photo: BriefMedia"
                return $$('span', caption).map((part) => part.textContent.replace(/\s+/g, ' ').trim()).filter(Boolean).join(' · ');
            }
        }
        const figure = img.closest('figure');
        const figcaption = figure && $('figcaption', figure);

        return figcaption ? figcaption.textContent.trim() : '';
    }

    function openViewer(photos, index) {
        closeViewer();
        let current = index;

        viewer = document.createElement('div');
        viewer.className = 'bm-viewer';
        viewer.setAttribute('role', 'dialog');
        viewer.setAttribute('aria-modal', 'true');
        viewer.setAttribute('aria-label', 'Photo viewer');
        viewer.innerHTML = `
            <button type="button" class="bm-viewer__close" aria-label="Close photo"><i class="bi bi-x-lg"></i></button>
            ${photos.length > 1 ? '<button type="button" class="bm-viewer__nav bm-viewer__nav--prev" aria-label="Previous photo"><i class="bi bi-chevron-left"></i></button>' : ''}
            <figure class="bm-viewer__stage"><img alt=""><figcaption class="bm-viewer__caption"></figcaption></figure>
            ${photos.length > 1 ? '<button type="button" class="bm-viewer__nav bm-viewer__nav--next" aria-label="Next photo"><i class="bi bi-chevron-right"></i></button>' : ''}
            ${photos.length > 1 ? '<span class="bm-viewer__count"></span>' : ''}`;

        const image = $('img', viewer);
        const caption = $('.bm-viewer__caption', viewer);
        const count = $('.bm-viewer__count', viewer);

        const show = (next) => {
            current = (next + photos.length) % photos.length;
            const source = photos[current];
            image.classList.remove('is-ready');
            image.onload = () => image.classList.add('is-ready');
            image.src = source.currentSrc || source.src;
            image.alt = source.alt || '';
            const text = captionFor(source);
            caption.textContent = text;
            caption.hidden = !text;
            if (count) { count.textContent = (current + 1) + ' / ' + photos.length; }
        };

        viewer.show = show;
        viewer.step = (by) => show(current + by);

        viewer.addEventListener('click', (event) => {
            if (event.target.closest('.bm-viewer__nav--prev')) { viewer.step(-1); return; }
            if (event.target.closest('.bm-viewer__nav--next')) { viewer.step(1); return; }
            // Anywhere except the photo itself closes the viewer
            if (event.target !== image) { closeViewer(); }
        });

        let touchX = null;
        viewer.addEventListener('touchstart', (event) => { touchX = event.touches[0].clientX; }, { passive: true });
        viewer.addEventListener('touchend', (event) => {
            if (touchX === null || photos.length < 2) { return; }
            const delta = event.changedTouches[0].clientX - touchX;
            if (Math.abs(delta) > 50) { viewer.step(delta < 0 ? 1 : -1); }
            touchX = null;
        });

        document.body.appendChild(viewer);
        document.documentElement.classList.add('bm-viewer-open');
        show(current);
        requestAnimationFrame(() => viewer && viewer.classList.add('is-open'));
        $('.bm-viewer__close', viewer).focus();
    }

    function closeViewer() {
        if (!viewer) { return; }
        viewer.remove();
        viewer = null;
        document.documentElement.classList.remove('bm-viewer-open');
    }

    document.addEventListener('click', (event) => {
        const img = event.target.closest(viewerPhotos);
        if (!img || event.button !== 0 || viewer) { return; }
        const photos = storyPhotos();
        const index = photos.indexOf(img);
        if (index === -1) { return; }
        event.preventDefault();
        openViewer(photos, index);
    });

    document.addEventListener('keydown', (event) => {
        if (!viewer) { return; }
        if (event.key === 'Escape') { closeViewer(); }
        if (event.key === 'ArrowRight') { viewer.step(1); }
        if (event.key === 'ArrowLeft') { viewer.step(-1); }
    });

    document.addEventListener('livewire:navigating', closeViewer);

    /* ---------- Back to top ---------- */
    window.addEventListener('scroll', () => {
        const toTop = $('#bm-to-top');
        toTop && toTop.classList.toggle('is-visible', window.scrollY > 500);
    }, { passive: true });

    /* ---------- Hero rotator (homepage) ---------- */
    let heroTimer;
    document.addEventListener('livewire:navigating', () => clearInterval(heroTimer));

    function setUpHero() {
        const hero = $('[data-hero]');
        if (!hero) { return; }

        const slides = $$('.bm-hero__slide', hero);
        const items = $$('[data-hero-go]', hero);
        const dots = $$('.bm-hero__dot', hero);
        const delay = parseInt(hero.dataset.delay || '6000', 10);
        let index = 0;

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
            clearInterval(heroTimer);
            if (slides.length > 1) { heroTimer = setInterval(() => show(index + 1), delay); }
        };

        [...items, ...dots].forEach((el) => el.addEventListener('click', () => {
            show(parseInt(el.dataset.heroGo, 10));
            play();
        }));
        hero.addEventListener('mouseenter', () => { clearInterval(heroTimer); hero.classList.add('is-paused'); });
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

    /* ---------- Reveal on scroll ---------- */
    function setUpReveal() {
        const reveals = $$('.bm-reveal');
        if (!('IntersectionObserver' in window)) {
            reveals.forEach((el) => el.classList.add('is-in'));
            return;
        }
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -60px 0px' });
        reveals.forEach((el) => observer.observe(el));
    }

    /* ---------- Page loader ---------- */
    function setUpLoader(firstPage) {
        const loader = $('.bm-loader');
        if (!loader) { return; }

        // Only the first visit shows the full-screen loader; later pages swap in straight away
        if (!firstPage) {
            loader.remove();
            return;
        }

        const hide = () => {
            loader.classList.add('is-hidden');
            setTimeout(() => loader.remove(), 500);
        };
        if (document.readyState === 'complete') { hide(); } else { window.addEventListener('load', hide, { once: true }); }
        setTimeout(hide, 2500);
    }

    onPage(({ firstPage }) => {
        setUpLoader(firstPage);
        setUpHero();
        setUpReveal();
        const toTop = $('#bm-to-top');
        toTop && toTop.classList.toggle('is-visible', window.scrollY > 500);
    });
})();
