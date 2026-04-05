function normalizeLightboxItems(items = []) {
    return (Array.isArray(items) ? items : [])
        .map((item) => ({
            src: item?.src ?? '',
            title: item?.title ?? '',
        }))
        .filter((item) => item.src);
}

function preloadImage(src) {
    if (!src) {
        return;
    }

    const image = new Image();
    image.src = src;
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

            this.preloadNeighbors();

            this.$nextTick(() => {
                this.scrollActiveThumbIntoView();
            });
        },

        close() {
            this.isOpen = false;

            window.setTimeout(() => {
                if (this.isOpen) {
                    return;
                }

                this.items = [];
                this.index = 0;
            }, 300);

            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        },

        next() {
            if (this.items.length <= 1) {
                return;
            }

            this.index = (this.index + 1) % this.items.length;
            this.preloadNeighbors();

            this.$nextTick(() => {
                this.scrollActiveThumbIntoView();
            });
        },

        prev() {
            if (this.items.length <= 1) {
                return;
            }

            this.index = (this.index - 1 + this.items.length) % this.items.length;
            this.preloadNeighbors();

            this.$nextTick(() => {
                this.scrollActiveThumbIntoView();
            });
        },

        goTo(index) {
            if (index < 0 || index >= this.items.length) {
                return;
            }

            this.index = index;
            this.preloadNeighbors();

            this.$nextTick(() => {
                this.scrollActiveThumbIntoView();
            });
        },

        preloadNeighbors() {
            if (this.items.length <= 1) {
                return;
            }

            const nextIndex = (this.index + 1) % this.items.length;
            const prevIndex = (this.index - 1 + this.items.length) % this.items.length;

            preloadImage(this.items[nextIndex]?.src);
            preloadImage(this.items[prevIndex]?.src);
        },

        scrollActiveThumbIntoView() {
            const container = this.$refs.thumbs;

            if (!container) {
                return;
            }

            const buttons = container.querySelectorAll('button');
            const activeButton = buttons[this.index];

            if (!activeButton) {
                return;
            }

            activeButton.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center',
            });
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
