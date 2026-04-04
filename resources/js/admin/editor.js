import tinymce from 'tinymce';
import '../../css/admin/tinymce.css';
import '../../css/front/tinymce-content.css';

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
let pendingImageReplace = null;

function getPresetConfig(preset = 'default') {
    const presets = {
        simple: {
            menubar: false,
            toolbar: 'undo redo | bold italic underline | alignright aligncenter alignleft | link ',
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
            toolbar: 'undo redo | blocks bold italic underline | alignright aligncenter alignleft | link mediaPicker | bullist numlist | ltr rtl',
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
            toolbar: 'undo redo | blocks bold italic | alignright aligncenter alignleft alignjustify | link mediaPicker | insertMore | forecolor backcolor | bullist numlist outdent indent | table underline strikethrough hr | rtl ltr | code fullscreen',
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

function buildImageHtml({url = '', alt = '', title = '', caption = '', mediaId = null, size = 'md'} = {}) {
    const safeUrl = escapeHtml(url);
    const safeAlt = escapeHtml(alt);
    const safeTitle = escapeHtml(title);
    const safeCaption = escapeHtml(caption);
    const safeMediaId = mediaId ? escapeHtml(String(mediaId)) : '';
    const normalizedSize = ['sm', 'md', 'lg', 'full'].includes(size) ? size : 'md';

    if (!safeUrl) {
        return '';
    }

    if (safeCaption) {
        return `
            <figure class="content-media image-size-${normalizedSize}">
                <img
                    src="${safeUrl}"
                    alt="${safeAlt}"
                    title="${safeTitle}"
                    class="editor-media-image"
                    data-media-type="image"
                    ${safeMediaId ? `data-media-id="${safeMediaId}"` : ''}
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
            class="editor-media-image image-size-${normalizedSize}"
            data-media-type="image"
            ${safeMediaId ? `data-media-id="${safeMediaId}"` : ''}
        >
    `;
}

function registerCustomButtons(editor, element) {
    const itemKey = element.dataset.editorKey;

    editor.ui.registry.addIcon('mediaPickerIcon', `
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                <path d="m3 16l4.47-4.47a1.81 1.81 0 0 1 2.56 0L14 15.5m1.5 1.5L14 15.5m7 .5l-2.47-2.47a1.81 1.81 0 0 0-2.56 0L14 15.5"/>
                <path d="M12 2.5c-4.23 0-6.345 0-7.747 1.198q-.3.256-.555.555C2.5 5.655 2.5 7.77 2.5 12s0 6.345 1.198 7.747q.256.3.555.555C5.655 21.5 7.77 21.5 12 21.5s6.345 0 7.747-1.198q.3-.256.555-.555C21.5 18.345 21.5 16.23 21.5 12m0-6H18m0 0h-3.5M18 6V2.5M18 6v3.5"/>
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

    editor.ui.registry.addIcon('moreIcon', `
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" d="M3 12h18"/>
                <path d="M14.5 5.5a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0Zm0 13a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0Z"/>
            </g>
        </svg>
    `);

    editor.ui.registry.addButton('insertMore', {
        icon: 'moreIcon',
        tooltip: 'درج جداکننده ادامه مطلب',
        onAction: () => {
            const existingMarker = editor.getBody().querySelector('[data-more-marker="true"]');

            if (existingMarker) {
                editor.selection.select(existingMarker);
                editor.selection.scrollIntoView(existingMarker);
                return;
            }

            editor.insertContent(`
            <div class="editor-more-marker" data-more-marker="true" contenteditable="false">
                ادامه مطلب
            </div>
        `);
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

    editor.ui.registry.addButton('mediaImageReplace', {
        icon: 'reload',
        tooltip: 'جایگزینی تصویر',
        onAction: () => {
            startImageReplace(editor, element);
        },
    });

    editor.ui.registry.addButton('mediaImageSizeSm', {
        text: 'S',
        tooltip: 'اندازه کوچک',
        onAction: () => {
            setImageSize(editor, 'sm');
        },
    });

    editor.ui.registry.addButton('mediaImageSizeMd', {
        text: 'M',
        tooltip: 'اندازه متوسط',
        onAction: () => {
            setImageSize(editor, 'md');
        },
    });

    editor.ui.registry.addButton('mediaImageSizeLg', {
        text: 'L',
        tooltip: 'اندازه بزرگ',
        onAction: () => {
            setImageSize(editor, 'lg');
        },
    });

    editor.ui.registry.addButton('mediaImageSizeFull', {
        text: '100%',
        tooltip: 'تمام عرض',
        onAction: () => {
            setImageSize(editor, 'full');
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
                {
                    type: 'selectbox',
                    name: 'size',
                    label: 'اندازه تصویر',
                    items: [
                        {value: 'sm', text: 'کوچک'},
                        {value: 'md', text: 'متوسط'},
                        {value: 'lg', text: 'بزرگ'},
                        {value: 'full', text: 'تمام عرض'},
                    ],
                },
            ],
        },
        initialData: {
            alt: currentAlt,
            title: currentTitle,
            caption: currentCaption,
            size: getCurrentImageSize(editor, node),
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
            setImageSize(editor, data.size || 'md');

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

function getSelectedImageNode(editor) {
    const node = editor.selection.getNode();

    if (node?.nodeName === 'IMG' && editor.dom.hasClass(node, 'editor-media-image')) {
        return node;
    }

    const img = node?.querySelector?.('img.editor-media-image');

    return img || null;
}

function getImageWrapper(editor, imageNode) {
    if (!imageNode) {
        return null;
    }

    return editor.dom.getParent(imageNode, 'figure.content-media') || imageNode;
}

function clearImageSizeClasses(editor, target) {
    if (!target) {
        return;
    }

    editor.dom.removeClass(target, 'image-size-sm');
    editor.dom.removeClass(target, 'image-size-md');
    editor.dom.removeClass(target, 'image-size-lg');
    editor.dom.removeClass(target, 'image-size-full');
}

function setImageSize(editor, size = 'md') {
    const imageNode = getSelectedImageNode(editor);

    if (!imageNode) {
        return;
    }

    const target = getImageWrapper(editor, imageNode);

    clearImageSizeClasses(editor, target);

    const allowedSizes = ['sm', 'md', 'lg', 'full'];
    const safeSize = allowedSizes.includes(size) ? size : 'md';

    editor.dom.addClass(target, `image-size-${safeSize}`);
}

function getCurrentImageSize(editor, imageNode) {
    const target = getImageWrapper(editor, imageNode);

    if (!target) {
        return 'md';
    }

    if (editor.dom.hasClass(target, 'image-size-sm')) return 'sm';
    if (editor.dom.hasClass(target, 'image-size-md')) return 'md';
    if (editor.dom.hasClass(target, 'image-size-lg')) return 'lg';
    if (editor.dom.hasClass(target, 'image-size-full')) return 'full';

    return 'md';
}

function startImageReplace(editor, element) {
    const imageNode = getSelectedImageNode(editor);

    if (!imageNode) {
        return;
    }

    pendingImageReplace = {
        editorId: editor.id,
        elementId: element.id,
    };

    window.dispatchEvent(new CustomEvent('mfip-open-requested', {
        detail: {
            itemKey: element.dataset.editorKey,
            selectedMediaFileId: imageNode.getAttribute('data-media-id') || null,
            selectedMediaPath: imageNode.getAttribute('src') || null,
            mode: 'replace-image',
        },
    }));
}

function replaceSelectedImage(editor, {mediaId = null, mediaPath = null, mediaTitle = ''} = {}) {
    const imageNode = getSelectedImageNode(editor);

    if (!imageNode || !mediaPath) {
        return;
    }

    imageNode.setAttribute('src', mediaPath);
    imageNode.setAttribute('alt', mediaTitle || '');
    imageNode.setAttribute('title', mediaTitle || '');

    if (mediaId) {
        imageNode.setAttribute('data-media-id', String(mediaId));
    } else {
        imageNode.removeAttribute('data-media-id');
    }
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

        quickbars_selection_toolbar: 'bold italic underline quicklink',
        quickbars_insert_toolbar: '',

        browser_spellcheck: true,
        contextmenu: false,

        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,

        autoresize_bottom_margin: 16,

        body_class: 'tinymce-content',
        content_css: [window.tinymceContentCss, window.editorCss],

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

            editor.on('GetContent', (event) => {
                event.content = normalizeEditorContentBeforeSave(event.content);
            });

            editor.on('BeforeSetContent', (event) => {
                event.content = normalizeEditorContentForDisplay(event.content);
            });

            editor.ui.registry.addContextToolbar('imageToolbar', {
                predicate: (node) => {
                    return node?.nodeName === 'IMG' && editor.dom.hasClass(node, 'editor-media-image');
                },
                items: 'mediaImageRemove mediaImageReplace mediaImageEdit',
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
    const mediaTitle = detail.mediaTitle || '';

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

    const shouldReplace =
        pendingImageReplace &&
        pendingImageReplace.editorId === editor.id;

    if (shouldReplace) {
        replaceSelectedImage(editor, {
            mediaId,
            mediaPath,
            mediaTitle,
        });

        pendingImageReplace = null;
        return;
    }

    const html = buildImageHtml({
        url: mediaPath,
        alt: mediaTitle,
        title: mediaTitle,
        caption: '',
        mediaId,
        size: 'md',
    });

    if (!html) {
        return;
    }

    editor.focus();
    editor.insertContent(html);
}

function normalizeEditorContentBeforeSave(content = '') {
    return String(content).replaceAll(
        /<div class="editor-more-marker"[^>]*data-more-marker="true"[^>]*>[\s\S]*?<\/div>/g,
        '<!--more-->'
    );
}

function normalizeEditorContentForDisplay(content = '') {
    return String(content).replaceAll(
        '<!--more-->',
        '<div class="editor-more-marker" data-more-marker="true" contenteditable="false">ادامه مطلب</div>'
    );
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
