import test from 'node:test';
import assert from 'node:assert/strict';
import {analyzeReadability, countWords} from '../../../resources/js/admin/seo/readability.js';

const words = count => Array(count).fill('واژه').join(' ');
const paragraph = text => ({kind: 'paragraph', text});
const heading = text => ({kind: 'heading', text});
const check = (result, key) => result.checks.find(item => item.key === key);

test('Persian half-spaces, diacritics and punctuation are counted correctly', () => {
    assert.equal(countWords('می‌توان کتاب‌ها را آسان‌تر خواند؛ واقعاً؟ بله!'), 7);
    assert.equal(countWords('... ؟ ! — \u200c \u200f'), 0);
    assert.equal(countWords('نسخه ۲۰۲۶ برای Windows منتشر شد.'), 6);
});

test('empty and short content do not receive a misleading assessment', () => {
    assert.equal(analyzeReadability([]).state, 'empty');
    const short = analyzeReadability([paragraph(words(49))]);
    assert.equal(short.state, 'short');
    assert.equal(short.status, 'neutral');
    assert.deepEqual(short.checks, []);
    assert.equal(short.readingMinutes, 1);
    assert.equal(analyzeReadability([paragraph(words(50))]).state, 'ready');
});

test('Persian question marks split sentences but commas and semicolons do not', () => {
    const result = analyzeReadability([paragraph('نصب شد؟ بله، آماده است؛ شروع کن! عالی است. پایان')]);
    assert.equal(result.sentences, 4);
});

test('versions, decimals, URLs and email addresses do not inflate sentence counts', () => {
    const result = analyzeReadability([paragraph('نسخه 1.2.3 و ۳.۱۴ در https://example.com/file.zip است. به test@example.com بنویسید!')]);
    assert.equal(result.sentences, 2);
});

test('a short well-structured description does not need headings', () => {
    const result = analyzeReadability([paragraph(Array(6).fill(words(10) + '.').join(' '))]);
    assert.equal(result.status, 'good');
    assert.equal(check(result, 'headings').status, 'good');
});

test('long sentence ratio respects boundaries and returns actionable examples', () => {
    const assess = lengths => analyzeReadability([paragraph(lengths.map(length => words(length) + '.').join(' '))]);
    assert.equal(check(assess([25, 25, 25, 26]), 'sentences').status, 'good');
    assert.equal(check(assess([15, 15, 26]), 'sentences').status, 'improvement');
    const problem = check(assess([26, 26, 10]), 'sentences');
    assert.equal(problem.status, 'problem');
    assert.equal(problem.examples.length, 2);
});

test('paragraph checks handle 120 and 180 word boundaries', () => {
    const assess = length => check(analyzeReadability([paragraph(words(length))]), 'paragraphs');
    assert.equal(assess(120).status, 'good');
    assert.equal(assess(121).status, 'improvement');
    assert.equal(assess(180).status, 'improvement');
    assert.equal(assess(181).status, 'problem');
});

test('sections measure the introduction and the final section independently', () => {
    const result = analyzeReadability([paragraph(words(301)), heading('نصب'), paragraph(words(451))]);
    const sections = check(result, 'headings');
    assert.equal(sections.status, 'problem');
    assert.equal(sections.examples.length, 2);
    assert.match(sections.examples[0], /نصب/);
    assert.match(sections.examples[1], /ابتدای متن/);
    const split = analyzeReadability([paragraph(words(300)), heading('نصب'), paragraph(words(300))]);
    assert.equal(check(split, 'headings').status, 'good');
});

test('headings and list items are not treated as prose paragraphs', () => {
    const result = analyzeReadability([heading(words(30)), {kind: 'item', text: words(30)}, {kind: 'item', text: words(30)}]);
    assert.equal(result.words, 90);
    assert.equal(result.sentences, 2);
    assert.equal(result.paragraphs, 0);
    assert.equal(check(result, 'paragraphs'), undefined);
});

test('reading time rounds up and the issue badge counts only failing checks', () => {
    const result = analyzeReadability([paragraph(words(401))]);
    assert.equal(result.readingMinutes, 3);
    assert.equal(result.issues, 3);
    assert.equal(result.status, 'problem');
});
