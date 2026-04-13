@extends('admin.layout')

@section('title', 'فهرست ها - ' . $navigationMenu->title)

@section('content')
    <x-admin.form action="{{ route('admin.navigation-menus.update', $navigationMenu) }}" method="put">
        <div x-data="navigationManager()" x-init="init()" class="space-y-6">
            <x-admin.page :columns="2" :sidebarSpan="3" sidebarPosition="right">
                <x-slot:sidebar>
                    @include('admin.navigation.menus.partials.add-item-box')
                </x-slot:sidebar>

                @include('admin.navigation.menus.partials.items-box')
            </x-admin.page>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

        <script>
            function navigationManager() {
                return {
                    activeType: 'custom_link',
                    nextId: 100,
                    sortables: [],
                    treeEventsBound: false,

                    customLink: {
                        title: '',
                        url: '',
                    },

                    pickerConfigs: {
                        category: {
                            label: 'جستجوی دسته‌بندی',
                            emptyText: 'دسته‌بندی‌ای پیدا نشد.',
                            topText: 'دسته‌بندی‌های پیشنهادی',
                            searchUrl: @json(route('admin.categories.ajax-search')),
                            topItems: @json($topCategories ?? []),
                        },

                        tag: {
                            label: 'جستجوی برچسب',
                            emptyText: 'برچسبی پیدا نشد.',
                            topText: 'برچسب‌های پیشنهادی',
                            searchUrl: @json(route('admin.tags.ajax-search')),
                            topItems: @json($topTags ?? []),
                        },

                        post: {
                            label: 'جستجوی نوشته',
                            emptyText: 'نوشته‌ای پیدا نشد.',
                            topText: 'نوشته‌های پیشنهادی',
                            searchUrl: @json(route('admin.posts.ajax-search')),
                            topItems: @json($topPosts ?? []),
                        },

                        page: {
                            label: 'جستجوی برگه',
                            emptyText: 'برگه‌ای پیدا نشد.',
                            topText: 'برگه‌های پیشنهادی',
                            searchUrl: @json(route('admin.pages.ajax-search')),
                            topItems: @json($topPages ?? []),
                        },

                        project: {
                            label: 'جستجوی پروژه',
                            emptyText: 'پروژه‌ای پیدا نشد.',
                            topText: 'پروژه‌های پیشنهادی',
                            searchUrl: @json(route('admin.projects.ajax-search')),
                            topItems: @json($topProjects ?? []),
                        },
                    },

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
                            this.entityPicker.searched = true;
                            return;
                        }

                        if (!this.entityPicker.searchUrl) {
                            return;
                        }

                        this.entityPicker.loading = true;
                        this.entityPicker.searched = true;

                        try {
                            const url = new URL(this.entityPicker.searchUrl, window.location.origin);
                            url.searchParams.set('q', query);

                            const response = await fetch(url.toString(), {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            if (!response.ok) {
                                throw new Error('Entity search failed.');
                            }

                            const result = await response.json();

                            this.entityPicker.items = Array.isArray(result.items)
                                ? result.items.map((item) => ({
                                    id: Number(item.value),
                                    title: item.label,
                                    path: item.path ?? '',
                                }))
                                : [];
                        } catch (error) {
                            console.error(error);
                            this.entityPicker.items = [];
                        } finally {
                            this.entityPicker.loading = false;
                        }
                    },

                    resetEntitySearch() {
                        this.entityPicker.search = '';
                        this.entityPicker.items = [...this.entityPicker.topItems];
                        this.entityPicker.loading = false;
                        this.entityPicker.searched = false;
                    },

                    menuItems: [
                        {
                            uid: 'menu-item-1',
                            type: 'custom_link',
                            typeLabel: 'لینک اختصاصی',
                            title: 'خانه',
                            url: '/',
                            sourceId: null,
                            isOpen: false,
                            isActive: true,
                            target: '_self',
                            icon: 'home-03',
                            badge: '',
                            cssClass: '',
                            isMegaMenu: false,
                            megaMenuTitle: '',
                            megaMenuColumns: '4',
                            megaMenuStyle: 'default',
                            children: [],
                        },
                        {
                            uid: 'menu-item-2',
                            type: 'category',
                            typeLabel: 'Category',
                            title: 'بازی‌ها',
                            url: '/categories/games',
                            sourceId: 10,
                            isOpen: true,
                            isActive: true,
                            target: '_self',
                            icon: '',
                            badge: 'محبوب',
                            cssClass: '',
                            isMegaMenu: true,
                            megaMenuTitle: 'دسترسی سریع بازی‌ها',
                            megaMenuColumns: '4',
                            megaMenuStyle: 'default',
                            children: [
                                {
                                    uid: 'menu-item-3',
                                    type: 'category',
                                    typeLabel: 'Category',
                                    title: 'PES',
                                    url: '/categories/pes',
                                    sourceId: 1,
                                    isOpen: false,
                                    isActive: true,
                                    target: '_self',
                                    icon: '',
                                    badge: '',
                                    cssClass: '',
                                    isMegaMenu: false,
                                    megaMenuTitle: '',
                                    megaMenuColumns: '4',
                                    megaMenuStyle: 'default',
                                    children: [],
                                },
                                {
                                    uid: 'menu-item-4',
                                    type: 'category',
                                    typeLabel: 'Category',
                                    title: 'FIFA',
                                    url: '/categories/fifa',
                                    sourceId: 2,
                                    isOpen: false,
                                    isActive: true,
                                    target: '_self',
                                    icon: '',
                                    badge: '',
                                    cssClass: '',
                                    isMegaMenu: false,
                                    megaMenuTitle: '',
                                    megaMenuColumns: '4',
                                    megaMenuStyle: 'default',
                                    children: [],
                                },
                            ],
                        },
                        {
                            uid: 'menu-item-5',
                            type: 'page',
                            typeLabel: 'Page',
                            title: 'تماس با ما',
                            url: '/contact-us',
                            sourceId: 2,
                            isOpen: false,
                            isActive: true,
                            target: '_self',
                            icon: '',
                            badge: '',
                            cssClass: '',
                            isMegaMenu: false,
                            megaMenuTitle: '',
                            megaMenuColumns: '4',
                            megaMenuStyle: 'default',
                            children: [],
                        },
                    ],

                    typeLabels: {
                        custom_link: 'لینک اختصاصی',
                        category: 'Category',
                        tag: 'Tag',
                        post: 'Post',
                        page: 'Page',
                        project: 'Project',
                    },

                    init() {
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

                    buildUid() {
                        return `menu-item-${this.nextId++}`;
                    },

                    makeBaseItem(type, title, url, sourceId = null) {
                        return {
                            uid: this.buildUid(),
                            type,
                            typeLabel: this.typeLabels[type],
                            title,
                            url,
                            sourceId,
                            isOpen: true,
                            isActive: true,
                            target: '_self',
                            icon: '',
                            badge: '',
                            cssClass: '',
                            isMegaMenu: false,
                            megaMenuTitle: '',
                            megaMenuColumns: '4',
                            megaMenuStyle: 'default',
                            children: [],
                        };
                    },

                    cloneMenuItem(item) {
                        return {
                            uid: item.uid,
                            type: item.type,
                            typeLabel: item.typeLabel,
                            title: item.title,
                            url: item.url,
                            sourceId: item.sourceId ?? null,
                            isOpen: !!item.isOpen,
                            isActive: !!item.isActive,
                            target: item.target ?? '_self',
                            icon: item.icon ?? '',
                            badge: item.badge ?? '',
                            cssClass: item.cssClass ?? '',
                            isMegaMenu: !!item.isMegaMenu,
                            megaMenuTitle: item.megaMenuTitle ?? '',
                            megaMenuColumns: item.megaMenuColumns ?? '4',
                            megaMenuStyle: item.megaMenuStyle ?? 'default',
                            children: [],
                        };
                    },

                    addCustomLink() {
                        const title = this.customLink.title.trim();
                        const url = this.customLink.url.trim();

                        if (!title || !url) return;

                        this.menuItems.unshift(this.makeBaseItem('custom_link', title, url));
                        this.customLink.title = '';
                        this.customLink.url = '';
                        this.fullRefresh();
                    },

                    addEntityItem(type, item) {
                        this.menuItems.unshift(this.makeBaseItem(type, item.title, item.path, item.id));

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

                    renderTree() {
                        if (!this.$refs.treeRoot) return;
                        this.$refs.treeRoot.innerHTML = this.buildChildrenHtml(this.menuItems, 0, true);
                        this.mountNestedSortables();
                    },

                    buildChildrenHtml(items, level = 0, isRoot = false) {
                        return `
                            <ul class="nested-sortable space-y-2.5 ${
                            isRoot
                                ? ''
                                : 'pr-4 border-r border-slate-200/80'
                        }" data-root="${isRoot ? 'true' : 'false'}">
                                ${items.map(item => this.buildItemHtml(item, level)).join('')}
                            </ul>
                        `;
                    },

                    buildItemHtml(item, level = 0) {
                        const inactiveBadge = !item.isActive
                            ? `<span class="inline-flex items-center rounded-xl bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-600">غیرفعال</span>`
                            : '';

                        const megaBadge = item.isMegaMenu
                            ? `<span class="inline-flex items-center rounded-xl bg-violet-50 px-2.5 py-1 text-[11px] font-bold text-violet-700">مگامنو</span>`
                            : '';

                        const levelBadge = level > 0
                            ? `<span class="inline-flex items-center rounded-xl border border-sky-200 bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700">زیرمجموعه سطح ${level + 1}</span>`
                            : '';

                        const iconBadge = item.icon
                            ? `<span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600">آیکون: ${this.escapeHtml(item.icon)}</span>`
                            : '';

                        const badgeBadge = item.badge
                            ? `<span class="rounded-lg bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700">${this.escapeHtml(item.badge)}</span>`
                            : '';

                        const childBadge = item.children?.length
                            ? `<span class="rounded-lg bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-700">${item.children.length} زیرآیتم</span>`
                            : '';

                        const settingsHidden = item.isOpen ? '' : 'hidden';
                        const megaHidden = item.isMegaMenu ? '' : 'hidden';

                        return `
                            <li class="sortable-item list-none" data-uid="${item.uid}">
                                <div class="sortable-card overflow-hidden rounded-3xl border bg-white shadow-sm transition ${item.isOpen ? 'border-brand-200 ring-4 ring-brand-50' : 'border-slate-200 hover:border-slate-300'}">
                                    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-5">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <button type="button"
                                                    class="drag-handle flex h-11 w-11 shrink-0 cursor-move items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 transition hover:bg-slate-100"
                                                    title="جابجایی">
                                                ⋮⋮
                                            </button>

                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="truncate text-sm font-black text-slate-900">${this.escapeHtml(item.title)}</h3>
                                                    <span class="inline-flex items-center rounded-xl bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">${this.escapeHtml(item.typeLabel)}</span>
                                                    ${levelBadge}
                                                    ${megaBadge}
                                                    ${inactiveBadge}
                                                </div>

                                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                                    <span class="truncate dir-ltr text-left">${this.escapeHtml(item.url)}</span>
                                                    ${iconBadge}
                                                    ${badgeBadge}
                                                    ${childBadge}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 items-center gap-2">
                                            <button type="button"
                                                    data-action="toggle"
                                                    data-uid="${item.uid}"
                                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                                                ${item.isOpen ? 'بستن تنظیمات' : 'تنظیمات'}
                                            </button>

                                            <button type="button"
                                                    data-action="remove"
                                                    data-uid="${item.uid}"
                                                    class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50">
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

                                                <div>
                                                    <label class="mb-2 block text-sm font-bold text-slate-700">لینک</label>
                                                    <input type="text" data-field="url" data-uid="${item.uid}" value="${this.escapeHtml(item.url)}"
                                                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                                </div>

                                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                    <div>
                                                        <label class="mb-2 block text-sm font-bold text-slate-700">آیکون</label>
                                                        <input type="text" data-field="icon" data-uid="${item.uid}" value="${this.escapeHtml(item.icon)}"
                                                               placeholder="مثلاً home-03"
                                                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                                    </div>

                                                    <div>
                                                        <label class="mb-2 block text-sm font-bold text-slate-700">Badge</label>
                                                        <input type="text" data-field="badge" data-uid="${item.uid}" value="${this.escapeHtml(item.badge)}"
                                                               placeholder="مثلاً جدید"
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
                                                           placeholder="مثلاً nav-item-featured"
                                                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                                </div>

                                                <div class="space-y-3">
                                                    <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                        <div>
                                                            <div class="text-sm font-black text-slate-900">وضعیت آیتم</div>
                                                            <div class="mt-1 text-xs text-slate-500">نمایش یا عدم نمایش در فهرست</div>
                                                        </div>
                                                        <input type="checkbox" data-field="isActive" data-uid="${item.uid}" ${item.isActive ? 'checked' : ''}
                                                               class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                    </label>

                                                    <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                        <div>
                                                            <div class="text-sm font-black text-slate-900">فعال‌سازی مگامنو</div>
                                                            <div class="mt-1 text-xs text-slate-500">برای آیتم‌های سطح بالا</div>
                                                        </div>
                                                        <input type="checkbox" data-field="isMegaMenu" data-uid="${item.uid}" ${item.isMegaMenu ? 'checked' : ''}
                                                               class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                    </label>
                                                </div>
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
                                                    <label class="mb-2 block text-sm font-bold text-violet-900">تعداد ستون</label>
                                                    <select data-field="megaMenuColumns" data-uid="${item.uid}"
                                                            class="w-full rounded-2xl border border-violet-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                                        <option value="2" ${item.megaMenuColumns === '2' ? 'selected' : ''}>2 ستون</option>
                                                        <option value="3" ${item.megaMenuColumns === '3' ? 'selected' : ''}>3 ستون</option>
                                                        <option value="4" ${item.megaMenuColumns === '4' ? 'selected' : ''}>4 ستون</option>
                                                        <option value="5" ${item.megaMenuColumns === '5' ? 'selected' : ''}>5 ستون</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="mb-2 block text-sm font-bold text-violet-900">استایل</label>
                                                    <select data-field="megaMenuStyle" data-uid="${item.uid}"
                                                            class="w-full rounded-2xl border border-violet-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                                        <option value="default" ${item.megaMenuStyle === 'default' ? 'selected' : ''}>پیش‌فرض</option>
                                                        <option value="highlighted" ${item.megaMenuStyle === 'highlighted' ? 'selected' : ''}>برجسته</option>
                                                        <option value="compact" ${item.megaMenuStyle === 'compact' ? 'selected' : ''}>فشرده</option>
                                                    </select>
                                                </div>
                                            </div>
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
                        if (this.treeEventsBound || !this.$refs.treeRoot) return;
                        this.treeEventsBound = true;

                        this.$refs.treeRoot.addEventListener('click', (event) => {
                            const actionEl = event.target.closest('[data-action]');
                            if (!actionEl) return;

                            const action = actionEl.dataset.action;
                            const uid = actionEl.dataset.uid;

                            if (action === 'toggle') {
                                this.toggleItem(uid);
                                this.fullRefresh();
                            }

                            if (action === 'remove') {
                                this.removeItem(uid);
                                this.fullRefresh();
                            }
                        });

                        this.$refs.treeRoot.addEventListener('input', (event) => {
                            const field = event.target.dataset.field;
                            const uid = event.target.dataset.uid;
                            if (!field || !uid) return;

                            const item = this.findItem(uid);
                            if (!item) return;

                            item[field] = event.target.type === 'checkbox'
                                ? event.target.checked
                                : event.target.value;

                            if (field === 'isMegaMenu' || ['title', 'url', 'icon', 'badge'].includes(field)) {
                                this.fullRefresh();
                            }
                        });

                        this.$refs.treeRoot.addEventListener('change', (event) => {
                            const field = event.target.dataset.field;
                            const uid = event.target.dataset.uid;
                            if (!field || !uid) return;

                            const item = this.findItem(uid);
                            if (!item) return;

                            item[field] = event.target.type === 'checkbox'
                                ? event.target.checked
                                : event.target.value;

                            if (field === 'isMegaMenu') {
                                this.fullRefresh();
                            }
                        });
                    },

                    mountNestedSortables() {
                        this.destroySortables();

                        if (typeof Sortable === 'undefined' || !this.$refs.treeRoot) return;

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
                        this.sortables.forEach(sortable => {
                            try {
                                sortable.destroy();
                            } catch (_) {
                            }
                        });
                        this.sortables = [];
                    },

                    serializeTree() {
                        const root = this.$refs.treeRoot.querySelector('.nested-sortable[data-root="true"]');
                        if (!root) return [];
                        return this.serializeList(root);
                    },

                    serializeList(listEl) {
                        const directItems = Array.from(listEl.children).filter(
                            child => child.classList.contains('sortable-item')
                        );

                        return directItems.map((itemEl) => {
                            const uid = itemEl.dataset.uid;
                            const oldItem = this.findItem(uid);

                            if (!oldItem) {
                                return null;
                            }

                            const cloned = this.cloneMenuItem(oldItem);

                            const childList = itemEl.querySelector(':scope > .mt-2 > .nested-sortable');

                            cloned.children = childList ? this.serializeList(childList) : [];

                            return cloned;
                        }).filter(Boolean);
                    },

                    findItem(uid, items = this.menuItems) {
                        for (const item of items) {
                            if (item.uid === uid) return item;

                            if (item.children?.length) {
                                const found = this.findItem(uid, item.children);
                                if (found) return found;
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
                                if (found) return true;
                            }
                        }

                        return false;
                    },

                    removeItem(uid, items = this.menuItems) {
                        const index = items.findIndex(item => item.uid === uid);
                        if (index !== -1) {
                            items.splice(index, 1);
                            return true;
                        }

                        for (const item of items) {
                            if (item.children?.length) {
                                const removed = this.removeItem(uid, item.children);
                                if (removed) return true;
                            }
                        }

                        return false;
                    },
                };
            }
        </script>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection

@push('styles')
    @vite(['resources/css/admin/navigation-menu.css'])
@endpush
