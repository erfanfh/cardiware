function normalizeLightboxItems(items = []) {
    return (Array.isArray(items) ? items : [])
        .map((item) => ({
            src: item?.src ?? '',
            title: item?.title ?? '',
        }))
        .filter((item) => item.src);
}

window.lightboxSystem = function () {
    return {
        isOpen: false,
        items: [],
        index: 0,

        get current() {
            return this.items[this.index] ?? {src: '', title: ''};
        },

        open(items = [], index = 0) {
            const normalizedItems = normalizeLightboxItems(items);

            if (!normalizedItems.length) {
                return;
            }

            this.items = normalizedItems;
            this.index = Math.max(0, Math.min(index, normalizedItems.length - 1));
            this.isOpen = true;

            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
        },

        close() {
            this.isOpen = false;
            this.items = [];
            this.index = 0;

            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        },

        next() {
            if (this.items.length <= 1) {
                return;
            }

            this.index = (this.index + 1) % this.items.length;
        },

        prev() {
            if (this.items.length <= 1) {
                return;
            }

            this.index = (this.index - 1 + this.items.length) % this.items.length;
        },
    };
};

function getLightboxItemFromElement(element) {
    const src =
        element.dataset.src ||
        element.getAttribute('href') ||
        element.getAttribute('src') ||
        element.querySelector('img')?.getAttribute('src') ||
        '';

    const title =
        element.dataset.title ||
        element.getAttribute('title') ||
        element.querySelector('img')?.getAttribute('alt') ||
        '';

    return {
        src,
        title,
    };
}

function collectLightboxItems(galleryName) {
    const elements = Array.from(document.querySelectorAll(`[data-lightbox="${galleryName}"]`));

    return elements
        .map((element) => getLightboxItemFromElement(element))
        .filter((item) => item.src);
}

function openLightboxFromElement(element) {
    const galleryName = element.dataset.lightbox;

    if (!galleryName) {
        return;
    }

    const galleryElements = Array.from(document.querySelectorAll(`[data-lightbox="${galleryName}"]`));
    const items = collectLightboxItems(galleryName);
    const index = galleryElements.indexOf(element);

    window.dispatchEvent(new CustomEvent('lightbox:open', {
        detail: {
            items,
            index: index >= 0 ? index : 0,
        },
    }));
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-lightbox]');

    if (!trigger) {
        return;
    }

    event.preventDefault();
    openLightboxFromElement(trigger);
});
