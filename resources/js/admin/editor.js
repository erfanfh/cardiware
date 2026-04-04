import tinymce from 'tinymce';
import '../../css/admin/tinymce.css';

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
            menubar: false,
            toolbar: 'redo undo | blocks bold italic | alignright aligncenter alignleft alignjustify | link mediaPicker | forecolor backcolor | bullist numlist outdent indent | table underline strikethrough hr | rtl ltr | code fullscreen',
            plugins: [
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
                <img
                    src="${safeUrl}"
                    alt="${safeAlt}"
                    title="${safeTitle}"
                    class="editor-media-image"
                    data-media-type="image"
                >
                <figcaption>${safeCaption}</figcaption>
            </figure>
        `;
    }

    return `
        <img
            src="${safeUrl}"
            alt="${safeAlt}"
            title="${safeTitle}"
            class="editor-media-image"
            data-media-type="image"
        >
    `;
}

function registerCustomButtons(editor, element) {
    const itemKey = element.dataset.editorKey;

    editor.ui.registry.addIcon('mediaPickerIcon', `
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                <path d="M22 12c0 4.714 0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12s0-7.071 1.464-8.536C4.93 2 7.286 2 12 2"/>
                <path d="m2 12.5l1.752-1.533a2.3 2.3 0 0 1 3.14.105l4.29 4.29a2 2 0 0 0 2.564.222l.299-.21a3 3 0 0 1 3.731.225L21 18.5" opacity=".5"/>
                <path d="M15 5.5h3.5m0 0H22m-3.5 0V9m0-3.5V2"/>
            </g>
        </svg>
    `);

    editor.ui.registry.addButton('mediaPicker', {
        icon: 'mediaPickerIcon',
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

    editor.ui.registry.addButton('mediaImageEdit', {
        icon: 'edit-block',
        tooltip: 'ویرایش تصویر',
        onAction: () => {
            openImageEditDialog(editor);
        },
    });

    editor.ui.registry.addButton('mediaImageRemove', {
        icon: 'remove',
        tooltip: 'حذف تصویر',
        onAction: () => {
            const node = editor.selection.getNode();

            if (node?.nodeName === 'IMG') {
                const figure = editor.dom.getParent(node, 'figure');

                if (figure) {
                    editor.dom.remove(figure);
                } else {
                    editor.dom.remove(node);
                }
            }
        },
    });
}

function openImageEditDialog(editor) {
    const node = editor.selection.getNode();

    if (!node || node.nodeName !== 'IMG') {
        return;
    }

    const figure = editor.dom.getParent(node, 'figure');
    const figcaption = figure ? figure.querySelector('figcaption') : null;

    const currentAlt = node.getAttribute('alt') || '';
    const currentTitle = node.getAttribute('title') || '';
    const currentCaption = figcaption?.innerText || '';

    editor.windowManager.open({
        title: 'ویرایش تصویر',
        body: {
            type: 'panel',
            items: [
                {
                    type: 'input',
                    name: 'alt',
                    label: 'متن جایگزین',
                },
                {
                    type: 'input',
                    name: 'title',
                    label: 'عنوان',
                },
                {
                    type: 'textarea',
                    name: 'caption',
                    label: 'کپشن',
                },
            ],
        },
        initialData: {
            alt: currentAlt,
            title: currentTitle,
            caption: currentCaption,
        },
        buttons: [
            {
                type: 'cancel',
                text: 'انصراف',
            },
            {
                type: 'submit',
                text: 'ذخیره',
                primary: true,
            },
        ],
        onSubmit: (api) => {
            const data = api.getData();

            node.setAttribute('alt', data.alt || '');
            node.setAttribute('title', data.title || '');

            if (figure) {
                let captionNode = figure.querySelector('figcaption');

                if (data.caption?.trim()) {
                    if (!captionNode) {
                        captionNode = editor.dom.create('figcaption');
                        figure.appendChild(captionNode);
                    }

                    captionNode.textContent = data.caption.trim();
                } else if (captionNode) {
                    editor.dom.remove(captionNode);
                }
            } else if (data.caption?.trim()) {
                const wrapper = editor.dom.create('figure', {class: 'content-media'});
                const clonedImg = node.cloneNode(true);
                const captionNode = editor.dom.create('figcaption');

                captionNode.textContent = data.caption.trim();

                wrapper.appendChild(clonedImg);
                wrapper.appendChild(captionNode);

                editor.dom.replace(wrapper, node);
            }

            api.close();
        },
    });
}

function setImageAlignment(editor, align = 'center') {
    const node = editor.selection.getNode();

    if (!node || node.nodeName !== 'IMG') {
        return;
    }

    const target = editor.dom.getParent(node, 'figure') || node;

    editor.dom.removeClass(target, 'image-align-right');
    editor.dom.removeClass(target, 'image-align-center');
    editor.dom.removeClass(target, 'image-align-left');

    editor.dom.addClass(target, `image-align-${align}`);
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
                const container = editor.getContainer();

                if (container) {
                    container.setAttribute('dir', dir);

                    if (dir === 'rtl') {
                        container.classList.add('tox-rtl-custom');
                    } else {
                        container.classList.remove('tox-rtl-custom');
                    }
                }

                editor.getElement()?.setAttribute('data-editor-initialized', 'true');
            });

            editor.on('remove', () => {
                editor.getElement()?.removeAttribute('data-editor-initialized');
            });

            editor.ui.registry.addContextToolbar('imageToolbar', {
                predicate: (node) => {
                    return node?.nodeName === 'IMG' && editor.dom.hasClass(node, 'editor-media-image');
                },
                items: 'mediaImageRemove mediaImageEdit',
                position: 'node',
                scope: 'node',
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
