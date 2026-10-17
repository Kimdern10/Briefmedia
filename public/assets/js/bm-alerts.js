/* ==========================================================================
   BriefMedia — SweetAlert2 helpers shared by the public site and the admin panel
   window.bmToast(message, tone)   small corner notification
   window.bmConfirm(options)       "Are you sure?" dialog, resolves to true / false
   ========================================================================== */
(function () {
    'use strict';

    const css = (name, fallback) => {
        const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return value || fallback;
    };

    // Colours follow the current light / dark theme of the site or admin panel.
    const palette = () => ({
        background: css('--bm-surface', '') || css('--ad-surface', '#ffffff'),
        color: css('--bm-ink', '') || css('--ad-ink', '#15151b'),
        accent: css('--bm-accent', '') || css('--ad-accent', '#e4407f'),
        muted: css('--bm-muted', '') || css('--ad-muted', '#7a7a88'),
    });

    const icons = { success: 'success', error: 'error', warning: 'warning', info: 'info' };

    window.bmToast = function (message, tone = 'success', duration = 4000) {
        if (!window.Swal) { return; }
        const colors = palette();
        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icons[tone] || 'success',
            title: String(message),
            showConfirmButton: false,
            timer: typeof duration === 'number' ? duration : 4000,
            timerProgressBar: true,
            background: colors.background,
            color: colors.color,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', window.Swal.stopTimer);
                toast.addEventListener('mouseleave', window.Swal.resumeTimer);
            },
        });
    };

    // Older admin code calls adToast(message, tone)
    window.adToast = function (message, tone = 'success') { window.bmToast(message, tone); };

    /**
     * @param {{confirmText?: string, confirmTitle?: string, confirmButton?: string, tone?: string}} options
     *        Usually a button's dataset: data-confirm-text, data-confirm-title, data-confirm-button.
     */
    window.bmConfirm = function (options = {}) {
        const text = options.confirmText || options.text || 'Are you sure?';
        if (!window.Swal) { return Promise.resolve(window.confirm(text)); }

        const colors = palette();
        const danger = (options.tone || options.confirmTone || 'danger') === 'danger';

        return window.Swal.fire({
            icon: danger ? 'warning' : 'question',
            title: options.confirmTitle || options.title || 'Are you sure?',
            text: text,
            showCancelButton: true,
            confirmButtonText: options.confirmButton || options.button || 'Yes, continue',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: danger,
            confirmButtonColor: danger ? '#d63c3c' : colors.accent,
            cancelButtonColor: colors.muted,
            background: colors.background,
            color: colors.color,
        }).then((result) => result.isConfirmed);
    };

    // Plain forms with data-confirm-text ask first, then submit.
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('form[data-confirm-text]') || form.dataset.confirmed === '1') { return; }
        event.preventDefault();
        window.bmConfirm(form.dataset).then((ok) => {
            if (ok) { form.dataset.confirmed = '1'; form.submit(); }
        });
    }, true);
})();
