import assert from 'node:assert/strict';
import test from 'node:test';
import { setImmediate } from 'node:timers/promises';
import initRecentPurchases, { purchaseTime, validPurchase } from '../../resources/js/front/recent-purchases.js';

const origin = 'https://Cardiware.test';
const now = Date.parse('2026-10-19T12:00:00Z');
const purchase = (id = 'a') => ({
    id: id.repeat(64), buyer: 'علیرضا محمدی', product: 'پچ فیفا', url: `${origin}/products/fifa/`,
    purchased_at: now / 1000 - 60, expires_at: now / 1000 + 1740,
});

function browser(t, { hidden = false, blocked = false, unavailableStorage = false, reducedMotion = false } = {}) {
    t.mock.timers.enable({ apis: ['Date', 'setInterval', 'setTimeout'], now });
    t.mock.method(Math, 'random', () => 0);
    const originals = { window: globalThis.window, document: globalThis.document, fetch: globalThis.fetch };
    t.after(() => Object.assign(globalThis, originals));
    const storage = new Map();
    const listeners = new Map();
    const fields = new Map(['buyer', 'product', 'time', 'close'].map((field) => [field, {
        textContent: '',
        addEventListener: (name, fn) => listeners.set(`${field}:${name}`, fn),
    }]));
    const root = {
        hidden: true, dataset: { url: '/notifications/recent-purchases' },
        querySelector: (selector) => fields.get(selector.match(/data-purchase-(\w+)/)?.[1]),
        matches: () => false,
    };
    const state = { blocked, payload: [purchase()], calls: [], fail: false };
    globalThis.document = {
        hidden, body: { style: {} },
        querySelector: () => root,
        querySelectorAll: () => state.blocked ? [{ getClientRects: () => [1] }] : [],
        addEventListener: (name, fn) => listeners.set(name, fn),
        removeEventListener: (name) => listeners.delete(name),
    };
    globalThis.window = {
        location: { origin }, setInterval, clearInterval, setTimeout, clearTimeout,
        matchMedia: () => ({ matches: reducedMotion }),
        localStorage: {
            getItem: (key) => { if (unavailableStorage) throw new Error('Storage disabled'); return storage.get(key) ?? null; },
            setItem: (key, value) => { if (unavailableStorage) throw new Error('Storage disabled'); storage.set(key, value); },
        },
        getComputedStyle: () => ({ visibility: 'visible', opacity: '1' }),
        addEventListener: (name, fn) => listeners.set(name, fn),
    };
    globalThis.fetch = async (url, options) => {
        state.calls.push({ url, options });
        if (state.fail) throw new Error('Offline');
        return { ok: true, json: async () => ({ purchases: state.payload }) };
    };
    initRecentPurchases();
    return { state, root, fields, listeners, storage };
}

test('accepts only fresh purchases with a same-origin HTTP link', () => {
    const value = purchase();
    assert.equal(validPurchase(value, now / 1000, origin), true);
    for (const patch of [
        { url: 'javascript:alert(1)' }, { url: 'https://evil.test/product' },
        { id: '123' }, { buyer: '' }, { purchased_at: now / 1000 + 1 },
        { expires_at: now / 1000 }, { expires_at: 'tomorrow' },
    ]) {
        assert.equal(validPurchase({ ...value, ...patch }, now / 1000, origin), false);
    }
    assert.equal(purchaseTime(now / 1000 - 30, now / 1000), 'کمتر از یک دقیقه پیش');
    assert.equal(purchaseTime(now / 1000 - 180, now / 1000), '۳ دقیقه پیش');
});

test('delays the first request, displays safe text, and never replays the same order', async (t) => {
    const { state, root, fields } = browser(t, { unavailableStorage: true });
    state.payload[0].product = '<img src=x onerror=alert(1)>';
    t.mock.timers.tick(7_000);
    assert.equal(state.calls.length, 0);
    t.mock.timers.tick(1_000);
    await setImmediate();
    assert.equal(state.calls.length, 1);
    assert.equal(state.calls[0].options.credentials, 'omit');
    t.mock.timers.tick(1_000);
    assert.equal(root.hidden, false);
    assert.equal(fields.get('product').textContent, '<img src=x onerror=alert(1)>');
    t.mock.timers.tick(6_000);
    assert.equal(root.dataset.state, 'leaving');
    assert.equal(root.hidden, false);
    t.mock.timers.tick(240);
    assert.equal(root.hidden, true);
    t.mock.timers.tick(30_000);
    await setImmediate();
    t.mock.timers.tick(1_000);
    assert.equal(root.hidden, true);
});

test('does not fetch or display while the tab is hidden', async (t) => {
    const { state, listeners, root } = browser(t, { hidden: true });
    t.mock.timers.tick(60_000);
    assert.equal(state.calls.length, 0);
    document.hidden = false;
    listeners.get('visibilitychange')();
    await setImmediate();
    t.mock.timers.tick(1_000);
    assert.equal(state.calls.length, 1);
    assert.equal(root.hidden, false);
    document.hidden = true;
    listeners.get('visibilitychange')();
    t.mock.timers.tick(60_000);
    assert.equal(root.hidden, true);
    assert.equal(state.calls.length, 1);
});

test('waits for other floating UI and removes the card when a modal opens', async (t) => {
    const { state, root } = browser(t, { blocked: true });
    t.mock.timers.tick(8_000);
    await setImmediate();
    t.mock.timers.tick(1_000);
    assert.equal(root.hidden, true);
    state.blocked = false;
    t.mock.timers.tick(1_000);
    assert.equal(root.hidden, false);
    state.blocked = true;
    t.mock.timers.tick(1_000);
    assert.equal(root.hidden, true);
});

test('closing stops requests and suppresses notifications after navigation', async (t) => {
    const { state, root, listeners } = browser(t);
    t.mock.timers.tick(8_000);
    await setImmediate();
    t.mock.timers.tick(1_000);
    assert.equal(root.hidden, false);
    listeners.get('close:click')();
    assert.equal(root.dataset.state, 'leaving');
    t.mock.timers.tick(240);
    assert.equal(root.hidden, true);
    delete root.dataset.initialized;
    initRecentPurchases();
    t.mock.timers.tick(120_000);
    assert.equal(state.calls.length, 1);
    assert.equal(root.dataset.initialized, undefined);
});

test('failed requests back off instead of retrying every poll interval', async (t) => {
    const { state } = browser(t);
    state.fail = true;
    t.mock.timers.tick(8_000);
    await setImmediate();
    assert.equal(state.calls.length, 1);
    t.mock.timers.tick(59_000);
    assert.equal(state.calls.length, 1);
    t.mock.timers.tick(1_000);
    await setImmediate();
    assert.equal(state.calls.length, 2);
});

test('an expired purchase waiting behind a modal is never shown', async (t) => {
    const { state, root } = browser(t, { blocked: true });
    state.payload[0].expires_at = now / 1000 + 10;
    t.mock.timers.tick(8_000);
    await setImmediate();
    t.mock.timers.tick(4_000);
    state.blocked = false;
    t.mock.timers.tick(1_000);
    assert.equal(root.hidden, true);
});

test('reduced motion closes immediately without waiting for an exit animation', async (t) => {
    const { root, listeners } = browser(t, { reducedMotion: true });
    t.mock.timers.tick(8_000);
    await setImmediate();
    t.mock.timers.tick(1_000);
    assert.equal(root.hidden, false);
    listeners.get('close:click')();
    assert.equal(root.hidden, true);
    assert.equal(root.dataset.state, 'hidden');
});
