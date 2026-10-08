import test from 'node:test';
import assert from 'node:assert/strict';
import { localizeDisplayDigits } from '../../resources/js/localize-display-digits.js';

test('uses Persian digits beside Persian words and Latin digits beside English words', () => {
    assert.equal(localizeDisplayDigits('نسخه 12 و FIFA 23'), 'نسخه ۱۲ و FIFA 23');
    assert.equal(localizeDisplayDigits('FIFA ۲۳ و نسخه 12'), 'FIFA 23 و نسخه ۱۲');
    assert.equal(localizeDisplayDigits('2026 فارسی'), '۲۰۲۶ فارسی');
    assert.equal(localizeDisplayDigits('2026 English'), '2026 English');
});

test('uses the surrounding language for numbers without adjacent letters', () => {
    assert.equal(localizeDisplayDigits('1,234', 'fa'), '۱,۲۳۴');
    assert.equal(localizeDisplayDigits('۱٬۲۳۴', 'en'), '1٬234');
    assert.equal(localizeDisplayDigits('١٢٣', 'fa'), '۱۲۳');
});

test('keeps version and identifier numbers with their word', () => {
    assert.equal(localizeDisplayDigits('Windows 11.2'), 'Windows 11.2');
    assert.equal(localizeDisplayDigits('نسخه 2.1'), 'نسخه ۲.۱');
    assert.equal(localizeDisplayDigits('AB-12 و کد 34'), 'AB-12 و کد ۳۴');
});
