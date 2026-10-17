/* ==========================================================================
   BriefMedia — admin panel interactions (no dependencies)
   ========================================================================== */
(function () {
    'use strict';

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

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

    /* ---------- Theme ---------- */
    $$('[data-theme-toggle]').forEach((button) => button.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try { localStorage.setItem('bm-admin-theme', next); } catch (e) { /* storage unavailable */ }
    }));

    /* ---------- Sidebar (mobile) ---------- */
    const side = $('.ad-side');
    const backdrop = $('.ad-backdrop');
    const toggleSide = (open) => {
        if (!side) { return; }
        side.classList.toggle('is-open', open);
        backdrop && backdrop.classList.toggle('is-open', open);
    };
    $$('[data-side-toggle]').forEach((el) => el.addEventListener('click', () => toggleSide(!side.classList.contains('is-open'))));
    backdrop && backdrop.addEventListener('click', () => toggleSide(false));

    /* ---------- Dropdowns ---------- */
    document.addEventListener('click', (event) => {
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

    /* ---------- Tabs ---------- */
    $$('[data-tabs]').forEach((group) => {
        const tabs = $$('[data-tab]', group);
        tabs.forEach((tab) => tab.addEventListener('click', () => {
            tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
            $$('.ad-tab-pane', group).forEach((pane) => pane.classList.toggle('is-active', pane.id === tab.dataset.tab));
        }));
    });

    /* ---------- Client-side table filter ---------- */
    $$('[data-table-filter]').forEach((input) => {
        const table = $(input.dataset.tableFilter);
        if (!table) { return; }
        const segment = input.closest('.ad-toolbar') ? $('.ad-segment', input.closest('.ad-toolbar')) : null;
        let status = 'all';

        const apply = () => {
            const term = input.value.trim().toLowerCase();
            let visible = 0;
            $$('tbody tr[data-row]', table).forEach((row) => {
                const matchesText = !term || row.textContent.toLowerCase().includes(term);
                const matchesStatus = status === 'all' || row.dataset.status === status;
                row.hidden = !(matchesText && matchesStatus);
                if (!row.hidden) { visible++; }
            });
            const empty = $('[data-filter-empty]', table);
            if (empty) { empty.hidden = visible > 0; }
        };

        input.addEventListener('input', apply);
        if (segment) {
            $$('button', segment).forEach((button) => button.addEventListener('click', () => {
                $$('button', segment).forEach((b) => b.classList.toggle('is-active', b === button));
                status = button.dataset.status;
                apply();
            }));
        }
    });

    /* ---------- Image upload previews ---------- */
    $$('.ad-upload__box input[type=file]').forEach((input) => {
        input.addEventListener('change', () => {
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
    });

    /* ---------- Character counters ---------- */
    $$('[data-counter]').forEach((field) => {
        const output = document.getElementById(field.dataset.counter);
        const limit = parseInt(field.dataset.recommended || '0', 10);
        const update = () => {
            if (!output) { return; }
            output.textContent = field.value.length;
            output.closest('.ad-counter') && output.closest('.ad-counter').classList.toggle('is-over', limit > 0 && field.value.length > limit);
        };
        field.addEventListener('input', update);
        update();
    });
})();
