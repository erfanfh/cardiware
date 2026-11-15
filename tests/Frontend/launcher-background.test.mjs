import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const template = readFileSync(new URL('../../resources/views/front/launcher/landing.blade.php', import.meta.url), 'utf8');
const source = template.match(/\(function \(\) \{[\s\S]*?\}\)\(\);/)[0]
    .replace(/\{!![\s\S]*?!!\}/g, '24');

function setup(reducedMotion = false) {
    const frames = [];
    const events = new Map();
    let draws = 0;
    const context = {
        setTransform() {}, clearRect() { draws++; }, beginPath() {},
        moveTo() {}, lineTo() {}, stroke() {}, arc() {}, fill() {},
    };
    const canvas = {offsetWidth: 320, offsetHeight: 180, getContext: () => context};
    const document = {
        hidden: false,
        getElementById: () => canvas,
        addEventListener: (name, callback) => events.set(name, callback),
    };
    vm.runInNewContext(source, {
        document,
        window: {
            matchMedia: () => ({matches: reducedMotion}),
            addEventListener: (name, callback) => events.set(name, callback),
        },
        requestAnimationFrame: callback => frames.push(callback),
    });
    return {frames, events, document, drawCount: () => draws};
}

test('launcher background keeps one animation loop and pauses drawing while hidden', () => {
    const state = setup();
    for (let frame = 1; frame <= 120; frame++) {
        assert.equal(state.frames.length, 1);
        state.frames.shift()(frame * 50);
    }
    const before = state.drawCount();
    state.document.hidden = true;
    state.events.get('visibilitychange')();
    state.frames.shift()(6100);
    assert.equal(state.drawCount(), before);
    state.document.hidden = false;
    state.events.get('visibilitychange')();
    state.frames.shift()(6200);
    assert.equal(state.drawCount(), before + 1);
    assert.equal(state.frames.length, 1);
});

test('reduced motion draws a static background and redraws it after resizing', () => {
    const state = setup(true);
    assert.equal(state.frames.length, 0);
    const before = state.drawCount();
    state.events.get('resize')();
    assert.equal(state.drawCount(), before + 1);
    assert.equal(state.frames.length, 0);
});
