<div
    class="mwip-root"
    x-data="{
        open: @entangle('opened').live,
        pendingId: @entangle('pendingIconId').live,
        selectedInfo: @entangle('selectedInfo').live,

        init() {
            this.$watch('open', (isOpen) => {
                document.documentElement.classList.toggle('mwip-scroll-lock', isOpen);

                if (isOpen) {
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                }
            });
        },

        openModal(itemKey, selectedIconName = null) {
            this.open = true;
            this.$wire.openSingleton(itemKey, selectedIconName);
        },

        closeModal() {
            this.open = false;
            this.$wire.closeModal();
            document.documentElement.classList.remove('mwip-scroll-lock');
        },

        copyName() {
            const name = this.selectedInfo?.full_name;
            if (name) navigator.clipboard?.writeText(name);
        },
    }"
    x-on:mwip-open-requested.window="openModal(
        $event.detail.itemKey,
        $event.detail.selectedIconName ?? null
    )"
    x-on:mwip-close-modal.window="open = false; document.documentElement.classList.remove('mwip-scroll-lock')"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        class="mwip-modal-wrap"
        role="dialog"
        aria-modal="true"
        aria-label="انتخاب آیکون"
        @keydown.escape.window="closeModal()"
    >
        <div class="mwip-backdrop" @click="closeModal()"></div>

        <div class="mwip-viewport">
            <div class="mwip-modal" @click.stop>
                <header class="mwip-header">
                    <div>
                        <div class="mwip-title">انتخاب آیکون</div>
                        <div class="mwip-subtitle">
                            جستجو و انتخاب از کتابخانه آیکون‌های پروژه
                        </div>
                    </div>

                    <button type="button" class="mwip-close" @click="closeModal()" aria-label="بستن">
                        @include('icons.cancel-01')
                    </button>
                </header>

                <div class="mwip-body">
                    <section class="mwip-browser">
                        <div class="mwip-toolbar">
                            <label class="mwip-search">
                                <span class="mwip-search-icon">@include('icons.search-01')</span>
                                <input
                                    x-ref="searchInput"
                                    type="search"
                                    wire:model.live.debounce.250ms="search"
                                    placeholder="جستجو با نام آیکون..."
                                    autocomplete="off"
                                >
                                <span wire:loading.delay wire:target="search" class="mwip-search-loader"></span>
                            </label>

                            <div class="mwip-result-count">
                                {{ number_format($icons->total()) }} آیکون
                            </div>
                        </div>

                        <div class="mwip-sets" aria-label="مجموعه آیکون‌ها">
                            <button
                                type="button"
                                wire:click="selectSet('')"
                                class="mwip-set {{ $activeSet === '' ? 'is-active' : '' }}"
                            >
                                همه مجموعه‌ها
                            </button>

                            @foreach($sets as $set)
                                <button
                                    type="button"
                                    wire:key="icon-set-{{ $set->id }}"
                                    wire:click="selectSet('{{ $set->key }}')"
                                    class="mwip-set {{ $activeSet === $set->key ? 'is-active' : '' }}"
                                >
                                    <span>{{ $set->label }}</span>
                                    <span class="mwip-set-count">{{ number_format($set->icons_count) }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="mwip-grid-wrap">
                            <div
                                wire:loading.delay
                                wire:target="search,activeSet,selectSet,previousPage,nextPage,gotoPage"
                                class="mwip-loading-overlay"
                            >
                                <span class="mwip-spinner"></span>
                                <span>در حال بارگذاری آیکون‌ها...</span>
                            </div>

                            @if($icons->count() === 0)
                                <div class="mwip-empty">
                                    <span class="mwip-empty-icon">@include('icons.search-01')</span>
                                    <div class="mwip-empty-title">آیکونی پیدا نشد</div>
                                    <div class="mwip-empty-text">
                                        عبارت دیگری جستجو کنید یا مجموعه متفاوتی را انتخاب کنید.
                                    </div>
                                </div>
                            @else
                                <div class="mwip-grid">
                                    @foreach($icons as $icon)
                                        <button
                                            type="button"
                                            wire:key="icon-picker-item-{{ $icon->id }}"
                                            wire:click="previewSelection({{ $icon->id }})"
                                            class="mwip-icon-tile {{ (int)$pendingIconId === (int)$icon->id ? 'is-selected' : '' }}"
                                            title="{{ $icon->full_name }}"
                                        >
                                            <span class="mwip-icon-tile-svg">{!! $icon->svg !!}</span>
                                            <span class="mwip-icon-tile-name">{{ $icon->name }}</span>
                                            <span class="mwip-icon-tile-set">{{ $icon->set?->label }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if($icons->hasPages())
                            <div class="mwip-pagination">
                                <button
                                    type="button"
                                    wire:click="previousPage"
                                    wire:loading.attr="disabled"
                                    @disabled($icons->onFirstPage())
                                >
                                    صفحه قبل
                                </button>

                                <span>
                                    صفحه {{ number_format($icons->currentPage()) }} از {{ number_format($icons->lastPage()) }}
                                </span>

                                <button
                                    type="button"
                                    wire:click="nextPage"
                                    wire:loading.attr="disabled"
                                    @disabled(!$icons->hasMorePages())
                                >
                                    صفحه بعد
                                </button>
                            </div>
                        @endif
                    </section>

                    <aside class="mwip-preview-panel">
                        <template x-if="selectedInfo?.id">
                            <div class="mwip-preview-content">
                                <div class="mwip-preview-label">پیش‌نمایش آیکون</div>

                                <div class="mwip-preview-box">
                                    <span class="mwip-preview-svg" x-html="selectedInfo.svg"></span>
                                </div>

                                <div class="mwip-preview-details">
                                    <div class="mwip-preview-name" x-text="selectedInfo.full_name"></div>

                                    <div class="mwip-preview-meta">
                                        <span x-text="selectedInfo.set_label"></span>
                                        <span>•</span>
                                        <span x-text="selectedInfo.name"></span>
                                    </div>

                                    <button type="button" class="mwip-copy" @click="copyName()">
                                        کپی نام آیکون
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="!selectedInfo?.id">
                            <div class="mwip-preview-empty">
                                <span class="mwip-preview-empty-icon">@include('icons.image-03')</span>
                                <div class="mwip-preview-empty-title">آیکونی انتخاب نشده است</div>
                                <div class="mwip-preview-empty-text">
                                    یکی از آیکون‌های کتابخانه را برای مشاهده پیش‌نمایش انتخاب کنید.
                                </div>
                            </div>
                        </template>
                    </aside>
                </div>

                <footer class="mwip-footer">
                    <button
                        type="button"
                        class="mwip-button mwip-button--danger"
                        x-show="selectedInfo?.id"
                        x-cloak
                        wire:click="clearSelection"
                    >
                        حذف انتخاب
                    </button>

                    <div class="mwip-footer-actions">
                        <button type="button" class="mwip-button mwip-button--secondary" @click="closeModal()">
                            انصراف
                        </button>

                        <button
                            type="button"
                            class="mwip-button mwip-button--primary"
                            wire:click="confirmSelection"
                            wire:loading.attr="disabled"
                            wire:target="confirmSelection"
                            @disabled(!$pendingIconId)
                        >
                            تایید آیکون
                        </button>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</div>
