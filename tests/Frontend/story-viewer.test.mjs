import test from 'node:test';
import assert from 'node:assert/strict';
import storyViewer from '../../resources/js/front/story-viewer.js';

function setup(t) {
    let now = 1000;
    let interval = null;
    const opener = { focused: false, focus() { this.focused = true; } };
    const closeButton = { focused: false, focus() { this.focused = true; } };
    t.mock.method(Date, 'now', () => now);
    t.mock.method(globalThis, 'setInterval', callback => { interval = callback; return 1; });
    t.mock.method(globalThis, 'clearInterval', () => { interval = null; });
    const original = globalThis.document;
    globalThis.document = { activeElement: opener, body: { style: { overflow: 'auto' } } };
    t.after(() => { globalThis.document = original; });
    const viewer = storyViewer([{ id: 1, durationMs: 3000 }, { id: 2, durationMs: 6000 }]);
    viewer.$refs = { closeButton };
    viewer.$nextTick = callback => callback();
    return {
        viewer, opener, closeButton,
        advance(ms) { now += ms; interval?.(); },
        hasTimer() { return interval !== null; },
    };
}

test('uses each duration, updates progress and closes after the last item', t => {
    const { viewer, advance, hasTimer, opener, closeButton } = setup(t);
    viewer.openByIndex(0);
    assert.equal(document.body.style.overflow, 'hidden');
    assert.equal(closeButton.focused, true);
    advance(1500);
    assert.equal(viewer.progressStyle(0), 'width:50%');
    advance(1500);
    assert.equal(viewer.index, 1);
    assert.equal(viewer.progressStyle(0), 'width:100%');
    advance(3000);
    assert.equal(viewer.progressStyle(1), 'width:50%');
    advance(3000);
    assert.equal(viewer.isOpen, false);
    assert.equal(hasTimer(), false);
    assert.equal(document.body.style.overflow, 'auto');
    assert.equal(opener.focused, true);
});

test('holding pauses time and release preserves an explicitly paused state', t => {
    const { viewer, advance } = setup(t);
    viewer.openByIndex(0);
    advance(1000);
    viewer.hold();
    advance(20000);
    assert.equal(viewer.elapsedMs, 1000);
    viewer.release();
    advance(500);
    assert.equal(viewer.elapsedMs, 1500);
    viewer.pause();
    viewer.hold();
    viewer.release();
    assert.equal(viewer.paused, true);
    viewer.resume();
    advance(1500);
    assert.equal(viewer.index, 1);
});

test('invalid indexes do nothing and destruction restores focus and clears timers', t => {
    const { viewer, advance, hasTimer } = setup(t);
    viewer.openByIndex(-1);
    viewer.openByIndex(99);
    assert.equal(viewer.isOpen, false);
    assert.equal(hasTimer(), false);
    viewer.openByIndex(1);
    advance(1000);
    viewer.prev();
    assert.equal(viewer.index, 0);
    assert.equal(viewer.elapsedMs, 0);
    viewer.destroy();
    assert.equal(hasTimer(), false);
    assert.equal(document.body.style.overflow, 'auto');
});

test('keyboard navigation follows RTL and Escape closes only the active viewer', t => {
    const { viewer } = setup(t);
    let prevented = 0;
    const key = value => viewer.handleKeydown({ key: value, preventDefault() { prevented++; } });
    key('ArrowLeft');
    assert.equal(prevented, 0);
    viewer.openByIndex(0);
    key('ArrowLeft');
    assert.equal(viewer.index, 1);
    key('ArrowRight');
    assert.equal(viewer.index, 0);
    key('Escape');
    assert.equal(viewer.isOpen, false);
    assert.equal(prevented, 3);
});

test('returning to a visible tab respects a manually paused story', t => {
    const { viewer, advance } = setup(t);
    viewer.openByIndex(0);
    advance(1000);
    document.hidden = true;
    viewer.visibilityChanged();
    advance(10000);
    document.hidden = false;
    viewer.visibilityChanged();
    advance(500);
    assert.equal(viewer.elapsedMs, 1500);
    viewer.pause();
    document.hidden = true;
    viewer.visibilityChanged();
    document.hidden = false;
    viewer.visibilityChanged();
    assert.equal(viewer.paused, true);
});

test('Tab and Shift+Tab keep focus inside the dialog', t => {
    const { viewer, closeButton } = setup(t);
    const nextButton = { focused: false, focus() { this.focused = true; }, getClientRects() { return [1]; } };
    closeButton.getClientRects = () => [1];
    viewer.$refs.dialog = {
        querySelectorAll: () => [closeButton, nextButton],
        contains: element => element === closeButton || element === nextButton,
    };
    viewer.openByIndex(0);
    let prevented = 0;
    document.activeElement = nextButton;
    viewer.handleKeydown({ key: 'Tab', preventDefault() { prevented++; } });
    assert.equal(prevented, 1);
    assert.equal(closeButton.focused, true);
    document.activeElement = closeButton;
    viewer.handleKeydown({ key: 'Tab', shiftKey: true, preventDefault() { prevented++; } });
    assert.equal(prevented, 2);
    assert.equal(nextButton.focused, true);
});

test('holding exactly at a story boundary can resume the next story', t => {
    const { viewer } = setup(t);
    viewer.openByIndex(0);
    Date.now.mock.mockImplementation(() => 4000);
    viewer.hold();
    assert.equal(viewer.index, 1);
    assert.equal(viewer.paused, true);
    viewer.release();
    assert.equal(viewer.paused, false);
});
