<div
    class="mfip-root"
    x-data="{
        open: @js($opened),
        tab: 'library',

        selectedId: @entangle('selectedMediaFileId').live,
        pendingId: @entangle('pendingMediaFileId').live,
        selectedUrl: @entangle('selectedUrl').live,
        selectedInfo: @entangle('selectedInfo').live,

        titleDraft: '',
        optimisticInfo: null,
        uploading: false,
        uploadProgress: 0,

        get previewInfo(){
            return this.optimisticInfo ?? this.selectedInfo ?? null;
        },

        init(){
            this.tab = this.selectedUrl ? 'library' : 'upload';
            this.titleDraft = this.selectedInfo?.title ?? '';

            this.$watch('selectedInfo', (v) => {
                this.titleDraft = v?.title ?? '';
                if (v?.id && v.id === this.selectedId) {
                    this.optimisticInfo = null;
                }
            });

            this.$watch('selectedUrl', () => {
                this.$nextTick(() => {
                    const slot = this.$refs.previewSlot;
                    if(!slot) return;
                    slot.classList.remove('is-loaded','is-error');
                    this.ensureImgLoaded(slot);
                });
            });

            this.$nextTick(() => {
                const root = this.$el;
                const gridWrap = root.querySelector('.mfip-gridWrap');
                if(!gridWrap) return;

                const run = () => {
                    gridWrap.querySelectorAll('.mfip-imgSlot').forEach((slot) => {
                        if (slot.classList.contains('is-loaded') || slot.classList.contains('is-error')) return;
                        this.ensureImgLoaded(slot);
                    });
                };

                run();

                const obs = new MutationObserver(() => run());
                obs.observe(gridWrap, { childList: true, subtree: true });

                this.__mfipObs = obs;
            });

            this.markCachedImagesLoaded();

            document.addEventListener('livewire:morph.updated', () => {
                this.markCachedImagesLoaded();
            });
        },

        pick(id, e) {
            if (Number(this.selectedId) === Number(id)) {
                this.clearSelection();
                return;
            }

            const btn = e?.currentTarget;

            this.selectedId = id;

            const url = btn?.dataset?.url || null;
            this.selectedUrl = url;

            this.optimisticInfo = {
                id: id,
                title: btn?.dataset?.title || null,
                url: url || null,
                path: btn?.dataset?.path || null,
                size_human: '…',
            };

            this.titleDraft = this.optimisticInfo.title ?? '';
            this.$wire.previewSelection(id);
        },

        confirmDelete(){
            if(!this.selectedId) return;
            const ok = confirm('حذف شود؟ این عملیات غیرقابل بازگشت است و همه فایل‌ها/نسخه‌ها حذف می‌شوند.');
            if(!ok) return;
            this.$wire.deleteSelected();
        },

        clearSelection() {
            this.selectedId = null;
            this.selectedUrl = null;
            this.optimisticInfo = null;
            this.selectedInfo = {
                id: null,
                title: null,
                url: null,
                path: null,
                size_bytes: null,
                size_human: null,
            };
            this.titleDraft = '';
            this.$wire.unselect();
        },

        saveTitle(){
            if(!this.selectedId) return;
            this.optimisticInfo = null;
            this.$wire.updateSelectedTitle(this.titleDraft);
        },

        ensureImgLoaded(el){
            const img = el?.querySelector('img');
            if(!img) return;

            el.classList.remove('is-loaded','is-error');
            el.classList.add('is-loading');

            const setLoaded = () => {
                el.classList.remove('is-loading');
                el.classList.add('is-loaded');
            };

            const setError = () => {
                el.classList.remove('is-loading');
                el.classList.add('is-error');
            };

            if (img.complete) {
                if (img.naturalWidth > 0) setLoaded();
                else setError();
                return;
            }

            img.addEventListener('load', setLoaded, { once: true });
            img.addEventListener('error', setError, { once: true });
        },

        markCachedImagesLoaded(){
            this.$nextTick(() => {
                document.querySelectorAll('.mfip-root .mfip-imgSlot').forEach((slot) => {
                    if (slot.classList.contains('is-loaded') || slot.classList.contains('is-error')) return;

                    const img = slot.querySelector('img');
                    if (!img) return;

                    if (img.complete) {
                        if (img.naturalWidth > 0) slot.classList.add('is-loaded');
                        else slot.classList.add('is-error');
                    }
                });
            });
        },

        copyText(text){
            if(!text) return;
            navigator.clipboard?.writeText(text);
        }
    }"
    x-on:mfip-modal-opened.window="open = true"
    x-on:mfip-close-modal.window="open = false"
    x-on:mfip-switch-tab.window="tab = $event.detail.tab"
    x-on:mfip-upload-saved.window="uploading = false; uploadProgress = 0"

    x-on:livewire-upload-start.window="uploading = true; uploadProgress = 0"
    x-on:livewire-upload-progress.window="uploadProgress = $event.detail.progress"
    x-on:livewire-upload-finish.window="uploadProgress = 100"
    x-on:livewire-upload-error.window="uploading = false; uploadProgress = 0"
