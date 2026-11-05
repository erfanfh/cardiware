const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const ARABIC_INDIC_DIGITS = '٠١٢٣٤٥٦٧٨٩';
const LETTER = /\p{Letter}/u;
const ARABIC_LETTER = /\p{Script=Arabic}/u;
const LATIN_LETTER = /\p{Script=Latin}/u;
const SKIP_SELECTOR = 'script, style, textarea, input, select, option, code, pre, kbd, samp, [contenteditable]:not([contenteditable="false"]), [data-no-localize-digits]';
const INLINE_TAGS = new Set(['A', 'B', 'BDI', 'EM', 'I', 'SMALL', 'SPAN', 'STRONG', 'TIME', 'U']);

function scriptOf(character) {
    if (!LETTER.test(character)) return null;
    if (ARABIC_LETTER.test(character)) return 'fa';
    if (LATIN_LETTER.test(character)) return 'en';
    return null;
}

function surroundingScript(value, start, end) {
    let left = null;
    let right = null;
    let leftDistance = Infinity;
    let rightDistance = Infinity;

    for (let index = start - 1; index >= 0; index--) {
        left = scriptOf(value[index]);
        if (left) {
            leftDistance = start - index;
            break;
        }
    }

    for (let index = end; index < value.length; index++) {
        right = scriptOf(value[index]);
        if (right) {
            rightDistance = index - end + 1;
            break;
        }
    }

    if (!left) return right;
    if (!right) return left;
    return leftDistance <= rightDistance ? left : right;
}

export function localizeDisplayDigits(value, fallbackLanguage = 'fa') {
    // بازه‌های عددی متصل با جداکننده (26-27، 1405/06/15) یک واحد تلقی می‌شوند
    // تا هر دو سر بازه همیشه یک زبان بگیرند و ترکیب «۲۶-27» رخ ندهد.
    const digitRange = /[0-9۰-۹٠-٩]+(?:\s*[-–—/]?\s*[0-9۰-۹٠-٩]+)*/g;

    return value.replace(digitRange, (digits, start) => {
        const language = surroundingScript(value, start, start + digits.length) ?? fallbackLanguage;
        const ascii = digits.replace(/[۰-۹٠-٩]/g, digit => {
            const persianIndex = PERSIAN_DIGITS.indexOf(digit);
            return String(persianIndex < 0 ? ARABIC_INDIC_DIGITS.indexOf(digit) : persianIndex);
        });

        return language === 'fa'
            ? ascii.replace(/[0-9]/g, digit => PERSIAN_DIGITS[digit])
            : ascii;
    });
}

function fallbackLanguage(element) {
    const context = element.closest('[lang], [dir]');
    if (!context) return 'fa';

    const explicitLanguage = context.getAttribute('lang');
    if (explicitLanguage) return explicitLanguage.toLowerCase().startsWith('fa') ? 'fa' : 'en';

    return context.getAttribute('dir') === 'ltr' ? 'en' : 'fa';
}

function edgeScript(node, reverse) {
    const value = node.nodeType === Node.TEXT_NODE
        ? node.nodeValue
        : node.nodeType === Node.ELEMENT_NODE && INLINE_TAGS.has(node.tagName)
            ? node.textContent
            : '';

    if (reverse) {
        for (let index = value.length - 1; index >= 0; index--) {
            const language = scriptOf(value[index]);
            if (language) return language;
        }
    } else {
        for (const character of value) {
            const language = scriptOf(character);
            if (language) return language;
        }
    }

    return null;
}

function adjacentInlineLanguage(node) {
    let current = node;

    while (current && current !== document.body) {
        const previous = current.previousSibling;
        const next = current.nextSibling;
        const left = previous && edgeScript(previous, true);
        const right = next && edgeScript(next, false);
        if (left || right) return left ?? right;

        const parent = current.parentElement;
        if (!parent || !INLINE_TAGS.has(parent.tagName)) break;
        current = parent;
    }

    return null;
}

function localizeTextNode(node) {
    if (!/[0-9۰-۹٠-٩]/.test(node.nodeValue)) return;

    const parent = node.parentElement;
    if (!parent || parent.closest(SKIP_SELECTOR)) return;

    // A number in its own inline element can use the neighboring word as context.
    const hasLetter = Array.from(node.nodeValue).some(character => scriptOf(character));
    const language = hasLetter
        ? fallbackLanguage(parent)
        : adjacentInlineLanguage(node) ?? fallbackLanguage(parent);
    const localized = localizeDisplayDigits(node.nodeValue, language);
    if (localized !== node.nodeValue) node.nodeValue = localized;
}

function localizeTree(root) {
    if (root.nodeType === Node.TEXT_NODE) {
        localizeTextNode(root);
        return;
    }

    if (root.nodeType !== Node.ELEMENT_NODE || root.matches(SKIP_SELECTOR)) return;

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    while (walker.nextNode()) localizeTextNode(walker.currentNode);
}

function start() {
    localizeTree(document.body);

    new MutationObserver(records => {
        for (const record of records) {
            if (record.type === 'characterData') {
                localizeTextNode(record.target);
            } else {
                record.addedNodes.forEach(localizeTree);
            }
        }
    }).observe(document.body, { subtree: true, childList: true, characterData: true });
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
}
