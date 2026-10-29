import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/front/popups.js', import.meta.url), 'utf8');

function browser() {
    const listeners = new Map();
    const timers = [];
    const storage = new Map();
    const classes = new Set(['hidden', 'opacity-0']);
    const document = {
        activeElement: null, body: { style: { overflow: '' } },
        addEventListener: (name, listener) => listeners.set(name, listener),
    };
    const control = (dismiss) => ({
        dataset: { popupDismiss: dismiss },
        addEventListener(name, listener) { this[name] = listener; },
        focus() { document.activeElement = this; },
    });
    const close = control('later');
    const hide = control('long');
    const link = control();
    const popup = {
        dataset: { popupKey: 'test', popupDelay: '0', popupCloseMinutes: '60', popupHideDays: '30' },
        classList: {
            toggle(name, enabled) { if (enabled) classes.add(name); else classes.delete(name); },
            add: (name) => classes.add(name), remove: (name) => classes.delete(name),
        },
        addEventListener: (name, listener) => listeners.set(`popup:${name}`, listener),
        querySelector: (selector) => selector === 'button' ? close : null,
        querySelectorAll: (selector) => selector === '[data-popup-dismiss]' ? [close, hide] :
            selector.includes('a[href]') ? [close, hide, link] : [close, hide],
    };
    document.querySelectorAll = () => [popup];
    const window = {
        matchMedia: () => ({ matches: true }), requestAnimationFrame: (callback) => callback(),
        setTimeout: (callback) => timers.push(callback),
    };
    vm.runInNewContext(source, { document, window, localStorage: {
        getItem: (key) => storage.get(key), setItem: (key, value) => storage.set(key, value),
    }, Date });
    listeners.get('DOMContentLoaded')();
    timers.shift()();
    return { document, listeners, close, hide, link, storage, timers, classes };
}

test('Tab includes action links and wraps focus in both directions', () => {
    const { document, listeners, close, hide, link } = browser();
    assert.equal(document.activeElement, close);
    let prevented = false;
    const keydown = (shiftKey) => listeners.get('keydown')({
        key: 'Tab', shiftKey, preventDefault() { prevented = true; },
    });
    hide.focus();
    keydown(false);
    assert.equal(prevented, false);
    link.focus();
    keydown(false);
    assert.equal(document.activeElement, close);
    assert.equal(prevented, true);
    keydown(true);
    assert.equal(document.activeElement, link);
});

for (const [control, duration] of [['close', 60 * 60_000], ['hide', 30 * 86_400_000]]) {
    test(`${control} preserves its suppression duration and restores scrolling`, () => {
        const state = browser();
        const before = Date.now();
        state[control].click();
        const suppressedUntil = Number(state.storage.get('test'));
        assert.ok(suppressedUntil >= before + duration);
        assert.ok(suppressedUntil <= Date.now() + duration);
        state.timers.shift()();
        assert.equal(state.classes.has('hidden'), true);
        assert.equal(state.document.body.style.overflow, '');
    });
}
