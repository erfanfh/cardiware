const DISPLAY_DURATION = 7200;
const SEEN_STORAGE_KEY = 'loyalty-earned-toast-seen';
const MAX_SEEN_ITEMS = 30;

const numberFormatter = new Intl.NumberFormat('fa-IR');

function loadSeenIds() {
    try {
        const value = JSON.parse(window.localStorage.getItem(SEEN_STORAGE_KEY) || '[]');

        return Array.isArray(value) ? value.filter((id) => typeof id === 'string') : [];
    } catch {
        return [];
    }
}

function rememberSeenId(id) {
    const ids = loadSeenIds().filter((seenId) => seenId !== id);
    ids.push(id);

    try {
        window.localStorage.setItem(
            SEEN_STORAGE_KEY,
            JSON.stringify(ids.slice(-MAX_SEEN_ITEMS)),
        );
    } catch {
        // Storage can be unavailable in strict privacy modes; the server-side
        // claim and ledger idempotency still prevent normal duplicate toasts.
    }
}

function buildMessage(notification) {
    const points = numberFormatter.format(notification.points);

    if (notification.type === 'daily_visit') {
        return `به‌خاطر اولین ورود امروز، ${points} امتیاز به حسابت اضافه شد.`;
    }

    const seconds = numberFormatter.format(notification.required_seconds || 1);

    return `برای ${notification.action_label} به مدت ${seconds} ثانیه، ${points} امتیاز به حسابت اضافه شد.`;
}

function isValidNotification(notification) {
    return notification
        && typeof notification.id === 'string'
        && notification.id.length > 0
        && Number(notification.points) > 0
        && ['daily_visit', 'time_spent'].includes(notification.type);
}

export default function initLoyaltyEarnedToast() {
    const root = document.querySelector('[data-loyalty-earned-toast]');

    if (!root) return;

    const card = root.querySelector('[data-loyalty-earned-card]');
    const title = root.querySelector('[data-loyalty-earned-title]');
    const message = root.querySelector('[data-loyalty-earned-message]');
    const points = root.querySelector('[data-loyalty-earned-points]');
    const balance = root.querySelector('[data-loyalty-earned-balance]');
    const progress = root.querySelector('[data-loyalty-earned-progress]');
    const closeButton = root.querySelector('[data-loyalty-earned-close]');

    if (!card || !title || !message || !points || !balance || !progress || !closeButton) {
        return;
    }

    const queue = [];
    let visible = false;
    let hiding = false;
    let hideTimer = null;

    const hide = () => {
        if (!visible || hiding) return;

        hiding = true;
        window.clearTimeout(hideTimer);
        hideTimer = null;
        card.classList.remove('is-visible');

        window.setTimeout(() => {
            card.hidden = true;
            visible = false;
            hiding = false;

            const next = queue.shift();
            if (next) show(next);
        }, 240);
    };

    const show = (notification) => {
        visible = true;
        hiding = false;
        title.textContent = notification.title;
        message.textContent = buildMessage(notification);
        points.textContent = numberFormatter.format(notification.points);
        balance.textContent = numberFormatter.format(notification.balance_after);

        card.hidden = false;
        progress.style.animation = 'none';
        void progress.offsetWidth;
        progress.style.animation = '';

        window.requestAnimationFrame(() => card.classList.add('is-visible'));
        hideTimer = window.setTimeout(hide, DISPLAY_DURATION);
    };

    const enqueue = (notification) => {
        if (!isValidNotification(notification)) return;
        if (loadSeenIds().includes(notification.id)) return;

        rememberSeenId(notification.id);

        if (visible) {
            queue.push(notification);
            return;
        }

        show(notification);
    };

    closeButton.addEventListener('click', hide);
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') hide();
    });
    window.addEventListener('loyalty:earned', (event) => enqueue(event.detail));

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const pendingUrl = root.dataset.pendingUrl;
    const pendingCookie = root.dataset.pendingCookie;

    if (!csrf || !pendingUrl || !pendingCookie) return;

    const hasPendingNotification = document.cookie
        .split(';')
        .some((part) => part.trim().startsWith(`${pendingCookie}=`));

    if (!hasPendingNotification) return;

    fetch(pendingUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    }).then((response) => {
        if (!response.ok) throw new Error(`Unable to claim loyalty notifications (${response.status})`);

        return response.json();
    }).then((payload) => {
        (payload.notifications || []).forEach(enqueue);
    }).catch(() => {
        // Earning points must never be coupled to a presentational failure.
    });
}
