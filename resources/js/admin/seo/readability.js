// Editorial guidelines, not a language difficulty or search ranking formula.
const MIN_WORDS = 50;
const LONG_SENTENCE = 25;
const LONG_PARAGRAPH = 120;
const LONG_SECTION = 300;

export function countWords(text) {
    // Keep Persian half-spaces inside words; ignore punctuation and bidi controls.
    return (text.match(/[\p{L}\p{N}][\p{L}\p{M}\p{N}\u200c\u200d]*(?:['’][\p{L}\p{M}]+)*/gu) || []).length;
}

export function extractReadabilityBlocks(html) {
    const doc = new DOMParser().parseFromString(html || '', 'text/html');
    doc.querySelectorAll('script, style, template, noscript, iframe, object, svg, video, audio, canvas, pre, code, [hidden], [aria-hidden="true"], [data-more-marker], [data-mce-bogus="all"]').forEach(node => node.replaceWith(doc.createTextNode(' ')));
    const blocks = [];
    const blockTags = new Set(['P', 'DIV', 'SECTION', 'ARTICLE', 'BLOCKQUOTE', 'UL', 'OL', 'LI', 'TABLE', 'TR', 'TD', 'TH', 'FIGURE', 'FIGCAPTION', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'HR']);
    let text = '';
    let kind = 'paragraph';
    const flush = () => {
        const normalized = text.replace(/[\u200e\u200f\u202a-\u202e\u2066-\u2069]/g, '').replace(/\s+/g, ' ').trim();
        if (countWords(normalized)) blocks.push({kind, text: normalized});
        text = '';
    };
    const walk = node => {
        if (node.nodeType === 3) {
            text += node.textContent;
            return;
        }
        if (node.nodeType !== 1) return;
        if (node.tagName === 'BR') {
            flush();
            return;
        }
        const isBlock = blockTags.has(node.tagName);
        const previousKind = kind;
        if (isBlock) {
            flush();
            kind = /^H[1-6]$/.test(node.tagName) ? 'heading'
                : ['LI', 'TD', 'TH', 'FIGCAPTION'].includes(node.tagName) ? 'item' : previousKind;
        }
        node.childNodes.forEach(walk);
        if (isBlock) {
            flush();
            kind = previousKind;
        }
    };
    doc.body.childNodes.forEach(walk);
    flush();
    return blocks;
}

function sentencesIn(text) {
    // Do not split decimal numbers, version numbers, URLs or email addresses at dots.
    const prepared = text.replace(/https?:\/\/\S+|www\.\S+|[\w.+-]+@[\w.-]+\.[a-z]+/gi, value => value.replace(/\.(?=\S)/g, '\uE000'))
        .replace(/(?<=\p{N})\.(?=\p{N})/gu, '\uE000');
    return prepared.split(/[.!?؟。…]+/u).map(value => value.replace(/\uE000/g, '.').trim()).filter(value => countWords(value));
}

export function analyzeReadability(blocks) {
    const measured = blocks.map(block => ({...block, words: countWords(block.text)})).filter(block => block.words);
    const words = measured.reduce((sum, block) => sum + block.words, 0);
    const paragraphs = measured.filter(block => block.kind === 'paragraph');
    const sentences = measured.filter(block => block.kind !== 'heading').flatMap(block => sentencesIn(block.text))
        .map(text => ({text, words: countWords(text)}));
    const result = {
        words,
        sentences: sentences.length,
        paragraphs: paragraphs.length,
        readingMinutes: words ? Math.max(1, Math.ceil(words / 200)) : 0,
        state: words === 0 ? 'empty' : words < MIN_WORDS ? 'short' : 'ready',
        status: 'neutral',
        issues: 0,
        checks: [],
    };
    if (result.state !== 'ready') return result;

    const example = items => items.slice().sort((a, b) => b.words - a.words).slice(0, 2)
        .map(item => `${item.words} کلمه: ${item.text.length > 160 ? item.text.slice(0, 160) + '…' : item.text}`);
    const longSentences = sentences.filter(sentence => sentence.words > LONG_SENTENCE);
    const ratio = sentences.length ? longSentences.length / sentences.length : 0;
    if (sentences.length) result.checks.push({
        key: 'sentences', title: 'طول جمله‌ها',
        status: ratio > 0.4 ? 'problem' : ratio > 0.25 ? 'improvement' : 'good',
        description: `${longSentences.length} جمله از ${sentences.length} جمله، بیش از ${LONG_SENTENCE} کلمه دارد. ` + (ratio > 0.25
            ? 'پیشنهاد: سهم جمله‌های بلند حداکثر ۲۵٪ باشد؛ هر جملهٔ بلند را به چند جملهٔ کوتاه‌تر با یک پیام مشخص تقسیم کنید.'
            : 'سهم جمله‌های بلند از ۲۵٪ بیشتر نیست و طول جمله‌ها مناسب است.'),
        examples: ratio > 0.25 ? example(longSentences) : [],
    });

    const longParagraphs = paragraphs.filter(block => block.words > LONG_PARAGRAPH);
    if (paragraphs.length) result.checks.push({
        key: 'paragraphs', title: 'طول پاراگراف‌ها',
        status: longParagraphs.some(block => block.words > 180) ? 'problem' : longParagraphs.length ? 'improvement' : 'good',
        description: longParagraphs.length
            ? `${longParagraphs.length} پاراگراف بیش از ${LONG_PARAGRAPH} کلمه دارد. با تغییر موضوع، پاراگراف تازه‌ای بسازید؛ پاراگراف‌های بیش از ۱۸۰ کلمه در اولویت اصلاح هستند.`
            : `همهٔ ${paragraphs.length} پاراگراف حداکثر ${LONG_PARAGRAPH} کلمه دارند و برای مرور متن مناسب‌اند.`,
        examples: example(longParagraphs),
    });

    let sectionWords = 0;
    let sectionTitle = 'ابتدای متن';
    const sections = [];
    for (const block of measured) {
        if (block.kind === 'heading') {
            if (sectionWords) sections.push({text: sectionTitle, words: sectionWords});
            sectionWords = 0;
            sectionTitle = block.text;
        } else {
            sectionWords += block.words;
        }
    }
    if (sectionWords) sections.push({text: sectionTitle, words: sectionWords});
    const longSections = sections.filter(section => section.words > LONG_SECTION);
    result.checks.push({
        key: 'headings', title: 'تقسیم متن با زیرعنوان',
        status: longSections.some(section => section.words > 450) ? 'problem' : longSections.length ? 'improvement' : 'good',
        description: longSections.length
            ? `${longSections.length} بخش بیش از ${LONG_SECTION} کلمه بدون زیرعنوان دارد. در محل تغییر موضوع از زیرعنوان مرتبط استفاده کنید؛ بخش‌های بیش از ۴۵۰ کلمه در اولویت‌اند.`
            : `هیچ بخشی بیش از ${LONG_SECTION} کلمه بدون زیرعنوان نیست؛ برای متن کوتاه، افزودن زیرعنوان ضروری نیست.`,
        examples: example(longSections),
    });
    result.issues = result.checks.filter(check => check.status !== 'good').length;
    result.status = result.checks.some(check => check.status === 'problem') ? 'problem'
        : result.issues ? 'improvement' : 'good';
    return result;
}
