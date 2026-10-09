import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../../resources/js/admin/edit-lock.js', import.meta.url), 'utf8');

function page() {
    const documentEvents = new Map();
    const windowEvents = new Map();
    const intervals = new Map();
    const intervalDelays = [];
    const requests = [];
    const responses = [];
    const button = { disabled: false };
    const status = { textContent: 'ویرایش در اختیار شماست', classList: { toggle() {} } };
    const form = {
        dataset: { renewUrl: '/renew', releaseUrl: '/release' },
        nativeSubmissions: 0, sharedSubmissions: 0, listeners: [],
        querySelector(selector) { return { value: selector === '[name="_token"]' ? 'csrf' : 'editor-token' }; },
        querySelectorAll() { return [button]; },
        addEventListener(type, listener) { if (type === 'submit') this.listeners.push(listener); },
        requestSubmit(submitter) {
            const event = submitEvent(submitter);
            this.listeners.forEach(listener => { listener(event); });
            if (!event.stopped) this.sharedSubmissions++;
            if (!event.defaultPrevented) this.nativeSubmissions++;
        },
    };
    let timerId = 0;
    const context = vm.createContext({
        document: {
            visibilityState: 'visible',
            querySelector: selector => selector === '[data-edit-lock]' ? form : status,
            addEventListener: (type, fn) => documentEvents.set(type, fn),
        },
        window: { addEventListener: (type, fn) => windowEvents.set(type, fn) },
        FormData: class extends Map {}, AbortSignal,
        setInterval: (fn, delay) => { intervalDelays.push(delay); intervals.set(++timerId, fn); return timerId; },
        clearInterval: id => intervals.delete(id),
        fetch: async (url, options) => {
            requests.push({ url, options });
            const response = responses.shift() ?? 204;
            if (response instanceof Error) throw response;
            if (typeof response === 'function') return response();
            return { ok: response === 204, status: response, json: async () => ({ message: 'دسترسی پایان یافته' }) };
        },
    });
    vm.runInContext(source, context);
    documentEvents.get('DOMContentLoaded')();

    return {
        button, status, form, requests, responses, intervals, intervalDelays,
        submit: () => form.listeners[0](submitEvent(button)),
        tick: () => [...intervals.values()][0](),
        event: (type, event = {}) => windowEvents.get(type)(event),
        flush: () => new Promise(resolve => setImmediate(resolve)),
    };
}

function submitEvent(submitter) {
    return {
        submitter, defaultPrevented: false, stopped: false,
        preventDefault() { this.defaultPrevented = true; },
        stopImmediatePropagation() { this.stopped = true; },
    };
}

test('a save renews first and never releases the lock while navigating to the response', async () => {
    const p = page();
    await p.submit();
    assert.equal(p.form.nativeSubmissions, 1);
    assert.equal(p.form.sharedSubmissions, 1);
    assert.equal(p.requests[0].url, '/renew');
    p.event('pagehide');
    assert.equal(p.requests.length, 1);
});

test('a lost lock keeps edits on the page and prevents submission and further renewals', async () => {
    const p = page();
    p.responses.push(423);
    await p.submit();
    assert.equal(p.form.nativeSubmissions, 0);
    assert.equal(p.form.sharedSubmissions, 0);
    assert.equal(p.button.disabled, true);
    await p.tick();
    assert.equal(p.requests.length, 1);
    assert.equal(p.status.textContent, 'دسترسی پایان یافته');
});

test('network failure blocks saving and reconnecting restores the save button', async () => {
    const p = page();
    p.responses.push(new Error('offline'));
    await p.submit();
    assert.equal(p.form.nativeSubmissions, 0);
    assert.equal(p.button.disabled, true);
    await p.event('online');
    assert.equal(p.button.disabled, false);
    await p.submit();
    assert.equal(p.form.nativeSubmissions, 1);
});

test('normal navigation releases only this pages token with a CSRF token', async () => {
    const p = page();
    p.event('pagehide');
    await p.flush();
    assert.equal(p.intervals.size, 0);
    assert.equal(p.requests[0].url, '/release');
    assert.equal(p.requests[0].options.keepalive, true);
    assert.equal(p.requests[0].options.body.get('_token'), 'csrf');
    assert.equal(p.requests[0].options.body.get('edit_lock_token'), 'editor-token');
});

test('returning from browser cache rechecks ownership and restarts heartbeat', async () => {
    const p = page();
    p.event('pagehide');
    p.responses.push(423);
    p.event('pageshow', { persisted: true });
    await p.flush();
    assert.equal(p.intervals.size, 1);
    assert.equal(p.requests.at(-1).url, '/renew');
    assert.equal(p.button.disabled, true);
});

test('multiple clicks while checking ownership result in a single save', async () => {
    const p = page();
    let finish;
    p.responses.push(() => new Promise(resolve => { finish = resolve; }));
    const pending = p.submit();
    await p.submit();
    assert.equal(p.requests.length, 1);
    finish({ ok: true, status: 204 });
    await pending;
    assert.equal(p.form.nativeSubmissions, 1);
});

test('a canceled resubmission keeps heartbeats running', async () => {
    const p = page();
    p.form.listeners.push(event => event.preventDefault());
    await p.submit();
    assert.equal(p.form.nativeSubmissions, 0);
    await p.tick();
    assert.equal(p.requests.length, 2);
});

test('heartbeat runs every fifteen seconds for the one-minute lock', () => {
    const p = page();
    assert.deepEqual(p.intervalDelays, [15000]);
});
