const iconCache = new Map();
const pendingRequests = new Map();
const queues = new Map();

const normalizeTitle = (value) => {
    if (typeof value === 'string') {
        return value.trim();
    }

    if (value && typeof value === 'object') {
        const title = value.title ?? value.full_name ?? value.name;

        if (typeof title === 'string') {
            return title.trim();
        }
    }

    return '';
};

const normalizeId = (value) => {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const id = Number.parseInt(value, 10);

    return Number.isInteger(id) && id > 0 ? id : null;
};

const normalizeIcon = (value) => {
    if (!value || typeof value !== 'object') {
        return null;
    }

    const title = normalizeTitle(value);

    if (!title) {
        return null;
    }

    return {
        id: normalizeId(value.id),
        title,
        svg: typeof value.svg === 'string' && value.svg.trim()
            ? value.svg
            : null,
    };
};

const flushQueue = async (lookupUrl) => {
    const queue = queues.get(lookupUrl);

    if (!queue || queue.titles.size === 0) {
        return;
    }

    const titles = [...queue.titles].slice(0, 100);
    titles.forEach((title) => queue.titles.delete(title));
    queue.timer = null;

    try {
        const url = new URL(lookupUrl, window.location.origin);

        titles.forEach((title) => url.searchParams.append('names[]', title));

        const response = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Icon lookup request failed.');
        }

        const payload = await response.json();
        const icons = payload?.icons && typeof payload.icons === 'object'
            ? payload.icons
            : {};

        titles.forEach((title) => {
            const icon = normalizeIcon(icons[title]);
            iconCache.set(title, icon);

            const request = pendingRequests.get(title);
            request?.resolve(icon);
            pendingRequests.delete(title);
        });
    } catch (error) {
        console.error(error);

        titles.forEach((title) => {
            const request = pendingRequests.get(title);
            request?.resolve(null);
            pendingRequests.delete(title);
        });
    }

    if (queue.titles.size > 0) {
        queue.timer = window.setTimeout(() => flushQueue(lookupUrl), 0);
    }
};

window.mwIconLibrary = {
    register(icon) {
        icon = normalizeIcon(icon);

        if (!icon) {
            return;
        }

        iconCache.set(icon.title, icon);
    },

    load(title, lookupUrl) {
        title = normalizeTitle(title);

        if (!title) {
            return Promise.resolve(null);
        }

        if (iconCache.has(title)) {
            return Promise.resolve(iconCache.get(title));
        }

        if (pendingRequests.has(title)) {
            return pendingRequests.get(title).promise;
        }

        let resolveRequest;
        const promise = new Promise((resolve) => {
            resolveRequest = resolve;
        });

        pendingRequests.set(title, {
            promise,
            resolve: resolveRequest,
        });

        if (!lookupUrl) {
            resolveRequest(null);
            pendingRequests.delete(title);
            return promise;
        }

        if (!queues.has(lookupUrl)) {
            queues.set(lookupUrl, {
                titles: new Set(),
                timer: null,
            });
        }

        const queue = queues.get(lookupUrl);
        queue.titles.add(title);

        if (queue.timer === null) {
            queue.timer = window.setTimeout(() => flushQueue(lookupUrl), 20);
        }

        return promise;
    },
};