>
    <!-- Modal -->
    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        @keydown.escape.window="open = false"
        class="mfip-modalWrap"
        aria-modal="true"
        role="dialog"
    >
        <div class="mfip-modalBackdrop" @click="open = false"></div>

        <div class="mfip-modalViewport">
            <div class="mfip-modal">
                <!-- Header -->
                <div class="mfip-modalHeader">
                    <div class="mfip-modalHeaderText">
                        <div class="mfip-modalTitle">انتخاب رسانه</div>
                        <div class="mfip-modalSub">کتابخانه / آپلود</div>
                    </div>

                    <button type="button" class="mfip-button mfip-button--ghost mfip-button--sm" @click="open = false">
                        بستن
                    </button>
                </div>

                <!-- Body -->
                <div class="mfip-modalBody">
                    <!-- Tabs -->
                    <div class="mfip-tabs">
                        <button type="button" class="mfip-tab" :class="tab === 'library' ? 'is-active' : ''"
                                @click="tab = 'library'">
                            کتابخانه
                        </button>
                        <button type="button" class="mfip-tab" :class="tab === 'upload' ? 'is-active' : ''"
                                @click="tab = 'upload'">
                            آپلود
                        </button>
                    </div>

                    <div class="mfip-body" :class="(tab === 'library' && selectedUrl) ? 'has-preview' : 'no-preview'">
                        <!-- LEFT -->
                        <div class="mfip-left">
                            <!-- Library -->
                            <div x-show="tab === 'library'">
                                <div class="mfip-searchRow">
                                    <input
                                        type="text"
                                        placeholder="جستجو (id / path / checksum)..."
                                        wire:model.live.debounce.350ms="search"
                                        class="mfip-input"
                                    >
                                </div>

                                <!-- Upload progress bar (WP-like top bar) -->
                                <div class="mfip-uploadBar" x-show="uploading || $wire.__instance?.isLoading" x-cloak>
                                    <div class="mfip-uploadBarTop">
                                        <div class="mfip-uploadBarTitle">
                                            <span class="mfip-dot"></span>
                                            در حال آپلود...
                                        </div>
                                        <div class="mfip-uploadBarPct" x-text="uploadProgress + '%'"></div>
                                    </div>
                                    <div class="mfip-progressTrack">
                                        <div class="mfip-progressFill" :style="`width:${uploadProgress}%`"></div>
                                    </div>

                                    <!-- server-side store phase -->
                                    <div class="mfip-uploadBarNote" wire:loading wire:target="saveUpload">
                                        در حال ذخیره‌سازی روی سرور...
                                    </div>
                                </div>

                                <div class="mfip-gridWrap">
                                    <div class="mfip-grid">
                                        @forelse ($library as $media)
                                            @php
                                                $url = $media->primaryVariant
                                                    ? \Illuminate\Support\Facades\Storage::disk($media->primaryVariant->disk->value)->url($media->primaryVariant->path)
                                                    : null;
                                            @endphp

                                            <button
                                                type="button"
                                                class="mfip-tile"
                                                :class="Number(pendingId) === {{ $media->id }} ? 'is-selected' : ''"
                                                data-url="{{ $url ?? '' }}"
                                                data-title="{{ e($media->title ?? '') }}"
                                                data-path="{{ e($media->primaryVariant?->path ?? '') }}"
                                                @click="pick({{ $media->id }}, $event)"
                                                wire:key="mfip-media-{{ $media->id }}"
                                            >
                                                <div class="mfip-square mfip-tileAspect">
                                                    @if($url)
                                                        <div class="mfip-imgSlot is-loading"
                                                             x-init="ensureImgLoaded($el)">
                                                            <img
                                                                src="{{ $url }}"
                                                                class="mfip-imgCover"
                                                                alt="Library image"
                                                                loading="lazy"
                                                                decoding="async"
                                                                onload="this.closest('.mfip-imgSlot')?.classList.add('is-loaded')"
                                                                onerror="this.closest('.mfip-imgSlot')?.classList.add('is-error')"
                                                            >
                                                        </div>
                                                    @else
                                                        <div class="mfip-noPreview">بدون preview</div>
                                                    @endif
                                                </div>

                                                <div class="mfip-tileOverlay">
                                                    <div class="mfip-tileOverlayChip">انتخاب</div>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="mfip-emptyState">چیزی پیدا نشد.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="mfip-pager">
                                    <button
                                        type="button"
                                        class="mfip-button mfip-button--secondary mfip-button--sm"
                                        wire:click="previousPage"
                                        wire:loading.attr="disabled"
                                        wire:target="previousPage"
                                        @disabled($library->onFirstPage())
                                    >
                                        قبلی
                                    </button>

                                    <div class="mfip-pagerText">
                                        صفحه {{ $library->currentPage() }} از {{ $library->lastPage() }}
                                    </div>

                                    <button
                                        type="button"
                                        class="mfip-button mfip-button--secondary mfip-button--sm"
                                        wire:click="nextPage"
                                        wire:loading.attr="disabled"
                                        wire:target="nextPage"
                                        @disabled(!$library->hasMorePages())
                                    >
                                        بعدی
                                    </button>
                                </div>
                            </div>

                            <!-- Upload (full height, no preview) -->
                            <div x-show="tab === 'upload'" style="height: 100%;">
                                <div class="mfip-uploadFull">
                                    <label class="mfip-dropzone mfip-dropzone--full">
                                        <input
                                            type="file"
                                            class="mfip-hiddenInput"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            wire:model="upload"
                                        >
                                        <div class="mfip-dropzoneContent">
                                            <div class="mfip-dropzoneTitle">آپلود تصویر</div>
                                            <div class="mfip-dropzoneHint">فایل را بکش و رها کن یا کلیک کن</div>
                                            <div class="mfip-dropzoneHint">jpg / png / webp — حداکثر ۵MB</div>
                                        </div>
                                    </label>

                                    @error('upload')
                                    <div class="mfip-error">{{ $message }}</div>
                                    @enderror

                                    <!-- Optional: show "uploading..." hint in upload tab too -->
                                    <div class="mfip-loading" wire:loading wire:target="upload">
                                        در حال آپلود...
                                    </div>
                                    <div class="mfip-loading" wire:loading wire:target="saveUpload">
                                        در حال ذخیره‌سازی...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Preview Panel -->
                        <aside
                            class="mfip-previewPanel"
                            x-cloak
                            x-show="tab === 'library' && selectedUrl"
                            x-transition:enter="mfip-preview-enter"
                            x-transition:enter-start="mfip-preview-enter-start"
                            x-transition:enter-end="mfip-preview-enter-end"
                            x-transition:leave="mfip-preview-leave"
                            x-transition:leave-start="mfip-preview-leave-start"
                            x-transition:leave-end="mfip-preview-leave-end"
                            wire:ignore
                        >
                            <div class="mfip-aspect mfip-previewMedia">
                                <template x-if="selectedUrl">
                                    <div class="mfip-imgSlot is-loading" x-init="ensureImgLoaded($el)"
                                         x-ref="previewSlot">
                                        <img
                                            :src="selectedUrl"
                                            class="mfip-imgCover"
                                            alt="Selected preview"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </div>
                                </template>

                                <template x-if="!selectedUrl">
                                    <div class="mfip-previewEmpty">هنوز تصویری انتخاب نشده.</div>
                                </template>
                            </div>

                            <div class="mfip-previewActions">
                                <!-- Title editable -->
                                <div class="mfip-field">
                                    <div class="mfip-fieldLabel">عنوان فایل</div>

                                    <div class="mfip-titleInline">
                                        <input
                                            type="text"
                                            class="mfip-input mfip-input--compact"
                                            x-model="titleDraft"
                                            placeholder="عنوان تصویر..."
                                        >

                                        <button
                                            type="button"
                                            class="mfip-button mfip-button--secondary mfip-button--sm"
                                            :disabled="!selectedId"
                                            @click="saveTitle()"
                                        >
                                            تغییر عنوان
                                        </button>
                                    </div>
                                </div>

                                <!-- File info (Disk removed) -->
                                <div class="mfip-info">
                                    <div class="mfip-infoRow">
                                        <div class="mfip-infoKey">شناسه</div>
                                        <div class="mfip-infoVal" x-text="previewInfo?.id ?? '-'"></div>
                                    </div>

                                    <!-- Link + Path combined -->
                                    <div class="mfip-infoRow">
                                        <div class="mfip-infoKey">فایل</div>
                                        <div class="mfip-infoVal">
                                            <template x-if="previewInfo?.url">
                                                <a class="mfip-fileChip"
                                                   :href="previewInfo.url"
                                                   target="_blank"
                                                   rel="noopener"
                                                   :title="previewInfo?.path || previewInfo.url">
                                                    <span class="mfip-fileChipIcon">⤴</span>
                                                    <span class="mfip-fileChipText"
                                                          x-text="previewInfo?.path || previewInfo.url"></span>
                                                </a>
                                            </template>

                                            <template x-if="!previewInfo?.url">
                                                <span>-</span>
                                            </template>

                                            <button type="button"
                                                    class="mfip-copyBtn"
                                                    :disabled="!(previewInfo?.path)"
                                                    @click="copyText(previewInfo?.path)">
                                                کپی
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mfip-infoRow">
                                        <div class="mfip-infoKey">حجم</div>
                                        <div class="mfip-infoVal" x-text="previewInfo?.size_human ?? '-'"></div>
                                    </div>

                                    <div class="mfip-infoRow">
                                        <div class="mfip-infoKey">تاریخ آپلود</div>
                                        <div class="mfip-infoVal" x-text="previewInfo?.uploaded_at ?? '-'"></div>
                                    </div>

                                    <div class="mfip-infoRow">
                                        <div class="mfip-infoKey">آپلودکننده</div>
                                        <div class="mfip-infoVal" x-text="previewInfo?.uploaded_by ?? '-'"></div>
                                    </div>

                                    <div class="mfip-infoRow">
                                        <div class="mfip-infoKey">ابعاد</div>
                                        <div class="mfip-infoVal"
                                             x-text="(previewInfo?.width && previewInfo?.height) ? (previewInfo.width + 'px' + ' × ' + previewInfo.height + 'px') : '-'"></div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="mfip-button mfip-button--primary"
                                    :disabled="!pendingId"
                                    wire:click="confirmSelection"
                                >
                                    استفاده از این تصویر
                                </button>

                                <button type="button" class="mfip-button mfip-button--danger" :disabled="!selectedId"
                                        @click="confirmDelete()">
                                    حذف کامل تصویر
                                </button>

                                @error('selectedMediaFileId')
                                <div class="mfip-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </aside>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
