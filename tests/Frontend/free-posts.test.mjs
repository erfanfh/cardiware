import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/front/home/free-posts.js', import.meta.url), 'utf8')
    .replace(/^import .*;\r?\n/gm, '');

function setup(readyState = 'complete') {
    const events = new Map();
    const hooks = new Map();
    const instances = [];
    const element = {querySelector: () => ({}), matches: selector => selector === '.mwFreeHeroCards'};
    const document = {
        readyState,
        querySelectorAll: () => [element],
        addEventListener: (event, callback) => events.set(event, callback),
    };
    class Swiper {
        constructor(el, options) {
            this.updates = 0;
            this.destroyed = false;
            this.options = options;
            el.swiper = this;
            instances.push(this);
        }
        update() { this.updates++; }
        destroy() { this.destroyed = true; }
    }
    vm.runInNewContext(source, {
        document, Swiper, Pagination: {}, Autoplay: {},
        Livewire: {hook: (event, callback) => hooks.set(event, callback)},
    });
    return {events, hooks, instances, element};
}

test('initializes when loaded after DOM ready and updates existing sliders on navigation', () => {
    const {events, instances} = setup();
    assert.equal(instances.length, 1);
    assert.equal(instances[0].options.breakpoints[1024].slidesPerView, 3);
    events.get('livewire:navigated')();
    assert.equal(instances.length, 1);
    assert.equal(instances[0].updates, 1);
});

test('waits for DOM ready and initializes only after the whole Livewire morph', () => {
    const {events, hooks, instances} = setup('loading');
    assert.equal(instances.length, 0);
    events.get('DOMContentLoaded')();
    events.get('livewire:initialized')();
    assert.equal(hooks.has('morph.updated'), false);
    hooks.get('morphed')();
    assert.equal(instances.length, 1);
    assert.equal(instances[0].updates, 1);
});

test('destroys removed sliders and their autoplay timers when tabs are replaced', () => {
    const {events, hooks, instances, element} = setup();
    events.get('livewire:initialized')();
    const parent = {matches: () => false, querySelectorAll: () => [element]};
    hooks.get('morph.removing')({el: parent});
    assert.equal(instances[0].destroyed, true);
});