const sharedPickerState = (options = {}) => ({
    lookupUrl: options.lookupUrl ?? null,
    itemKey: options.itemKey ?? null,
    selectedSvg: normalizeIcon(options.value)?.svg ?? null,
    loadingPreview: false,

    initPreview() {
        const title = this.getSelectedTitle();
        const icon = normalizeIcon({
            id: this.getSelectedId(),
            title,
            svg: this.selectedSvg,
        });

        if (icon?.svg) {
            window.mwIconLibrary.register(icon);
            return;
        }

        this.loadPreview(title);
    },

    async loadPreview(title = null) {
        title = normalizeTitle(title ?? this.getSelectedTitle());

        if (!title) {
            this.setSelectedId(null);
            this.selectedSvg = null;
            this.loadingPreview = false;
            return;
        }

        this.loadingPreview = true;
        const requestedTitle = title;
        const icon = await window.mwIconLibrary.load(requestedTitle, this.lookupUrl);

        if (this.getSelectedTitle() === requestedTitle) {
            this.setSelectedId(icon?.id ?? null);
            this.selectedSvg = icon?.svg ?? null;
        }

        this.loadingPreview = false;
    },

    openPicker() {
        this.$dispatch('mwip-open-requested', {
            itemKey: this.itemKey,
            selectedIconName: this.getSelectedTitle() || null,
        });
    },

    applySelection(detail) {
        if (detail?.itemKey !== this.itemKey) {
            return;
        }

        const id = normalizeId(detail.iconId);
        const title = normalizeTitle(detail.iconTitle ?? detail.iconName);
        const svg = typeof detail.iconSvg === 'string' ? detail.iconSvg : null;

        this.setSelectedId(id);
        this.setSelectedTitle(title);
        this.selectedSvg = svg;
        this.loadingPreview = false;

        if (title && svg) {
            window.mwIconLibrary.register({id, title, svg});
        }
    },

    applyCleared(detail) {
        if (detail?.itemKey !== this.itemKey) {
            return;
        }

        this.clearSelection();
    },

    clearSelection() {
        this.setSelectedId(null);
        this.setSelectedTitle('');
        this.selectedSvg = null;
        this.loadingPreview = false;
    },
});

window.mwIconPickerField = function (options = {}) {
    const initialIcon = normalizeIcon(options.value);

    return {
        ...sharedPickerState(options),
        selectedId: initialIcon?.id ?? null,
        selectedTitle: initialIcon?.title ?? '',
        livewireModel: options.livewireModel ?? null,
        livewireIdModel: options.livewireIdModel ?? null,

        init() {
            this.syncLivewire(this.livewireIdModel, this.selectedId);
            this.syncLivewire(this.livewireModel, this.selectedTitle);
            this.initPreview();
        },

        syncLivewire(model, value) {
            if (!model) {
                return;
            }

            try {
                this.$wire?.set(model, value, false);
            } catch (error) {
                console.error('Unable to sync icon picker with Livewire.', error);
            }
        },

        getSelectedId() {
            return normalizeId(this.selectedId);
        },

        setSelectedId(id) {
            this.selectedId = normalizeId(id);
            this.syncLivewire(this.livewireIdModel, this.selectedId);
        },

        getSelectedTitle() {
            return normalizeTitle(this.selectedTitle);
        },

        setSelectedTitle(title) {
            this.selectedTitle = normalizeTitle(title);
            this.syncLivewire(this.livewireModel, this.selectedTitle);
        },
    };
};

window.mwRepeaterIconPicker = function (options = {}) {
    return {
        ...sharedPickerState({
            ...options,
            itemKey: options.item?.__key ?? options.itemKey ?? null,
            value: options.value ?? options.item?.[options.fieldName] ?? null,
        }),
        item: options.item ?? {},
        fieldName: options.fieldName ?? 'icon',
        idFieldName: options.idFieldName ?? 'icon_id',

        init() {
            const currentValue = this.item[this.fieldName];

            if (currentValue && typeof currentValue === 'object') {
                const icon = normalizeIcon(currentValue);

                this.item[this.idFieldName] = icon?.id ?? normalizeId(this.item[this.idFieldName]);
                this.item[this.fieldName] = icon?.title ?? '';
                this.selectedSvg = icon?.svg ?? this.selectedSvg;
            } else {
                this.item[this.idFieldName] = normalizeId(this.item[this.idFieldName]);
                this.item[this.fieldName] = normalizeTitle(currentValue);
            }

            this.initPreview();
        },

        getSelectedId() {
            return normalizeId(this.item[this.idFieldName]);
        },

        setSelectedId(id) {
            this.item[this.idFieldName] = normalizeId(id);
        },

        getSelectedTitle() {
            return normalizeTitle(this.item[this.fieldName]);
        },

        setSelectedTitle(title) {
            this.item[this.fieldName] = normalizeTitle(title);
        },
    };
};
