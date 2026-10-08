// These names are also the public CSS classes stored in article/product HTML.
const BOX_STYLES = [
    {key: 'soft', label: 'ملایم', hint: 'پس‌زمینه رنگی آرام'},
    {key: 'outline', label: 'کادر خطی', hint: 'کادر ظریف و مینیمال'},
    {key: 'stripe', label: 'نوار کناری', hint: 'تأکید با خط رنگی'},
    {key: 'gradient', label: 'گرادیانی', hint: 'سطح روشن و چندلایه'},
    {key: 'solid', label: 'پررنگ', hint: 'اعلان با رنگ یکپارچه'},
    {key: 'glass', label: 'شیشه‌ای', hint: 'لایه نیمه‌شفاف'},
    {key: 'ribbon', label: 'روبان عنوان', hint: 'عنوان برجسته'},
    {key: 'quote', label: 'نقل‌قول', hint: 'متن شاخص و الهام‌بخش'},
    {key: 'ticket', label: 'بلیت', hint: 'کادر با خطوط بریده'},
    {key: 'spotlight', label: 'کانون توجه', hint: 'تأکید نمایشی'},
    {key: 'folded', label: 'گوشه‌تاخورده', hint: 'یادداشت با گوشه کاغذی'},
    {key: 'timeline', label: 'خط زمانی', hint: 'نقطه و مسیر کنار متن'},
    {key: 'notepad', label: 'کاغذ یادداشت', hint: 'خطوط ظریف دفترچه'},
    {key: 'capsule', label: 'کپسولی', hint: 'عنوان شناور و کادر نرم'},
    {key: 'aurora', label: 'شفقی', hint: 'هاله‌های رنگی مدرن'},
];

const BOX_COLORS = [
    {key: 'amber', label: 'نارنجی', swatch: '#ea701a'},
    {key: 'sky', label: 'آبی', swatch: '#0ea5e9'},
    {key: 'emerald', label: 'سبز', swatch: '#10b981'},
    {key: 'violet', label: 'بنفش', swatch: '#8b5cf6'},
    {key: 'rose', label: 'صورتی', swatch: '#f43f75'},
    {key: 'teal', label: 'فیروزه‌ای', swatch: '#0f766e'},
    {key: 'indigo', label: 'نیلی', swatch: '#4338ca'},
    {key: 'lime', label: 'لیمویی', swatch: '#668500'},
    {key: 'crimson', label: 'قرمز', swatch: '#be123c'},
    {key: 'slate', label: 'خاکستری', swatch: '#475569'},
];

let nextBoxId = 0;

function findCurrentBox(editor) {
    return editor.dom.getParent(editor.selection.getNode(), 'div.mw-box');
}

function insertOrRestyleBox(editor, style, color, commitChanges) {
    const currentBox = findCurrentBox(editor);

    if (currentBox) {
        editor.undoManager.transact(() => {
            BOX_STYLES.forEach(({key}) => currentBox.classList.remove(`mw-box--${key}`));
            BOX_COLORS.forEach(({key}) => currentBox.classList.remove(`mw-box--${key}`));
            currentBox.classList.add(`mw-box--${style}`, `mw-box--${color}`);
        });
    } else {
        const selectedHtml = editor.selection.getContent({format: 'html'});
        const insertId = `mw-box-insert-${++nextBoxId}`;
        const body = selectedHtml || '<p>متن این باکس را اینجا بنویسید.</p>';

        editor.undoManager.transact(() => {
            editor.insertContent(`
                <div class="mw-box mw-box--${style} mw-box--${color}" data-mw-insert-id="${insertId}">
                    <div class="mw-box-title">عنوان باکس را بنویسید</div>
                    <div class="mw-box-body">${body}</div>
                </div>
                <p><br></p>
            `);

            const insertedBox = editor.getBody().querySelector(`[data-mw-insert-id="${insertId}"]`);

            if (insertedBox) {
                insertedBox.removeAttribute('data-mw-insert-id');
                const title = insertedBox.querySelector('.mw-box-title');
                if (title) {
                    editor.selection.select(title, true);
                }
            }
        });
    }

    commitChanges(editor);
}

export function registerContentBoxControls(editor, commitChanges) {
    BOX_COLORS.forEach(({key, swatch}) => {
        editor.ui.registry.addIcon(`mw-box-color-${key}`, `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                <circle cx="12" cy="12" r="8" fill="${swatch}" stroke="#ffffff" stroke-width="1.5"/>
            </svg>
        `);
    });

    editor.ui.registry.addMenuButton('contentBoxes', {
        text: 'باکس‌ها',
        tooltip: 'افزودن باکس یا تغییر سبک باکس فعلی',
        fetch(callback) {
            callback(BOX_STYLES.map(({key: style, label, hint}) => ({
                type: 'nestedmenuitem',
                text: `${label} · ${hint}`,
                getSubmenuItems: () => BOX_COLORS.map(({key: color, label: colorLabel}) => ({
                    type: 'menuitem',
                    text: colorLabel,
                    icon: `mw-box-color-${color}`,
                    onAction: () => insertOrRestyleBox(editor, style, color, commitChanges),
                })),
            })));
        },
    });

    editor.ui.registry.addToggleButton('boxDarkPreview', {
        text: '◐',
        tooltip: 'پیش‌نمایش باکس‌ها در حالت تاریک',
        onAction(api) {
            const isDark = editor.getBody().classList.toggle('dark');
            api.setActive(isDark);
        },
        onSetup(api) {
            api.setActive(editor.getBody()?.classList.contains('dark') || false);
            return () => {};
        },
    });
}
