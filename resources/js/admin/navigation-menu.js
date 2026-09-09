import Sortable from 'sortablejs';

window.navigationManager = function (config = {}) {
    return {
        activeType: 'custom_link',
        nextId: 100,
        sortables: [],
        treeEventsBound: false,
        iconSelectedListener: null,
        iconClearedListener: null,

        customLink: {
            title: '',
            url: '',
        },

        pickerConfigs: config.pickerConfigs ?? {},

        entityPicker: {
            type: null,
            label: '',
            emptyText: 'موردی پیدا نشد.',
            topText: 'آیتم‌های پیشنهادی',
            search: '',
            loading: false,
            items: [],
            topItems: [],
            searched: false,
            debounceTimer: null,
            searchUrl: null,
        },

        menuItems: config.menuItems ?? [],

        typeLabels: {
            custom_link: 'لینک اختصاصی',
            category: 'دسته‌بندی',
            tag: 'برچسب',
            post: 'نوشته',
            page: 'برگه',
            project: 'پروژه',
        },

        init() {
            this.normalizeInitialItems();
            this.bindIconPickerEvents();

            this.$watch('activeType', (value) => {
                if (this.isSearchableEntityType(value)) {
                    this.activateEntityPicker(value);
                } else {
                    this.resetEntityPickerState();
                }
            });

            if (this.isSearchableEntityType(this.activeType)) {
                this.activateEntityPicker(this.activeType);
            }

            this.$nextTick(() => {
                this.renderTree();
                this.bindTreeEvents();
            });
        },

        destroy() {
            this.destroySortables();

            if (this.iconSelectedListener) {
                window.removeEventListener('mwip-selected', this.iconSelectedListener);
            }

            if (this.iconClearedListener) {
                window.removeEventListener('mwip-cleared', this.iconClearedListener);
            }
        },

        normalizeInitialItems() {
            this.menuItems = this.normalizeItems(this.menuItems);
            this.nextId = this.resolveNextId(this.menuItems);
        },

        normalizeItems(items = []) {
            return items.map((item) => ({
                uid: item.uid ?? this.buildUid(),
                id: item.id ?? null,
                type: item.type,
                typeLabel: item.typeLabel ?? this.typeLabels[item.type] ?? item.type,
                title: item.title ?? '',
                url: item.url ?? '',
                referenceId: item.referenceId ?? null,
                isOpen: !!item.isOpen,
                target: item.target ?? '_self',
                icon: item.icon ?? '',
                iconSvg: item.iconSvg ?? null,
                badge: item.badge ?? '',
                cssClass: item.cssClass ?? '',
                isMegaMenu: !!item.isMegaMenu,
                megaMenuTitle: item.megaMenuTitle ?? '',
                megaMenuDescription: item.megaMenuDescription ?? '',
                megaMenuColumns: String(item.megaMenuColumns ?? '4'),
                megaMenuQuickLinks: this.normalizeMegaMenuQuickLinks(item.megaMenuQuickLinks),
                children: this.normalizeItems(item.children ?? []),
            }));
        },

        normalizeMegaMenuQuickLinks(links = []) {
            if (!Array.isArray(links)) {
                return [];
            }

            return links.slice(0, 12).map((link) => ({
                title: String(link?.title ?? ''),
                url: String(link?.url ?? ''),
                target: link?.target === '_blank' ? '_blank' : '_self',
            }));
        },

        resolveNextId(items = []) {
            let max = 100;

            const walk = (nodes) => {
                for (const item of nodes) {
                    const matched = String(item.uid ?? '').match(/(\d+)$/);

                    if (matched) {
                        max = Math.max(max, Number(matched[1]) + 1);
                    }

                    if (item.children?.length) {
                        walk(item.children);
                    }
                }
            };

            walk(items);

            return max;
        },

        isSearchableEntityType(type) {
            return ['category', 'tag', 'post', 'page', 'project'].includes(type);
        },

        activateEntityPicker(type) {
            const config = this.pickerConfigs[type];

            if (!config) {
                this.resetEntityPickerState();
                return;
            }

            this.entityPicker.type = type;
            this.entityPicker.label = config.label;
            this.entityPicker.emptyText = config.emptyText;
            this.entityPicker.topText = config.topText;
            this.entityPicker.search = '';
            this.entityPicker.loading = false;
            this.entityPicker.searched = false;
            this.entityPicker.searchUrl = config.searchUrl;
            this.entityPicker.topItems = [...config.topItems];
            this.entityPicker.items = [...config.topItems];
        },

        resetEntityPickerState() {
            this.entityPicker.type = null;
            this.entityPicker.label = '';
            this.entityPicker.emptyText = 'موردی پیدا نشد.';
            this.entityPicker.topText = 'آیتم‌های پیشنهادی';
            this.entityPicker.search = '';
            this.entityPicker.loading = false;
            this.entityPicker.items = [];
            this.entityPicker.topItems = [];
            this.entityPicker.searched = false;
            this.entityPicker.searchUrl = null;
        },

        onEntitySearchInput() {
            clearTimeout(this.entityPicker.debounceTimer);

            const query = this.entityPicker.search.trim();

            if (!query) {
                this.entityPicker.items = [...this.entityPicker.topItems];
                this.entityPicker.loading = false;
                this.entityPicker.searched = false;
                return;
            }

            this.entityPicker.items = [];
            this.entityPicker.loading = query.length >= 2;
            this.entityPicker.searched = false;

            if (query.length < 2) {
                this.entityPicker.loading = false;
                return;
            }

            this.entityPicker.debounceTimer = setTimeout(() => {
                this.searchEntities();
            }, 300);
        },

        async searchEntities() {
            const query = this.entityPicker.search.trim();

            if (!query) {
                this.entityPicker.items = [...this.entityPicker.topItems];
                this.entityPicker.loading = false;
                this.entityPicker.searched = false;
                return;
            }

            if (query.length < 2) {
                this.entityPicker.items = [];
                this.entityPicker.loading = false;
                this.entityPicker.searched = false;
                return;
            }

            if (!this.entityPicker.searchUrl) {
                this.entityPicker.items = [];
                this.entityPicker.loading = false;
                this.entityPicker.searched = true;
                return;
            }

            const requestedQuery = query;

            this.entityPicker.loading = true;
            this.entityPicker.searched = false;
            this.entityPicker.items = [];

            try {
                const url = new URL(this.entityPicker.searchUrl, window.location.origin);
                url.searchParams.set('q', requestedQuery);

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Entity search failed.');
                }

                const result = await response.json();

                if (this.entityPicker.search.trim() !== requestedQuery) {
                    return;
                }

                this.entityPicker.items = Array.isArray(result.items)
                    ? result.items.map((item) => ({
                        id: Number(item.value),
                        title: item.label,
                    }))
                    : [];

                this.entityPicker.searched = true;
            } catch (error) {
                console.error(error);

                if (this.entityPicker.search.trim() !== requestedQuery) {
                    return;
                }

                this.entityPicker.items = [];
                this.entityPicker.searched = true;
            } finally {
                if (this.entityPicker.search.trim() === requestedQuery) {
                    this.entityPicker.loading = false;
                }
            }
        },

        resetEntitySearch() {
            clearTimeout(this.entityPicker.debounceTimer);

            this.entityPicker.search = '';
            this.entityPicker.items = [...this.entityPicker.topItems];
            this.entityPicker.loading = false;
            this.entityPicker.searched = false;
        },

        buildUid() {
            return `menu-item-${this.nextId++}`;
        },

        makeBaseItem({
                         type,
                         title,
                         url = '',
                         referenceId = null,
                     }) {
            return {
                uid: this.buildUid(),
                id: null,
                type,
                typeLabel: this.typeLabels[type],
                title,
                url,
                referenceId,
                isOpen: false,
                target: '_self',
                icon: '',
                iconSvg: null,
                badge: '',
                cssClass: '',
                isMegaMenu: false,
                megaMenuTitle: '',
                megaMenuDescription: '',
                megaMenuColumns: '4',
                megaMenuQuickLinks: [],
                children: [],
            };
        },

        cloneMenuItem(item) {
            return {
                uid: item.uid,
                id: item.id ?? null,
                type: item.type,
                typeLabel: item.typeLabel,
                title: item.title,
                url: item.url,
                referenceId: item.referenceId ?? null,
                isOpen: !!item.isOpen,
                target: item.target ?? '_self',
                icon: item.icon ?? '',
                iconSvg: item.iconSvg ?? null,
                badge: item.badge ?? '',
                cssClass: item.cssClass ?? '',
                isMegaMenu: !!item.isMegaMenu,
                megaMenuTitle: item.megaMenuTitle ?? '',
                megaMenuDescription: item.megaMenuDescription ?? '',
                megaMenuColumns: String(item.megaMenuColumns ?? '4'),
                megaMenuQuickLinks: this.normalizeMegaMenuQuickLinks(item.megaMenuQuickLinks),
                children: [],
            };
        },

        addCustomLink() {
            const title = this.customLink.title.trim();
            const url = this.customLink.url.trim();

            if (!title || !url) {
                return;
            }

            this.menuItems.push(this.makeBaseItem({
                type: 'custom_link',
                title,
                url,
            }));

            this.customLink.title = '';
            this.customLink.url = '';
            this.fullRefresh();
        },

        addEntityItem(type, item) {
            this.menuItems.push(this.makeBaseItem({
                type,
                title: item.title,
                referenceId: item.id,
            }));

            if (this.isSearchableEntityType(type)) {
                this.resetEntitySearch();
            }

            this.fullRefresh();
        },

        countItems(items = this.menuItems) {
            let total = 0;

            for (const item of items) {
                total += 1;

                if (item.children?.length) {
                    total += this.countItems(item.children);
                }
            }

            return total;
        },

        escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        fullRefresh() {
            this.destroySortables();
            this.renderTree();
        },

        bindIconPickerEvents() {
            this.iconSelectedListener = (event) => {
                const detail = event.detail ?? {};
                const item = this.itemFromIconPickerEvent(detail);

                if (!item) {
                    return;
                }

                item.icon = String(detail.iconTitle ?? detail.iconName ?? '').trim();
                item.iconSvg = typeof detail.iconSvg === 'string' && detail.iconSvg.trim()
                    ? detail.iconSvg
                    : null;

                this.fullRefresh();
            };

            this.iconClearedListener = (event) => {
                const item = this.itemFromIconPickerEvent(event.detail ?? {});

                if (!item) {
                    return;
                }

                item.icon = '';
                item.iconSvg = null;

                this.fullRefresh();
            };

            window.addEventListener('mwip-selected', this.iconSelectedListener);
            window.addEventListener('mwip-cleared', this.iconClearedListener);
        },

        iconPickerItemKey(uid) {
            return `navigation-menu-item:${uid}`;
        },

        itemFromIconPickerEvent(detail) {
            const prefix = 'navigation-menu-item:';
            const itemKey = String(detail?.itemKey ?? '');

            if (!itemKey.startsWith(prefix)) {
                return null;
            }

            return this.findItem(itemKey.slice(prefix.length));
        },

        openIconPicker(uid) {
            const item = this.findItem(uid);

            if (!item) {
                return;
            }

            window.dispatchEvent(new CustomEvent('mwip-open-requested', {
                detail: {
                    itemKey: this.iconPickerItemKey(uid),
                    selectedIconName: item.icon || null,
                },
            }));
        },

        clearItemIcon(uid) {
            const item = this.findItem(uid);

            if (!item || (!item.icon && !item.iconSvg)) {
                return false;
            }

            item.icon = '';
            item.iconSvg = null;

            return true;
        },

        renderTree() {
            if (!this.$refs.treeRoot) {
                return;
            }

            this.$refs.treeRoot.innerHTML = this.buildChildrenHtml(this.menuItems, 0, true);
            this.mountNestedSortables();
        },

        buildChildrenHtml(items, level = 0, isRoot = false) {
            return `
                <ul class="nested-sortable space-y-2.5 ${
                isRoot ? '' : 'pr-5 mr-2 border-r-2 border-slate-200'
            }" data-root="${isRoot ? 'true' : 'false'}">
                    ${items.map((item) => this.buildItemHtml(item, level)).join('')}
                </ul>
            `;
        },

        buildMegaMenuQuickLinksHtml(item) {
            const links = item.megaMenuQuickLinks ?? [];
            const canAdd = links.length < 12;

            return `
                <div class="mt-5 border-t border-violet-200 pt-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h5 class="text-sm font-black text-violet-900">لینک‌های دسترسی سریع</h5>
                            <p class="mt-1 text-xs leading-6 text-violet-700/70">لینک‌های این بخش مستقل از زیرمنوها هستند؛ حداکثر ۱۲ لینک.</p>
                        </div>

                        <button type="button"
                                data-action="add-mega-quick-link"
                                data-uid="${item.uid}"
                                ${canAdd ? '' : 'disabled'}
                                class="inline-flex items-center gap-1.5 rounded-xl border border-violet-200 bg-white px-3 py-2 text-xs font-bold text-violet-700 transition hover:border-violet-300 hover:bg-violet-100 disabled:cursor-not-allowed disabled:opacity-50">
                            <span class="text-base leading-none">+</span>
                            افزودن لینک
                        </button>
                    </div>

                    ${links.length === 0 ? `
                        <div class="mt-4 rounded-2xl border border-dashed border-violet-200 bg-white/70 px-4 py-5 text-center text-xs text-violet-700/70">
                            هنوز لینکی برای دسترسی سریع تعریف نشده است.
                        </div>
                    ` : `
                        <div class="mt-4 space-y-3">
                            ${links.map((link, index) => `
                                <div class="rounded-2xl border border-violet-200 bg-white p-3">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <span class="text-xs font-black text-violet-900">لینک ${index + 1}</span>

                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                    data-action="move-mega-quick-link-up"
                                                    data-uid="${item.uid}"
                                                    data-quick-index="${index}"
                                                    ${index === 0 ? 'disabled' : ''}
                                                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-35"
                                                    title="انتقال به بالا">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="m7 14 5-5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>

                                            <button type="button"
                                                    data-action="move-mega-quick-link-down"
                                                    data-uid="${item.uid}"
                                                    data-quick-index="${index}"
                                                    ${index === links.length - 1 ? 'disabled' : ''}
                                                    class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-35"
                                                    title="انتقال به پایین">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>

                                            <button type="button"
                                                    data-action="remove-mega-quick-link"
                                                    data-uid="${item.uid}"
                                                    data-quick-index="${index}"
                                                    class="grid h-8 w-8 place-items-center rounded-lg border border-rose-200 text-rose-500 transition hover:bg-rose-50"
                                                    title="حذف لینک">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                                        <div class="md:col-span-4">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">عنوان</label>
                                            <input type="text"
                                                   data-quick-field="title"
                                                   data-uid="${item.uid}"
                                                   data-quick-index="${index}"
                                                   maxlength="120"
                                                   value="${this.escapeHtml(link.title)}"
                                                   placeholder="مثلاً دانلودها"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                        </div>

                                        <div class="md:col-span-5">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">آدرس لینک</label>
                                            <input type="text"
                                                   data-quick-field="url"
                                                   data-uid="${item.uid}"
                                                   data-quick-index="${index}"
                                                   maxlength="2048"
                                                   value="${this.escapeHtml(link.url)}"
                                                   placeholder="/downloads یا https://example.com"
                                                   dir="ltr"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left text-sm text-slate-800 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                        </div>

                                        <div class="md:col-span-3">
                                            <label class="mb-1.5 block text-xs font-bold text-slate-600">نحوه باز شدن</label>
                                            <select data-quick-field="target"
                                                    data-uid="${item.uid}"
                                                    data-quick-index="${index}"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                                <option value="_self" ${link.target === '_self' ? 'selected' : ''}>همین صفحه</option>
                                                <option value="_blank" ${link.target === '_blank' ? 'selected' : ''}>تب جدید</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `}
                </div>
            `;
        },

        buildItemHtml(item, level = 0) {
            const positionMeta = this.getItemPositionMeta(item.uid);

            const actionBtnBase =
                'inline-flex h-9 w-9 items-center justify-center rounded-xl border transition';

            const actionBtnEnabled =
                'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 cursor-pointer';

            const actionBtnDisabled =
                'border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed opacity-60';

            const megaBadge = item.isMegaMenu
                ? `<span class="inline-flex items-center rounded-xl bg-violet-50 px-2.5 py-1 text-[11px] font-bold text-violet-700">مگامنو</span>`
                : '';

            const badgeBadge = item.badge
                ? `<span class="rounded-lg bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700">${this.escapeHtml(item.badge)}</span>`
                : '';

            const settingsHidden = item.isOpen ? '' : 'hidden';
            const isRootItem = level === 0;
            const megaHidden = item.isMegaMenu && isRootItem ? '' : 'hidden';
            const isCustomLink = item.type === 'custom_link';
            const iconPreview = item.iconSvg
                ? item.iconSvg
                : (item.icon
                    ? `<span class="text-lg">${this.escapeHtml(item.icon)}</span>`
                    : `<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>`);

            return `
                <li class="sortable-item group list-none" data-uid="${item.uid}">
                    <div class="sortable-card overflow-hidden rounded-3xl border bg-white shadow-sm transition ${item.isOpen ? 'border-brand-200 ring-4 ring-brand-50' : 'border-slate-200 hover:border-slate-300'}">
                        <div class="flex items-center justify-between gap-4 px-4 py-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <button type="button"
                                        class="drag-handle flex h-10 w-10 shrink-0 cursor-move items-center justify-center rounded-2xl border border-slate-300 bg-slate-100 text-slate-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                                        title="جابجایی">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M6 5a1 1 0 1 1 0 2a1 1 0 0 1 0-2m0 6a1 1 0 1 1 0 2a1 1 0 0 1 0-2m0 6a1 1 0 1 1 0 2a1 1 0 0 1 0-2M18 5a1 1 0 1 1 0 2a1 1 0 0 1 0-2m0 6a1 1 0 1 1 0 2a1 1 0 0 1 0-2m-6 0a1 1 0 1 1 0 2a1 1 0 0 1 0-2m0-6a1 1 0 1 1 0 2a1 1 0 0 1 0-2m6 12a1 1 0 1 1 0 2a1 1 0 0 1 0-2m-6 0a1 1 0 1 1 0 2a1 1 0 0 1 0-2"/>
                                    </svg>
                                </button>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-sm font-black text-slate-900">${this.escapeHtml(item.title)}</h3>
                                        <span class="inline-flex items-center rounded-xl bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">${this.escapeHtml(item.typeLabel)}</span>
                                        ${megaBadge}
                                        ${badgeBadge}
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <div class="flex shrink-0 items-center gap-1 opacity-0 transition group-hover:opacity-100">
                                    <button type="button"
                                            data-action="move-up"
                                            data-uid="${item.uid}"
                                            ${positionMeta.canMoveUp ? '' : 'disabled'}
                                            class="${actionBtnBase} ${positionMeta.canMoveUp ? actionBtnEnabled : actionBtnDisabled}"
                                            title="انتقال به بالا">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 15s-4.42-6-6-6s-6 6-6 6"/></svg>
                                    </button>

                                    <button type="button"
                                            data-action="move-down"
                                            data-uid="${item.uid}"
                                            ${positionMeta.canMoveDown ? '' : 'disabled'}
                                            class="${actionBtnBase} ${positionMeta.canMoveDown ? actionBtnEnabled : actionBtnDisabled}"
                                            title="انتقال به پایین">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9s-4.419 6-6 6s-6-6-6-6"/></svg>
                                    </button>

                                    <button type="button"
                                            data-action="outdent"
                                            data-uid="${item.uid}"
                                            ${positionMeta.canOutdent ? '' : 'disabled'}
                                            class="${actionBtnBase} ${positionMeta.canOutdent ? actionBtnEnabled : actionBtnDisabled}"
                                            title="خارج کردن از زیرمجموعه">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 6s6 4.419 6 6s-6 6-6 6"/></svg>
                                    </button>

                                    <button type="button"
                                            data-action="indent"
                                            data-uid="${item.uid}"
                                            ${positionMeta.canIndent ? '' : 'disabled'}
                                            class="${actionBtnBase} ${positionMeta.canIndent ? actionBtnEnabled : actionBtnDisabled}"
                                            title="تبدیل به زیرمجموعه">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 6s-6 4.419-6 6s6 6 6 6"/></svg>
                                    </button>
                                </div>

                                <button type="button"
                                        data-action="toggle"
                                        data-uid="${item.uid}"
                                        class="cursor-pointer inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-[0.7rem] font-bold text-slate-700 transition hover:bg-slate-50">
                                    ${item.isOpen ? 'بستن تنظیمات' : 'تنظیمات'}
                                </button>

                                <button type="button"
                                        data-action="remove"
                                        data-uid="${item.uid}"
                                        class="cursor-pointer inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-[0.7rem] font-bold text-rose-600 transition hover:bg-rose-50">
                                    حذف
                                </button>
                            </div>
                        </div>

                        <div class="item-settings ${settingsHidden} border-t border-slate-200 bg-slate-50/70 px-4 py-4 sm:px-5">
                            <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                                <div class="xl:col-span-7 space-y-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700">عنوان آیتم</label>
                                        <input type="text" data-field="title" data-uid="${item.uid}" value="${this.escapeHtml(item.title)}"
                                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                    </div>

                                    ${isCustomLink ? `
                                        <div>
                                            <label class="mb-2 block text-sm font-bold text-slate-700">لینک</label>
                                            <input type="text" data-field="url" data-uid="${item.uid}" value="${this.escapeHtml(item.url)}"
                                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                        </div>
                                    ` : ''}

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="mb-2 block text-sm font-bold text-slate-700">آیکون</label>
                                            <div class="flex items-stretch gap-2">
                                                <button type="button"
                                                        data-action="select-icon"
                                                        data-uid="${item.uid}"
                                                        class="flex min-w-0 flex-1 cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-right transition hover:border-brand-300 hover:bg-brand-50/40 focus:outline-none focus:ring-4 focus:ring-brand-100">
                                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500 [&_svg]:h-5 [&_svg]:w-5">
                                                        ${iconPreview}
                                                    </span>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block truncate text-xs font-black ${item.icon ? 'text-slate-800' : 'text-slate-500'}">
                                                            ${item.icon ? this.escapeHtml(item.icon) : 'انتخاب از کتابخانه آیکون‌ها'}
                                                        </span>
                                                        <span class="mt-1 block text-[10px] text-slate-400">
                                                            ${item.icon ? 'برای تغییر آیکون کلیک کنید' : 'اختیاری'}
                                                        </span>
                                                    </span>
                                                </button>

                                                ${item.icon ? `
                                                    <button type="button"
                                                            data-action="clear-icon"
                                                            data-uid="${item.uid}"
                                                            class="grid w-11 shrink-0 cursor-pointer place-items-center rounded-2xl border border-rose-200 bg-white text-rose-500 transition hover:bg-rose-50"
                                                            title="حذف آیکون">
                                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                                    </button>
                                                ` : ''}
                                            </div>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-bold text-slate-700">Badge</label>
                                            <input type="text" data-field="badge" data-uid="${item.uid}" value="${this.escapeHtml(item.badge)}"
                                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                        </div>
                                    </div>
                                </div>

                                <div class="xl:col-span-5 space-y-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700">Target</label>
                                        <select data-field="target" data-uid="${item.uid}"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            <option value="_self" ${item.target === '_self' ? 'selected' : ''}>همین صفحه</option>
                                            <option value="_blank" ${item.target === '_blank' ? 'selected' : ''}>تب جدید</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700">کلاس سفارشی</label>
                                        <input type="text" data-field="cssClass" data-uid="${item.uid}" value="${this.escapeHtml(item.cssClass)}"
                                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                    </div>

                                    ${isRootItem ? `
                                        <div class="space-y-3">
                                            <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                <div>
                                                    <div class="text-sm font-black text-slate-900">فعال‌سازی مگامنو</div>
                                                    <div class="mt-1 text-xs text-slate-500">فقط برای آیتم‌های سطح اول</div>
                                                </div>
                                                <input type="checkbox" data-field="isMegaMenu" data-uid="${item.uid}" ${item.isMegaMenu ? 'checked' : ''}
                                                       class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            </label>
                                        </div>
                                    ` : ``}
                                </div>
                            </div>

                            <div class="mega-settings ${megaHidden} mt-4 rounded-3xl border border-violet-200 bg-violet-50/70 p-4">
                                <div class="mb-4">
                                    <h4 class="text-sm font-black text-violet-900">تنظیمات مگامنو</h4>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-violet-900">عنوان مگامنو</label>
                                        <input type="text" data-field="megaMenuTitle" data-uid="${item.uid}" value="${this.escapeHtml(item.megaMenuTitle)}"
                                               class="w-full rounded-2xl border border-violet-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-violet-900">توضیحات مگامنو</label>
                                        <input type="text" data-field="megaMenuDescription" data-uid="${item.uid}" value="${this.escapeHtml(item.megaMenuDescription)}"
                                               class="w-full rounded-2xl border border-violet-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-violet-900">تعداد ستون</label>
                                        <select data-field="megaMenuColumns" data-uid="${item.uid}"
                                                class="w-full rounded-2xl border border-violet-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                            <option value="2" ${item.megaMenuColumns === '2' ? 'selected' : ''}>2 ستون</option>
                                            <option value="3" ${item.megaMenuColumns === '3' ? 'selected' : ''}>3 ستون</option>
                                            <option value="4" ${item.megaMenuColumns === '4' ? 'selected' : ''}>4 ستون</option>
                                            <option value="5" ${item.megaMenuColumns === '5' ? 'selected' : ''}>5 ستون</option>
                                        </select>
                                    </div>
                                </div>

                                ${this.buildMegaMenuQuickLinksHtml(item)}
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        ${this.buildChildrenHtml(item.children || [], level + 1, false)}
                    </div>
                </li>
            `;
        },

        bindTreeEvents() {
            if (this.treeEventsBound || !this.$refs.treeRoot) {
                return;
            }

            this.treeEventsBound = true;

            this.$refs.treeRoot.addEventListener('click', (event) => {
                const actionEl = event.target.closest('[data-action]');

                if (!actionEl) {
                    return;
                }

                if (actionEl.hasAttribute('disabled')) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const action = actionEl.dataset.action;
                const uid = actionEl.dataset.uid;
                const quickIndex = Number(actionEl.dataset.quickIndex);

                if (action === 'select-icon') {
                    this.openIconPicker(uid);
                    return;
                }

                let changed = false;

                if (action === 'toggle') {
                    changed = this.toggleItem(uid);
                } else if (action === 'remove') {
                    changed = this.removeItem(uid);
                } else if (action === 'move-up') {
                    changed = this.moveItemUp(uid);
                } else if (action === 'move-down') {
                    changed = this.moveItemDown(uid);
                } else if (action === 'indent') {
                    changed = this.indentItem(uid);
                } else if (action === 'outdent') {
                    changed = this.outdentItem(uid);
                } else if (action === 'clear-icon') {
                    changed = this.clearItemIcon(uid);
                } else if (action === 'add-mega-quick-link') {
                    changed = this.addMegaMenuQuickLink(uid);
                } else if (action === 'remove-mega-quick-link') {
                    changed = this.removeMegaMenuQuickLink(uid, quickIndex);
                } else if (action === 'move-mega-quick-link-up') {
                    changed = this.moveMegaMenuQuickLink(uid, quickIndex, -1);
                } else if (action === 'move-mega-quick-link-down') {
                    changed = this.moveMegaMenuQuickLink(uid, quickIndex, 1);
                }

                if (changed) {
                    this.fullRefresh();
                }
            });

            this.$refs.treeRoot.addEventListener('input', (event) => {
                if (this.updateMegaMenuQuickLinkFromInput(event.target)) {
                    return;
                }

                const field = event.target.dataset.field;
                const uid = event.target.dataset.uid;

                if (!field || !uid) {
                    return;
                }

                const item = this.findItem(uid);

                if (!item) {
                    return;
                }

                item[field] = event.target.type === 'checkbox'
                    ? event.target.checked
                    : event.target.value;
            });

            this.$refs.treeRoot.addEventListener('change', (event) => {
                if (this.updateMegaMenuQuickLinkFromInput(event.target)) {
                    return;
                }

                const field = event.target.dataset.field;
                const uid = event.target.dataset.uid;

                if (!field || !uid) {
                    return;
                }

                const item = this.findItem(uid);

                if (!item) {
                    return;
                }

                item[field] = event.target.type === 'checkbox'
                    ? event.target.checked
                    : event.target.value;

                if (field === 'isMegaMenu') {
                    this.fullRefresh();
                }
            });

            this.$refs.treeRoot.addEventListener('focusout', (event) => {
                const field = event.target.dataset.field;
                const uid = event.target.dataset.uid;

                if (!field || !uid) {
                    return;
                }

                if (['title', 'badge'].includes(field)) {
                    this.fullRefresh();
                }
            });
        },

        updateMegaMenuQuickLinkFromInput(input) {
            const field = input?.dataset?.quickField;
            const uid = input?.dataset?.uid;
            const index = Number(input?.dataset?.quickIndex);

            if (!field || !uid || !['title', 'url', 'target'].includes(field) || !Number.isInteger(index)) {
                return false;
            }

            const item = this.findItem(uid);
            const link = item?.megaMenuQuickLinks?.[index];

            if (!link) {
                return false;
            }

            link[field] = input.value;

            return true;
        },

        addMegaMenuQuickLink(uid) {
            const item = this.findItem(uid);

            if (!item || !item.isMegaMenu || item.megaMenuQuickLinks.length >= 12) {
                return false;
            }

            item.megaMenuQuickLinks.push({
                title: '',
                url: '',
                target: '_self',
            });

            return true;
        },

        removeMegaMenuQuickLink(uid, index) {
            const item = this.findItem(uid);

            if (!item || !Number.isInteger(index) || !item.megaMenuQuickLinks[index]) {
                return false;
            }

            item.megaMenuQuickLinks.splice(index, 1);

            return true;
        },

        moveMegaMenuQuickLink(uid, index, direction) {
            const item = this.findItem(uid);
            const targetIndex = index + direction;

            if (
                !item ||
                !Number.isInteger(index) ||
                !Number.isInteger(targetIndex) ||
                !item.megaMenuQuickLinks[index] ||
                !item.megaMenuQuickLinks[targetIndex]
            ) {
                return false;
            }

            [item.megaMenuQuickLinks[index], item.megaMenuQuickLinks[targetIndex]] =
                [item.megaMenuQuickLinks[targetIndex], item.megaMenuQuickLinks[index]];

            return true;
        },

        getItemPositionMeta(uid) {
            const path = this.findItemPath(uid);

            if (!path) {
                return {
                    canMoveUp: false,
                    canMoveDown: false,
                    canIndent: false,
                    canOutdent: false,
                };
            }

            const index = path[path.length - 1];
            const parentPath = path.slice(0, -1);

            if (typeof index !== 'number') {
                return {
                    canMoveUp: false,
                    canMoveDown: false,
                    canIndent: false,
                    canOutdent: false,
                };
            }

            const list = this.getListByPath(parentPath);
            const isNested = path.length >= 3 && path[path.length - 2] === 'children';

            return {
                canMoveUp: index > 0,
                canMoveDown: index < list.length - 1,
                canIndent: index > 0,
                canOutdent: isNested,
            };
        },

        findItemPath(uid, items = this.menuItems, path = []) {
            for (let index = 0; index < items.length; index++) {
                const item = items[index];

                if (item.uid === uid) {
                    return [...path, index];
                }

                if (item.children?.length) {
                    const found = this.findItemPath(uid, item.children, [...path, index, 'children']);

                    if (found) {
                        return found;
                    }
                }
            }

            return null;
        },

        getListByPath(path) {
            let current = this.menuItems;

            for (const segment of path) {
                current = current[segment];
            }

            return current;
        },

        getItemByPath(path) {
            const parentPath = path.slice(0, -1);
            const index = path[path.length - 1];
            const list = this.getListByPath(parentPath);

            return list[index] ?? null;
        },

        moveItemUp(uid) {
            const path = this.findItemPath(uid);

            if (!path) {
                return false;
            }

            const parentPath = path.slice(0, -1);
            const index = path[path.length - 1];

            if (typeof index !== 'number' || index === 0) {
                return false;
            }

            const list = this.getListByPath(parentPath);

            [list[index - 1], list[index]] = [list[index], list[index - 1]];

            return true;
        },

        moveItemDown(uid) {
            const path = this.findItemPath(uid);

            if (!path) {
                return false;
            }

            const parentPath = path.slice(0, -1);
            const index = path[path.length - 1];

            if (typeof index !== 'number') {
                return false;
            }

            const list = this.getListByPath(parentPath);

            if (index >= list.length - 1) {
                return false;
            }

            [list[index], list[index + 1]] = [list[index + 1], list[index]];

            return true;
        },

        indentItem(uid) {
            const path = this.findItemPath(uid);

            if (!path) {
                return false;
            }

            const parentPath = path.slice(0, -1);
            const index = path[path.length - 1];

            if (typeof index !== 'number' || index === 0) {
                return false;
            }

            const list = this.getListByPath(parentPath);
            const item = list[index];
            const previousItem = list[index - 1];

            if (!previousItem) {
                return false;
            }

            list.splice(index, 1);

            previousItem.children = previousItem.children ?? [];
            previousItem.children.push(item);

            if (item.isMegaMenu) {
                item.isMegaMenu = false;
                item.megaMenuTitle = '';
                item.megaMenuQuickLinks = [];
            }

            return true;
        },

        outdentItem(uid) {
            const path = this.findItemPath(uid);

            if (!path) {
                return false;
            }

            const index = path[path.length - 1];
            const childrenKey = path[path.length - 2];
            const parentIndex = path[path.length - 3];

            if (
                typeof index !== 'number' ||
                childrenKey !== 'children' ||
                typeof parentIndex !== 'number'
            ) {
                return false;
            }

            const childListPath = path.slice(0, -1);
            const childList = this.getListByPath(childListPath);
            const [item] = childList.splice(index, 1);

            const grandParentListPath = path.slice(0, -3);
            const grandParentList = this.getListByPath(grandParentListPath);

            grandParentList.splice(parentIndex + 1, 0, item);

            return true;
        },

        mountNestedSortables() {
            this.destroySortables();

            if (!this.$refs.treeRoot) {
                return;
            }

            const frame = this.$refs.treeFrame;
            const lists = this.$refs.treeRoot.querySelectorAll('.nested-sortable');

            lists.forEach((list) => {
                const sortable = new Sortable(list, {
                    group: 'nested',
                    animation: 180,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    emptyInsertThreshold: 28,
                    handle: '.drag-handle',
                    draggable: '.sortable-item',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',

                    onStart: () => {
                        frame?.classList.add('is-dragging');
                    },

                    onEnd: () => {
                        frame?.classList.remove('is-dragging');
                        this.menuItems = this.serializeTree();
                        this.fullRefresh();
                    },
                });

                this.sortables.push(sortable);
            });
        },

        destroySortables() {
            this.sortables.forEach((sortable) => {
                try {
                    sortable.destroy();
                } catch (_) {
                    //
                }
            });

            this.sortables = [];
        },

        serializeTree() {
            const root = this.$refs.treeRoot.querySelector('.nested-sortable[data-root="true"]');

            if (!root) {
                return [];
            }

            return this.serializeList(root, 0);
        },

        serializeList(listEl, level = 0) {
            const directItems = Array.from(listEl.children).filter((child) =>
                child.classList.contains('sortable-item')
            );

            return directItems
                .map((itemEl) => {
                    const uid = itemEl.dataset.uid;
                    const oldItem = this.findItem(uid);

                    if (!oldItem) {
                        return null;
                    }

                    const cloned = this.cloneMenuItem(oldItem);
                    const childList = itemEl.querySelector(':scope > .mt-2 > .nested-sortable');

                    cloned.children = childList ? this.serializeList(childList, level + 1) : [];

                    if (level > 0) {
                        cloned.isMegaMenu = false;
                        cloned.megaMenuTitle = '';
                        cloned.megaMenuQuickLinks = [];
                    }

                    return cloned;
                })
                .filter(Boolean);
        },

        serializedItemsPayload() {
            return JSON.stringify(this.prepareItemsForSubmit(this.menuItems));
        },

        prepareItemsForSubmit(items = []) {
            return items.map((item) => ({
                id: item.id ?? null,
                uid: item.uid,
                type: item.type,
                title: item.title ?? '',
                url: item.type === 'custom_link' ? (item.url ?? '') : '',
                target: item.target ?? '_self',
                reference_id: item.referenceId ?? null,
                icon: item.icon ?? '',
                badge: item.badge ?? '',
                css_class: item.cssClass ?? '',
                is_mega_menu: !!item.isMegaMenu,
                mega_menu_title: item.megaMenuTitle ?? '',
                mega_menu_description: item.megaMenuDescription ?? '',
                mega_menu_columns: Number(item.megaMenuColumns ?? 4),
                mega_menu_quick_links: item.isMegaMenu
                    ? this.normalizeMegaMenuQuickLinks(item.megaMenuQuickLinks)
                    : [],
                children: this.prepareItemsForSubmit(item.children ?? []),
            }));
        },

        findItem(uid, items = this.menuItems) {
            for (const item of items) {
                if (item.uid === uid) {
                    return item;
                }

                if (item.children?.length) {
                    const found = this.findItem(uid, item.children);

                    if (found) {
                        return found;
                    }
                }
            }

            return null;
        },

        toggleItem(uid, items = this.menuItems) {
            for (const item of items) {
                if (item.uid === uid) {
                    item.isOpen = !item.isOpen;
                    return true;
                }

                if (item.children?.length) {
                    const found = this.toggleItem(uid, item.children);

                    if (found) {
                        return true;
                    }
                }
            }

            return false;
        },

        removeItem(uid, items = this.menuItems) {
            const index = items.findIndex((item) => item.uid === uid);

            if (index !== -1) {
                items.splice(index, 1);
                return true;
            }

            for (const item of items) {
                if (item.children?.length) {
                    const removed = this.removeItem(uid, item.children);

                    if (removed) {
                        return true;
                    }
                }
            }

            return false;
        },
    };
};
