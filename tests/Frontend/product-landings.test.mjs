import test from 'node:test';
import assert from 'node:assert/strict';
import {productLandingEditor} from '../../resources/js/admin/product-landings.js';

test('moving and adding bilingual rows preserves their content and nested lists', () => {
    const editor = productLandingEditor({});
    const first = {title: {fa: 'اول', en: 'First'}, items: [], url: null};
    const second = {title: {fa: 'دوم', en: 'Second'}, items: ['value']};
    const rows = [first, second];
    editor.move(rows, 1, -1);
    assert.equal(rows[0], second);
    editor.move(rows, 0, -1);
    assert.equal(rows[0], second);
    editor.add(rows, first);
    rows[2].title.fa = 'جدید';
    rows[2].items.push('new');
    assert.equal(first.title.fa, 'اول');
    assert.deepEqual(first.items, []);
    assert.equal(rows[2].url, null);
});

test('JSON transport retains deleted lists, nulls, booleans and geographic coordinates', () => {
    const content = {items: [], link: null, enabled: false, lng: -12.5, title: {fa: 'متن', en: 'Text'}};
    const editor = productLandingEditor(content);
    assert.deepEqual(JSON.parse(JSON.stringify(editor.landingContent)), content);
    assert.equal(editor.mediaPreview('images/landings/example.svg'), '/images/landings/example.svg');
    assert.equal(editor.mediaPreview('https://example.com/media.png'), 'https://example.com/media.png');
});
