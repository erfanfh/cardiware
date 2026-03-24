import tinymce from 'tinymce';

// Theme
import 'tinymce/themes/silver/theme';

// Icons
import 'tinymce/icons/default/icons';

// Model
import 'tinymce/models/dom/model';

// Plugins
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/table';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/quickbars';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/autoresize';
import 'tinymce/plugins/directionality';

// Skins / Content CSS
import 'tinymce/skins/ui/oxide/skin';
import 'tinymce/skins/ui/oxide/content';
import 'tinymce/skins/content/default/content';

const EDITOR_SELECTOR = 'textarea[data-editor]';

function getPresetConfig(preset = 'default') {
    const presets = {
        simple: {
            menubar: false,
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | removeformat',
            plugins: [
                'advlist',
                'autolink',
                'lists',
                'link',
                'quickbars',
                'autoresize',
                'directionality',
            ],
        },

        default: {
            menubar: false,
            toolbar: 'undo redo | blocks | bold italic underline | alignright aligncenter alignleft | bullist numlist blockquote | link mediaPicker table | removeformat | ltr rtl',
            plugins: [
                'advlist',
                'autolink',
                'lists',
                'link',
                'table',
                'quickbars',
                'autoresize',
                'directionality',
            ],
        },

        full: {
            menubar: 'file edit view insert format table tools help',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignright aligncenter alignleft alignjustify | bullist numlist outdent indent blockquote | link mediaPicker table charmap | visualblocks code fullscreen | removeformat | ltr rtl',
            plugins: [
                'advlist',
                'autolink',
                'lists',
                'link',
                'table',
                'code',
                'fullscreen',
                'wordcount',
                'quickbars',
                'charmap',
                'visualblocks',
                'autoresize',
                'directionality',
            ],
        },
    };

    return presets[preset] || presets.default;
}

function escapeHtml(value = '') {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function buildImageHtml({url = '', alt = '', title = '', caption = ''} = {}) {
    const safeUrl = escapeHtml(url);
    const safeAlt = escapeHtml(alt);
    const safeTitle = escapeHtml(title);
    const safeCaption = escapeHtml(caption);

    if (!safeUrl) {
        return '';
    }

    if (safeCaption) {
        return `
            <figure class="content-media">
                <img src="${safeUrl}" alt="${safeAlt}" title="${safeTitle}">
                <figcaption>${safeCaption}</figcaption>
            </figure>
        `;
    }

    return `<img src="${safeUrl}" alt="${safeAlt}" title="${safeTitle}">`;
}

function registerCustomButtons(editor, element) {
    const itemKey = element.dataset.editorKey;

    editor.ui.registry.addButton('mediaPicker', {
        text: 'رسانه',
        tooltip: 'افزودن رسانه',
        onAction: () => {
            window.dispatchEvent(new CustomEvent('mfip-open-requested', {
                detail: {
                    itemKey,
                    selectedMediaFileId: null,
                    selectedMediaPath: null,
                },
            }));
        },
    });
}

function buildEditorConfig(element) {
    const preset = element.dataset.editorPreset || 'default';
    const height = Number(element.dataset.editorHeight || 400);
    const dir = element.dataset.editorDir || 'rtl';
    const readonly = element.dataset.editorReadonly === 'true';

    const presetConfig = getPresetConfig(preset);

    return {
        selector: `#${CSS.escape(element.id)}`,

        license_key: 'gpl',
        branding: false,
        promotion: false,

        language_url: '/vendor/tinymce/langs/fa.js',
        language: 'fa',

        directionality: dir === 'rtl' ? 'rtl' : 'ltr',
        readonly,

        height,
        min_height: 220,
        resize: false,

        menubar: presetConfig.menubar,
        toolbar: presetConfig.toolbar,
        plugins: presetConfig.plugins,

        toolbar_mode: 'sliding',
        statusbar: true,

        placeholder: element.getAttribute('placeholder') || '',

        paste_as_text: false,
        paste_data_images: false,

        link_default_target: '_blank',
        link_assume_external_targets: true,

        quickbars_selection_toolbar: 'bold italic underline | quicklink blockquote',
        quickbars_insert_toolbar: 'quicktable',

        browser_spellcheck: true,
        contextmenu: false,

        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,

        autoresize_bottom_margin: 16,

        content_style: `
            body {
                font-family: IRANSansX, Vazirmatn, sans-serif;
                font-size: 14px;
                line-height: 2;
                color: #0f172a;
                direction: ${dir};
                text-align: ${dir === 'rtl' ? 'right' : 'left'};
                padding: 12px;
            }

            p {
                margin: 0 0 1rem;
            }

            h1, h2, h3, h4, h5, h6 {
                margin: 1.25rem 0 0.75rem;
                line-height: 1.8;
            }

            ul, ol {
                margin: 0 0 1rem;
                padding-inline-start: 1.5rem;
            }

            blockquote {
                margin: 1rem 0;
                padding: 0.75rem 1rem;
                border-right: 4px solid #fdba74;
                background: #fff7ed;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            table th,
            table td {
                border: 1px solid #cbd5e1;
                padding: 0.5rem;
            }

            figure.content-media {
                margin: 1rem 0;
            }

            figure.content-media img {
                display: block;
                max-width: 100%;
                height: auto;
                border-radius: 0.75rem;
            }

            figure.content-media figcaption {
                margin-top: 0.5rem;
                color: #64748b;
                font-size: 0.875rem;
                text-align: center;
            }
        `,

        setup(editor) {
            registerCustomButtons(editor, element);

            editor.on('init', () => {
                editor.getElement()?.setAttribute('data-editor-initialized', 'true');
            });

            editor.on('remove', () => {
                editor.getElement()?.removeAttribute('data-editor-initialized');
            });
        },
    };
}

function isInitialized(element) {
    if (!element?.id) {
        return true;
    }

    return !!tinymce.get(element.id);
}

function handleMfipSelected(event) {
    const detail = event.detail || {};
    const itemKey = detail.itemKey;
    const mediaPath = detail.mediaPath;
    const mediaId = detail.mediaId;

    if (!itemKey || !mediaPath) {
        return;
    }

    const element = document.querySelector(`[data-editor-key="${CSS.escape(itemKey)}"]`);

    if (!element) {
        return;
    }

    const editor = tinymce.get(element.id);

    if (!editor) {
        return;
    }

    const html = buildImageHtml({
        url: mediaPath,
        alt: '',
        title: mediaId ? `media-${mediaId}` : '',
        caption: '',
    });

    if (!html) {
        return;
    }

    editor.focus();
    editor.insertContent(html);
}

export function initEditors(container = document) {
    const elements = container.querySelectorAll(EDITOR_SELECTOR);

    elements.forEach((element) => {
        if (!element.id) {
            return;
        }

        if (isInitialized(element)) {
            return;
        }

        tinymce.init(buildEditorConfig(element));
    });
}

export function destroyEditors(container = document) {
    const elements = container.querySelectorAll(EDITOR_SELECTOR);

    elements.forEach((element) => {
        if (!element.id) {
            return;
        }

        const instance = tinymce.get(element.id);

        if (instance) {
            instance.remove();
        }
    });
}

export function reinitEditors(container = document) {
    destroyEditors(container);
    initEditors(container);
}

document.addEventListener('DOMContentLoaded', () => {
    initEditors(document);
});

window.addEventListener('mfip-selected', handleMfipSelected);

window.AdminEditor = {
    init: initEditors,
    destroy: destroyEditors,
    reinit: reinitEditors,
};
