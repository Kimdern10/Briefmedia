/* ==========================================================================
   BriefMedia — admin panel interactions (no dependencies)

   Loaded once (data-navigate-once). Clicks, changes and typing are handled on the
   document once, so they work on every admin page after navigation; the few things
   that need setting up per page (counters) run through BM.onPage (bm-navigate.js).
   ========================================================================== */
(function () {
    'use strict';

    if (window.__bmAdminReady) { return; }
    window.__bmAdminReady = true;

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
    const onPage = (fn) => (window.BM && window.BM.onPage ? window.BM.onPage(fn) : document.addEventListener('DOMContentLoaded', () => fn({ firstPage: true })));

    /* ---------- Toast ---------- */
    let toastTimer;
    window.adToast = function (message, tone = 'success') {
        let el = $('.ad-toast');
        if (!el) {
            el = document.createElement('div');
            el.setAttribute('role', 'status');
            document.body.appendChild(el);
        }
        const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
        el.className = 'ad-toast ad-toast--' + tone;
        el.innerHTML = '<i class="bi ' + (icons[tone] || icons.info) + '"></i><span></span>';
        $('span', el).textContent = message;
        requestAnimationFrame(() => el.classList.add('is-visible'));
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => el.classList.remove('is-visible'), 4500);
    };

    /* ---------- Sidebar (mobile) ---------- */
    const toggleSide = (open) => {
        const side = $('.ad-side');
        if (!side) { return; }
        side.classList.toggle('is-open', open);
        const backdrop = $('.ad-backdrop');
        backdrop && backdrop.classList.toggle('is-open', open);
    };

    /* ---------- Clicks ---------- */
    document.addEventListener('click', (event) => {
        // Theme
        if (event.target.closest('[data-theme-toggle]')) {
            const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            try { localStorage.setItem('bm-admin-theme', next); } catch (e) { /* storage unavailable */ }
            document.documentElement.setAttribute('data-theme', next);
            return;
        }

        // Mobile sidebar
        if (event.target.closest('[data-side-toggle]')) {
            const side = $('.ad-side');
            toggleSide(!(side && side.classList.contains('is-open')));
            return;
        }
        if (event.target.closest('.ad-backdrop')) { toggleSide(false); return; }

        // Tabs
        const tab = event.target.closest('[data-tabs] [data-tab]');
        if (tab) {
            const group = tab.closest('[data-tabs]');
            $$('[data-tab]', group).forEach((t) => t.classList.toggle('is-active', t === tab));
            $$('.ad-tab-pane', group).forEach((pane) => pane.classList.toggle('is-active', pane.id === tab.dataset.tab));
        }

        // Status buttons next to a table filter
        const segmentButton = event.target.closest('.ad-toolbar .ad-segment button');
        if (segmentButton) {
            const segment = segmentButton.closest('.ad-segment');
            $$('button', segment).forEach((b) => b.classList.toggle('is-active', b === segmentButton));
            const input = $('[data-table-filter]', segmentButton.closest('.ad-toolbar'));
            if (input) { input.dataset.status = segmentButton.dataset.status; filterTable(input); }
        }

        // Dropdowns
        const trigger = event.target.closest('[data-dropdown-toggle]');
        $$('.ad-dropdown.is-open').forEach((el) => {
            if (!trigger || el !== trigger.closest('.ad-dropdown')) { el.classList.remove('is-open'); }
        });
        if (trigger) {
            event.preventDefault();
            trigger.closest('.ad-dropdown').classList.toggle('is-open');
        }
    });

    /* Confirm dialogs now use SweetAlert: see bm-alerts.js (bmConfirm). */

    /* ---------- Client-side table filter ---------- */
    function filterTable(input) {
        const table = $(input.dataset.tableFilter);
        if (!table) { return; }
        const term = input.value.trim().toLowerCase();
        const status = input.dataset.status || 'all';
        let visible = 0;
        $$('tbody tr[data-row]', table).forEach((row) => {
            const matchesText = !term || row.textContent.toLowerCase().includes(term);
            const matchesStatus = status === 'all' || row.dataset.status === status;
            row.hidden = !(matchesText && matchesStatus);
            if (!row.hidden) { visible++; }
        });
        const empty = $('[data-filter-empty]', table);
        if (empty) { empty.hidden = visible > 0; }
    }

    /* ---------- Character counters ---------- */
    function updateCounter(field) {
        const output = document.getElementById(field.dataset.counter);
        if (!output) { return; }
        const limit = parseInt(field.dataset.recommended || '0', 10);
        output.textContent = field.value.length;
        const counter = output.closest('.ad-counter');
        counter && counter.classList.toggle('is-over', limit > 0 && field.value.length > limit);
    }

    document.addEventListener('input', (event) => {
        if (event.target.matches('[data-table-filter]')) { filterTable(event.target); }
        if (event.target.matches('[data-counter]')) { updateCounter(event.target); }
    });

    /* ---------- Image upload previews ---------- */
    document.addEventListener('change', (event) => {
        const input = event.target;
        if (!input.matches('.ad-upload__box input[type=file]')) { return; }
        const box = input.closest('.ad-upload__box');
        const file = input.files[0];
        if (!file) { return; }
        let img = $('img', box);
        if (!img) {
            img = document.createElement('img');
            img.alt = '';
            box.prepend(img);
        }
        img.src = URL.createObjectURL(file);
        box.classList.add('has-image');
        const name = $('.ad-upload__name', box.closest('.ad-upload'));
        if (name) { name.textContent = file.name; }
    });

    /* ---------- Leaving a page ---------- */
    document.addEventListener('livewire:navigating', () => {
        // The post editor is rebuilt on the next edit page
        if (window.CKEDITOR) {
            Object.values(window.CKEDITOR.instances).forEach((editor) => {
                try { editor.destroy(true); } catch (e) { /* already gone */ }
            });
        }
    });

    onPage(() => {
        $$('[data-counter]').forEach(updateCounter);
    });
})();
