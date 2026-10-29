document.addEventListener('DOMContentLoaded', () => {
    const popups = [...document.querySelectorAll('[data-mw-popup]')];
    if (!popups.length) return;

    const due = [];
    const dismissedThisPage = new Set();
    let active = null;
    let previousFocus = null;
    let previousOverflow = '';
    let closing = false;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const setVisible = (popup, visible) => {
        const dialog = popup.querySelector('[role="dialog"]');
        popup.classList.toggle('opacity-0', !visible);
        dialog?.classList.toggle('opacity-0', !visible);
        dialog?.classList.toggle('translate-y-4', !visible);
        dialog?.classList.toggle('scale-95', !visible);
    };

    const isSuppressed = (popup) => {
        if (dismissedThisPage.has(popup.dataset.popupKey)) return true;
        try {
            return Number(localStorage.getItem(popup.dataset.popupKey) || 0) > Date.now();
        } catch {
            return false;
        }
    };

    const showNext = () => {
        if (active || closing) return;
        while (due.length) {
            const popup = due.shift();
            if (isSuppressed(popup)) continue;
            active = popup;
            previousFocus = document.activeElement;
            previousOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            popup.classList.remove('hidden');
            popup.classList.add('flex');
            window.requestAnimationFrame(() => setVisible(popup, true));
            popup.querySelector('button')?.focus();
            break;
        }
    };

    const dismiss = (popup, longTerm) => {
        if (active !== popup || closing) return;
        closing = true;
        const duration = longTerm
            ? Number(popup.dataset.popupHideDays) * 86400000
            : Number(popup.dataset.popupCloseMinutes) * 60000;
        dismissedThisPage.add(popup.dataset.popupKey);
        try {
            localStorage.setItem(popup.dataset.popupKey, String(Date.now() + duration));
        } catch { /* Browser storage may be disabled. */ }
        setVisible(popup, false);
        window.setTimeout(() => {
            popup.classList.add('hidden');
            popup.classList.remove('flex');
            document.body.style.overflow = previousOverflow;
            active = null;
            closing = false;
            previousFocus?.focus?.();
            showNext();
        }, reduceMotion ? 0 : 220);
    };

    popups.forEach((popup) => {
        if (isSuppressed(popup)) return;
        popup.addEventListener('click', (event) => {
            if (event.target === popup) dismiss(popup, false);
        });
        popup.querySelectorAll('[data-popup-dismiss]').forEach((button) => {
            button.addEventListener('click', () => dismiss(popup, button.dataset.popupDismiss === 'long'));
        });
        window.setTimeout(() => {
            due.push(popup);
            showNext();
        }, Number(popup.dataset.popupDelay) * 1000);
    });

    document.addEventListener('keydown', (event) => {
        if (!active) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            dismiss(active, false);
        } else if (event.key === 'Tab') {
            const focusable = [...active.querySelectorAll('button:not([disabled]), a[href]')];
            const first = focusable[0];
            const last = focusable.at(-1);
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });
});
