const POLL_INTERVAL = 30_000;
const DISPLAY_DURATION = 6_000;
const DISPLAY_GAP = 24_000;
const EXIT_DURATION = 240;
const SEEN_KEY = 'mw-recent-purchases-seen-v1';
const DISMISSED_KEY = 'mw-recent-purchases-dismissed-until';
const blockers = '[data-purchase-notification-blocker], [data-loyalty-earned-card], [data-product-sticky-buy-bar], [data-mw-popup], [role="dialog"], dialog[open]';

function readStorage(key, fallback) {
    try {
        return JSON.parse(window.localStorage.getItem(key)) ?? fallback;
    } catch {
        return fallback;
    }
}

function writeStorage(key, value) {
    try {
        window.localStorage.setItem(key, JSON.stringify(value));
    } catch {
        // In-memory state still prevents repeats when browser storage is unavailable.
    }
}

export function validPurchase(purchase, now, origin) {
    if (!purchase || typeof purchase.id !== 'string' || !/^[a-f0-9]{64}$/.test(purchase.id)
        || typeof purchase.buyer !== 'string' || !purchase.buyer.trim()
        || typeof purchase.product !== 'string' || !purchase.product.trim()
        || !Number.isFinite(purchase.purchased_at) || !Number.isFinite(purchase.expires_at)
        || purchase.purchased_at > now || purchase.expires_at <= now
        || purchase.expires_at <= purchase.purchased_at || typeof purchase.url !== 'string') return false;

    try {
        const url = new URL(purchase.url, origin);
        return url.origin === origin && ['http:', 'https:'].includes(url.protocol);
    } catch {
        return false;
    }
}

export function purchaseTime(purchasedAt, now) {
    const minutes = Math.max(0, Math.floor((now - purchasedAt) / 60));
    return minutes === 0 ? 'Less than a minute ago' : `${new Intl.NumberFormat('en-US').format(minutes)} minutes ago`;
}

export default function initRecentPurchases() {
    const root = document.querySelector('[data-recent-purchases]');
    if (!root || root.dataset.initialized || Number(readStorage(DISMISSED_KEY, 0)) > Date.now()) return;

    const buyer = root.querySelector('[data-purchase-buyer]');
    const product = root.querySelector('[data-purchase-product]');
    const time = root.querySelector('[data-purchase-time]');
    const close = root.querySelector('[data-purchase-close]');
    if (!buyer || !product || !time || !close) return;
    root.dataset.initialized = 'true';

    const seen = new Map();
    let purchases = [];
    let current = null;
    let hideAt = 0;
    let nextDisplayAt = 0;
    let nextPollAt = Date.now() + 8_000 + Math.random() * 2_000;
    let failures = 0;
    let inFlight = false;
    let controller = null;
    let timer = null;
    let stopped = false;
    let exitTimer = null;

    const refreshSeen = () => {
        const stored = readStorage(SEEN_KEY, []);
        if (Array.isArray(stored)) {
            stored.forEach((entry) => {
                if (Array.isArray(entry) && typeof entry[0] === 'string' && Number.isFinite(entry[1])) {
                    seen.set(entry[0], entry[1]);
                }
            });
        }
        for (const [id, expires] of seen) {
            if (expires <= Date.now() / 1000) seen.delete(id);
        }
    };

    const hide = (immediate = false) => {
        window.clearTimeout(exitTimer);
        current = null;
        nextDisplayAt = Date.now() + DISPLAY_GAP;
        const finish = () => {
            root.hidden = true;
            root.dataset.state = 'hidden';
            exitTimer = null;
        };
        if (immediate || root.hidden || window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
            finish();
        } else {
            root.dataset.state = 'leaving';
            exitTimer = window.setTimeout(finish, EXIT_DURATION);
        }
    };

    const isBlocked = () => document.body.style.overflow === 'hidden'
        || [...document.querySelectorAll(blockers)].some((element) => {
            if (!element.getClientRects().length) return false;
            const style = window.getComputedStyle(element);
            return style.visibility !== 'hidden' && style.opacity !== '0';
        });

    const poll = async () => {
        inFlight = true;
        nextPollAt = Date.now() + POLL_INTERVAL;
        controller = new AbortController();
        const timeout = window.setTimeout(() => controller?.abort(), 8_000);
        try {
            const response = await fetch(root.dataset.url, {
                headers: { Accept: 'application/json' },
                credentials: 'omit',
                mode: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
            });
            if (!response.ok) throw new Error('Recent purchases unavailable');
            const payload = await response.json();
            if (!Array.isArray(payload.purchases)) throw new Error('Invalid purchases');
            purchases = payload.purchases
                .filter((purchase) => validPurchase(purchase, Date.now() / 1000, window.location.origin))
                .slice(0, 5);
            failures = 0;
            // A refunded or removed purchase disappears on the next refresh.
            if (current && !purchases.some((purchase) => purchase.id === current.id)) hide(true);
        } catch {
            if (!document.hidden && !stopped) {
                failures = Math.min(failures + 1, 4);
                nextPollAt = Date.now() + Math.min(300_000, POLL_INTERVAL * 2 ** failures);
                purchases = [];
                if (current) hide(true);
            }
        } finally {
            window.clearTimeout(timeout);
            inFlight = false;
            controller = null;
        }
    };

    const tick = () => {
        if (stopped || document.hidden) return;
        if (Number(readStorage(DISMISSED_KEY, 0)) > Date.now()) {
            stop();
            return;
        }
        if (!inFlight && Date.now() >= nextPollAt) void poll();
        if (current) {
            if (isBlocked()) hide(true);
            else if (current.expires_at <= Date.now() / 1000
                || (Date.now() >= hideAt && !root.matches(':hover, :focus-within'))) hide();
            return;
        }
        if (!purchases.length || Date.now() < nextDisplayAt) return;
        refreshSeen();
        const purchase = purchases.find((item) => item.expires_at > Date.now() / 1000 && !seen.has(item.id));
        if (!purchase || isBlocked()) return;

        buyer.textContent = purchase.buyer;
        product.textContent = purchase.product;
        product.href = purchase.url;
        time.textContent = purchaseTime(purchase.purchased_at, Date.now() / 1000);
        current = purchase;
        window.clearTimeout(exitTimer);
        root.dataset.state = 'visible';
        root.hidden = false;
        hideAt = Date.now() + DISPLAY_DURATION;
        seen.set(purchase.id, purchase.expires_at);
        writeStorage(SEEN_KEY, [...seen].slice(-100));
    };

    const onVisibility = () => {
        window.clearInterval(timer);
        if (document.hidden) {
            controller?.abort();
            if (!root.hidden) hide(true);
        } else if (!stopped) {
            nextPollAt = Math.max(nextPollAt, Date.now());
            timer = window.setInterval(tick, 1_000);
            tick();
        }
    };

    const stop = () => {
        stopped = true;
        hide();
        controller?.abort();
        window.clearInterval(timer);
        document.removeEventListener('visibilitychange', onVisibility);
    };

    close.addEventListener('click', () => {
        writeStorage(DISMISSED_KEY, Date.now() + 15 * 60_000);
        stop();
    });
    document.addEventListener('visibilitychange', onVisibility);
    window.addEventListener('pagehide', () => {
        window.clearInterval(timer);
        controller?.abort();
        if (!root.hidden) hide(true);
    });
    window.addEventListener('pageshow', onVisibility);
    onVisibility();
}
