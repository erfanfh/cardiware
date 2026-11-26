// Normalize display digits without modifying form inputs or shared admin scripts.
const skip = 'script, style, textarea, input, select, option, code, pre, kbd, samp, [contenteditable]:not([contenteditable="false"]), [data-no-localize-digits]';
const normalize = (value) => value.replace(/[\u06f0-\u06f9\u0660-\u0669]/g, digit => String(digit.charCodeAt(0) - (digit.charCodeAt(0) >= 0x6f0 ? 0x6f0 : 0x660)));
function textNode(node) {
    if (!node.parentElement || node.parentElement.closest(skip)) return;
    const value = normalize(node.nodeValue);
    if (value !== node.nodeValue) node.nodeValue = value;
}
function scan(node) {
    if (node.nodeType === Node.TEXT_NODE) return textNode(node);
    if (node.nodeType !== Node.ELEMENT_NODE || node.matches(skip)) return;
    const walker = document.createTreeWalker(node, NodeFilter.SHOW_TEXT);
    while (walker.nextNode()) textNode(walker.currentNode);
}
function start() {
    scan(document.body);
    new MutationObserver(records => {
        for (const record of records) {
            if (record.type === 'characterData') textNode(record.target);
            else record.addedNodes.forEach(scan);
        }
    }).observe(document.body, { subtree: true, childList: true, characterData: true });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
else start();
