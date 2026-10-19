import test from 'node:test';
import assert from 'node:assert/strict';
import {randomUUID} from 'node:crypto';
import {repeaterEditor} from '../../../resources/js/admin/repeater-editor.js';

test('repeater clones get independent editor identities and are cleaned up on rebuild', () => {
    const initialized = [];
    const destroyed = [];
    globalThis.window = {
        crypto: {randomUUID},
        AdminEditor: {init: el => initialized.push(el), destroy: el => destroyed.push(el)},
    };
    const makeClone = () => {
        const source = {id: 'cloned-id', dataset: {editorKey: 'cloned-key'}};
        const pending = [];
        const component = Object.assign(repeaterEditor(), {
            $el: {isConnected: true, querySelector: () => source},
            $nextTick: callback => pending.push(callback),
        });
        component.init();
        return {source, component, flush: () => pending.forEach(callback => callback())};
    };
    const first = makeClone();
    const second = makeClone();
    assert.notEqual(first.source.id, second.source.id);
    assert.equal(first.source.id, first.source.dataset.editorKey);
    assert.equal(initialized.length, 0);
    first.flush();
    second.flush();
    assert.deepEqual(initialized, [first.component.$el, second.component.$el]);
    first.component.destroy();
    assert.deepEqual(destroyed, [first.component.$el]);

    const removedBeforeInit = makeClone();
    removedBeforeInit.component.$el.isConnected = false;
    removedBeforeInit.flush();
    assert.equal(initialized.length, 2);
});
