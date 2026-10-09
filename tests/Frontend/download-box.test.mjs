import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/front/post-detail/download-box.js', import.meta.url), 'utf8');
const trackingUrl = '/dl/e/test-entry/click?signature=test-signature';

function createBlock({ beacon, fetch = () => Promise.resolve(), now = 10000 } = {}) {
    const context = vm.createContext({
        window: {},
        navigator: beacon ? { sendBeacon: beacon } : {},
        fetch,
        Date: { now: () => now },
    });
    vm.runInContext(source, context);

    return context.window.downloadsBlock();
}

test('queues a beacon without waiting or cancelling the download click', () => {
    const calls = [];
    const block = createBlock({
        beacon: (url) => { calls.push(url); return true; },
        fetch: () => assert.fail('No fallback is needed after a queued beacon'),
    });
    const result = block.trackClick(trackingUrl, {
        button: 0,
        preventDefault: () => assert.fail('Direct download navigation must not be cancelled'),
    });

    assert.equal(result, undefined);
    assert.deepEqual(calls, [trackingUrl]);
});

test('uses a keepalive POST without awaiting the fallback request', () => {
    const calls = [];
    const block = createBlock({ fetch: (...args) => { calls.push(args); return new Promise(() => {}); } });

    assert.equal(block.trackClick(trackingUrl), undefined);
    assert.equal(calls.length, 1);
    assert.equal(calls[0][0], trackingUrl);
    assert.equal(calls[0][1].method, 'POST');
    assert.equal(calls[0][1].keepalive, true);
    assert.equal(calls[0][1].credentials, 'omit');
    assert.equal(calls[0][1].mode, 'same-origin');
});

test('falls back if the browser refuses to queue a beacon', () => {
    let calls = 0;
    const block = createBlock({ beacon: () => false, fetch: () => { calls++; return Promise.resolve(); } });

    block.trackClick(trackingUrl);
    assert.equal(calls, 1);
});

test('falls back if sendBeacon throws', () => {
    let calls = 0;
    const block = createBlock({
        beacon: () => { throw new Error('Blocked beacon'); },
        fetch: () => { calls++; return Promise.resolve(); },
    });

    assert.doesNotThrow(() => block.trackClick(trackingUrl));
    assert.equal(calls, 1);
});

test('ignores synchronous and asynchronous tracking errors', async () => {
    const syncBlock = createBlock({ fetch: () => { throw new Error('Blocked fetch'); } });
    assert.doesNotThrow(() => syncBlock.trackClick(trackingUrl));

    const asyncBlock = createBlock({ fetch: () => Promise.reject(new Error('Offline')) });
    assert.doesNotThrow(() => asyncBlock.trackClick(trackingUrl));
    await new Promise((resolve) => setImmediate(resolve));
});

test('suppresses rapid duplicate clicks on the same entry but counts another entry', () => {
    const calls = [];
    const block = createBlock({ beacon: (url) => { calls.push(url); return true; } });

    block.trackClick(trackingUrl);
    block.trackClick(trackingUrl);
    block.trackClick('/dl/e/another-entry/click?signature=another');
    assert.equal(calls.length, 2);
});

test('allows middle clicks but ignores right clicks and empty tracking URLs', () => {
    const calls = [];
    const block = createBlock({ beacon: (url) => { calls.push(url); return true; } });

    block.trackClick(trackingUrl, { button: 2 });
    block.trackClick('');
    block.trackClick(trackingUrl, { button: 1 });
    assert.deepEqual(calls, [trackingUrl]);
});
