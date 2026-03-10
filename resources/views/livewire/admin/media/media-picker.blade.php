<div
    class="mfip-root"
    x-data="{
        open: false,
        tab: 'library',

        selectedId: @entangle('selectedMediaFileId').live,
        selectedUrl: @entangle('selectedUrl').live,
        selectedInfo: @entangle('selectedInfo').live,

        titleDraft: '',
        optimisticInfo: null,

        // upload ui state
        uploading: false,
        uploadProgress: 0,

        get previewInfo(){
            return this.optimisticInfo ?? this.selectedInfo ?? null;
        },

        init(){
            // ✅ default tab if nothing selected
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

            // ✅ هر بار Livewire گرید رو morph کرد/نود جدید اضافه کرد، اسلات‌ها رو سریع finalize کن
            this.$nextTick(() => {
              const root = this.$el;
              const gridWrap = root.querySelector('.mfip-gridWrap');
              if(!gridWrap) return;

              const run = () => {
                gridWrap.querySelectorAll('.mfip-imgSlot').forEach((slot) => {
                  // اگر قبلاً final شده کاری نکن
                  if (slot.classList.contains('is-loaded') || slot.classList.contains('is-error')) return;
                  this.ensureImgLoaded(slot);
                });
              };

              // یک بار همین الان
              run();

              // هر تغییر DOM داخل گرید
              const obs = new MutationObserver(() => run());
              obs.observe(gridWrap, { childList: true, subtree: true });

              // نگه دار که اگر خواستی destroy کنی
              this.__mfipObs = obs;
            });


            this.markCachedImagesLoaded();

            document.addEventListener('livewire:morph.updated', () => {
                this.markCachedImagesLoaded();
            });
        },

        // open modal: choose correct initial tab
        openModal(){
            this.open = true;
            this.tab = this.selectedUrl ? 'library' : 'upload';
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

            this.$wire.select(id);
        },

        confirmDelete(){
            if(!this.selectedId) return;
            const ok = confirm('حذف شود؟ این عملیات غیرقابل بازگشت است و همه فایل‌ها/نسخه‌ها حذف می‌شوند.');
            if(!ok) return;
            this.$wire.deleteSelected();
        },

        saveTitle(){
            if(!this.selectedId) return;
            this.$wire.updateSelectedTitle(this.titleDraft);
        },

        ensureImgLoaded(el){
          const img = el?.querySelector('img');
          if(!img) return;

          // reset states for fresh checks
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

          // اگر از cache آماده است
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

                    // اگر از cache آماده است، load event ممکنه فایر نشه، پس دستی set کن
                    if (img.complete) {
                        if (img.naturalWidth > 0) slot.classList.add('is-loaded');
                        else slot.classList.add('is-error');
                    }
                });
            });
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

        copyText(text){
            if(!text) return;
            navigator.clipboard?.writeText(text);
        }
    }"

    x-on:mfip-close-modal.window="open = false; tab = (selectedUrl ? 'library' : 'upload');"
    x-on:mfip-switch-tab.window="tab = $event.detail.tab"
    x-on:mfip-upload-saved.window="uploading = false; uploadProgress = 0"

    x-on:livewire-upload-start.window="uploading = true; uploadProgress = 0; tab = 'library';"
    x-on:livewire-upload-progress.window="uploadProgress = $event.detail.progress"
    x-on:livewire-upload-finish.window="uploadProgress = 100"
    x-on:livewire-upload-error.window="uploading = false; uploadProgress = 0"
>
    <!-- Picker Box -->
    <button type="button" class="mfip-pickerBtn" @click="openModal()" wire:ignore>
        <div class="mfip-pickerCard">
            <div class="mfip-aspect mfip-pickerAspect">
                <template x-if="selectedUrl">
                    <div class="mfip-imgSlot is-loading" x-init="ensureImgLoaded($el)" x-ref="previewSlot">
                        <img
                            :src="selectedUrl"
                            alt="Selected image"
                            class="mfip-imgCover"
                            loading="lazy"
                            decoding="async"
                        >
                        <input type="hidden" name="{{ $name }}[id]" :value="selectedId">
                        <input type="hidden" name="{{ $name }}[path]" :value="selectedUrl">
                    </div>
                </template>

                <template x-if="!selectedUrl">
                    <div class="mfip-pickerEmpty">
                        <div class="mfip-pickerEmptyTitle">تصویر انتخاب نشده</div>
                        <div class="mfip-pickerEmptyHint">برای انتخاب یا آپلود، کلیک کن</div>
                    </div>
                </template>
            </div>

            <div class="mfip-pickerHover">
                <div class="mfip-pickerHoverChip">انتخاب / تغییر تصویر</div>
            </div>
        </div>
    </button>

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
                                                :class="selectedId === {{ $media->id }} ? 'is-selected' : ''"
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

                                <button type="button" class="mfip-button mfip-button--primary" :disabled="!selectedUrl"
                                        @click="open = false">
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

    <style>
        /* =========
           MFIP — Modern + Minimal + WP-like
           No Tailwind utilities
           Root: .mfip-root
           ========= */

        .mfip-root, .mfip-root * {
            box-sizing: border-box;
        }

        .mfip-root [x-cloak] {
            display: none !important;
        }

        /* Theme Tokens */
        .mfip-root {
            --mf-bg: #ffffff;
            --mf-surface: #ffffff;
            --mf-muted: #f8fafc;
            --mf-border: #e2e8f0;
            --mf-border-strong: #cbd5e1;

            --mf-text: #0f172a;
            --mf-text-2: #334155;
            --mf-text-3: #64748b;

            /* Accent (secondary color) */
            --mf-accent: #3b82f6;
            --mf-accent-2: #2563eb;
            --mf-accent-soft: rgba(59, 130, 246, .12);

            --mf-ring: rgba(59, 130, 246, .22);
            --mf-shadow-2: 0 26px 70px rgba(15, 23, 42, .14);

            --mf-r-md: 16px;
            --mf-r-lg: 20px;

            --mf-ease: cubic-bezier(.2, .7, .2, 1);
            --mf-dur: 180ms;

            color: var(--mf-text);
        }

        /* Buttons reset (scoped) */
        .mfip-root button {
            border: 0;
            cursor: pointer;
        }

        .mfip-root button:focus {
            outline: none;
        }

        .mfip-root button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        /* Helpers */
        .mfip-aspect {
            aspect-ratio: 16/9;
        }

        .mfip-square {
            aspect-ratio: 1/1;
        }

        /* ✅ square thumbs */
        .mfip-imgCover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Picker box */
        .mfip-pickerBtn {
            width: 100%;
            display: block;
            padding: 0;
            background: transparent;
        }

        .mfip-pickerCard {
            position: relative;
            overflow: hidden;
            border-radius: var(--mf-r-lg);
            border: 1px solid var(--mf-border);
            background: var(--mf-surface);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .06), 0 1px 1px rgba(15, 23, 42, .04);
        }

        .mfip-pickerAspect {
            width: 100%;
            background: var(--mf-muted);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mfip-pickerEmpty {
            text-align: center;
            padding: 32px 24px;
        }

        .mfip-pickerEmptyTitle {
            font-size: 14px;
            font-weight: 800;
            color: var(--mf-text-2);
        }

        .mfip-pickerEmptyHint {
            margin-top: 4px;
            font-size: 12px;
            color: var(--mf-text-3);
        }

        .mfip-pickerHover {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity var(--mf-dur) var(--mf-ease);
            background: rgba(2, 6, 23, .40);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mfip-pickerCard:hover .mfip-pickerHover {
            opacity: 1;
        }

        .mfip-pickerHoverChip {
            background: rgba(255, 255, 255, .92);
            border-radius: 14px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 900;
            color: var(--mf-text);
            backdrop-filter: blur(8px);
        }

        /* Modal */
        .mfip-modalWrap {
            position: fixed;
            inset: 0;
            z-index: 20000;
        }

        .mfip-modalBackdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .55);
        }

        .mfip-modalViewport {
            position: relative;
            width: 92vw;
            height: 92vh;
            margin: 4vh auto 0;
            display: flex;
        }

        .mfip-modal {
            width: 100%;
            height: 100%;
            border-radius: var(--mf-r-lg);
            background: var(--mf-surface);
            border: 1px solid var(--mf-border);
            box-shadow: var(--mf-shadow-2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .mfip-modalHeader {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(226, 232, 240, .75);
            background: linear-gradient(180deg, rgba(248, 250, 252, .92), rgba(255, 255, 255, 1));
        }

        .mfip-modalHeader button {
            border: 1px solid var(--mf-border);
            background: var(--mf-muted);
        }

        .mfip-modalTitle {
            font-size: 16px;
            font-weight: 900;
        }

        .mfip-modalSub {
            margin-top: 4px;
            font-size: 12px;
            color: var(--mf-text-3);
        }

        .mfip-modalBody {
            padding: 16px 18px 18px;
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* Tabs */
        .mfip-tabs {
            display: flex;
            gap: 10px;
            padding: 4px;
            border: 1px solid var(--mf-border);
            background: var(--mf-muted);
            border-radius: 999px;
            width: fit-content;
        }

        .mfip-tab {
            border-radius: 999px;
            padding: 9px 14px;
            font-weight: 900;
            font-size: 13px;
            color: var(--mf-text-2);
            transition: transform var(--mf-dur) var(--mf-ease), background var(--mf-dur) var(--mf-ease), box-shadow var(--mf-dur) var(--mf-ease);
        }

        .mfip-tab:hover {
            transform: translateY(-1px);
        }

        .mfip-tab.is-active {
            background: var(--mf-bg);
            color: var(--mf-text);
            box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
        }

        /* Layout */
        .mfip-body {
            flex: 1;
            min-height: 0;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 14px;
        }

        .mfip-body.no-preview {
            grid-template-columns: 1fr;
        }

        .mfip-body.has-preview {
            grid-template-columns: 1fr 340px;
        }

        /* Preview enter / leave animation */
        .mfip-preview-enter {
            transition: opacity 220ms var(--mf-ease), transform 260ms var(--mf-ease);
        }

        .mfip-preview-enter-start {
            opacity: 0;
            transform: translateX(16px) scale(.98);
        }

        .mfip-preview-enter-end {
            opacity: 1;
            transform: translateX(0) scale(1);
        }

        .mfip-preview-leave {
            transition: opacity 160ms var(--mf-ease), transform 160ms var(--mf-ease);
        }

        .mfip-preview-leave-start {
            opacity: 1;
            transform: translateX(0) scale(1);
        }

        .mfip-preview-leave-end {
            opacity: 0;
            transform: translateX(8px) scale(.985);
        }

        @media (max-width: 900px) {
            .mfip-body {
                grid-template-columns: 1fr;
            }
        }

        .mfip-left {
            min-width: 0;
        }

        /* Search */
        .mfip-searchRow {
            margin-bottom: 12px;
        }

        .mfip-input {
            width: 100%;
            border: 1px solid var(--mf-border);
            border-radius: 14px;
            padding: 11px 12px;
            background: var(--mf-bg);
            color: var(--mf-text);
            font-size: 14px;
        }

        .mfip-input::placeholder {
            color: #94a3b8;
        }

        .mfip-input:focus {
            outline: none;
            border-color: var(--mf-accent);
            box-shadow: 0 0 0 4px var(--mf-ring);
        }

        .mfip-input--compact {
            padding: 10px 12px;
            font-size: 13px;
        }

        /* Grid wrap */
        .mfip-gridWrap {
            border: 1px solid var(--mf-border);
            background: var(--mf-bg);
            border-radius: var(--mf-r-md);
            padding: 12px;
            height: calc(92vh - 300px);
            min-height: 240px;
            overflow: auto;
        }

        /* ✅ more columns overall */
        .mfip-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        @media (min-width: 560px) {
            .mfip-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        @media (min-width: 900px) {
            .mfip-grid {
                grid-template-columns: repeat(7, minmax(0, 1fr));
            }
        }

        .mfip-tile {
            position: relative;
            border: 1px solid var(--mf-border);
            background: var(--mf-muted);
            border-radius: 14px;
            overflow: hidden;
            padding: 0;
            transition: transform var(--mf-dur) var(--mf-ease), box-shadow var(--mf-dur) var(--mf-ease), border-color var(--mf-dur) var(--mf-ease);
        }

        .mfip-tile:hover {
            transform: translateY(-2px);
            border-color: var(--mf-border-strong);
            box-shadow: 0 14px 32px rgba(15, 23, 42, .10);
        }

        .mfip-tile.is-selected {
            border-color: var(--mf-accent);
            box-shadow: 0 0 0 3px var(--mf-accent-soft);
        }

        .mfip-tile.is-selected::after {
            content: "✓";
            position: absolute;
            top: 10px;
            right: 10px;
            width: 26px;
            height: 26px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-weight: 900;
            font-size: 14px;
            color: #fff;
            background: var(--mf-accent);
            box-shadow: 0 14px 30px rgba(59, 130, 246, .22);
            z-index: 2;
        }

        .mfip-noPreview {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: var(--mf-text-3);
        }

        .mfip-tileOverlay {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity var(--mf-dur) var(--mf-ease);
            background: rgba(2, 6, 23, .44);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mfip-tile:hover .mfip-tileOverlay {
            opacity: 1;
        }

        .mfip-tileOverlayChip {
            background: rgba(255, 255, 255, .92);
            border-radius: 12px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 900;
            color: var(--mf-text);
            backdrop-filter: blur(8px);
        }

        .mfip-emptyState {
            padding: 10px 0;
            font-size: 13px;
            color: var(--mf-text-3);
        }

        /* Pager */
        .mfip-pager {
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .mfip-pagerText {
            font-size: 12px;
            color: var(--mf-text-3);
        }

        /* Preview panel */
        .mfip-previewPanel {
            border: 1px solid var(--mf-border);
            background: var(--mf-bg);
            border-radius: var(--mf-r-md);
            overflow: hidden;
            height: fit-content;
            position: sticky;
            top: 12px;
        }

        @media (max-width: 900px) {
            .mfip-previewPanel {
                position: static;
            }
        }

        .mfip-previewHead {
            padding: 12px 12px 10px;
            border-bottom: 1px solid rgba(226, 232, 240, .75);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .mfip-previewLabel {
            font-size: 12px;
            font-weight: 900;
            color: var(--mf-text-2);
        }

        .mfip-previewMeta {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .mfip-kbd {
            font-size: 11px;
            font-weight: 900;
            color: var(--mf-text-3);
            border: 1px solid var(--mf-border);
            background: #fff;
            padding: 3px 6px;
            border-radius: 8px;
        }

        .mfip-previewMedia {
            background: var(--mf-muted);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mfip-previewEmpty {
            text-align: center;
            padding: 18px 14px;
            color: var(--mf-text-3);
            font-size: 12px;
        }

        .mfip-previewActions {
            padding: 12px;
            display: grid;
            gap: 10px;
            border-top: 1px solid rgba(226, 232, 240, .75);
            background: linear-gradient(180deg, rgba(248, 250, 252, .85), rgba(255, 255, 255, 1));
        }

        .mfip-previewHint {
            font-size: 12px;
            color: var(--mf-text-3);
            line-height: 1.6;
        }

        .mfip-field {
            display: grid;
            gap: 8px;
        }

        .mfip-fieldLabel {
            font-size: 12px;
            font-weight: 900;
            color: var(--mf-text-2);
        }

        /* Info */
        .mfip-info {
            border: 1px solid var(--mf-border);
            background: var(--mf-bg);
            border-radius: var(--mf-r-md);
            overflow: hidden;
        }

        .mfip-infoRow {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 10px;
            padding: 10px 12px;
            border-top: 1px solid rgba(226, 232, 240, .75);
            align-items: center;
        }

        .mfip-infoRow:first-child {
            border-top: 0;
        }

        .mfip-infoKey {
            font-size: 12px;
            font-weight: 900;
            color: var(--mf-text-2);
        }

        .mfip-infoVal {
            font-size: 12px;
            color: var(--mf-text-3);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        /* Link+Path chip */
        .mfip-fileChip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 100%;
            border: 1px solid var(--mf-border);
            background: linear-gradient(180deg, rgba(59, 130, 246, .10), rgba(255, 255, 255, 1));
            padding: 8px 10px;
            border-radius: 12px;
            text-decoration: none;
            color: var(--mf-text);
            font-weight: 900;
            overflow: hidden;
        }

        .mfip-fileChip:hover {
            border-color: var(--mf-accent);
            box-shadow: 0 10px 24px rgba(59, 130, 246, .12);
        }

        .mfip-fileChipIcon {
            width: 22px;
            height: 22px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: rgba(59, 130, 246, .14);
            color: var(--mf-accent-2);
            flex: 0 0 auto;
        }

        .mfip-fileChipText {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        .mfip-copyBtn {
            border: 1px solid var(--mf-border);
            background: #fff;
            padding: 8px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 900;
            color: var(--mf-text-2);
            flex: 0 0 auto;
        }

        .mfip-copyBtn:hover {
            border-color: var(--mf-border-strong);
        }

        /* Upload full */
        .mfip-uploadFull {
            border: 1px solid var(--mf-border);
            border-radius: var(--mf-r-md);
            background: var(--mf-bg);
            overflow: hidden;
            height: 100%;
            min-height: 320px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 12px;
        }

        .mfip-hiddenInput {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .mfip-dropzone {
            position: relative;
            display: block;
            border-radius: 18px;
            border: 2px dashed var(--mf-border-strong);
            background: linear-gradient(180deg, var(--mf-muted), var(--mf-bg));
            padding: 18px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .mfip-dropzone--full {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .mfip-dropzone:hover {
            border-color: var(--mf-accent);
            box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
        }

        .mfip-dropzoneContent {
            text-align: center;
        }

        .mfip-dropzoneTitle {
            font-size: 14px;
            font-weight: 900;
            color: var(--mf-text);
        }

        .mfip-dropzoneHint {
            margin-top: 6px;
            font-size: 12px;
            color: var(--mf-text-3);
        }

        .mfip-loading {
            font-size: 12px;
            color: var(--mf-text-3);
            margin-top: 10px;
            text-align: center;
        }

        .mfip-error {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            font-size: 13px;
            font-weight: 900;
        }

        /* Buttons */
        .mfip-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 14px;
            padding: 11px 14px;
            font-weight: 900;
            font-size: 13px;
            line-height: 1;
            border: 1px solid var(--mf-border);
            background: var(--mf-bg);
            color: var(--mf-text-2);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            transition: transform var(--mf-dur) var(--mf-ease), box-shadow var(--mf-dur) var(--mf-ease), border-color var(--mf-dur) var(--mf-ease), filter var(--mf-dur) var(--mf-ease);
        }

        .mfip-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(15, 23, 42, .10);
            border-color: var(--mf-border-strong);
        }

        .mfip-button:disabled {
            opacity: .55;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .mfip-button--sm {
            padding: 9px 12px;
            border-radius: 12px;
            font-size: 12px;
        }

        .mfip-button--secondary {
            background: var(--mf-bg);
            color: var(--mf-text-2);
        }

        .mfip-button--primary {
            background: linear-gradient(180deg, var(--mf-accent), var(--mf-accent-2));
            border-color: rgba(0, 0, 0, .02);
            color: #fff;
            box-shadow: 0 18px 40px rgba(59, 130, 246, .22);
        }

        .mfip-button--ghost {
            background: rgba(255, 255, 255, .55);
            border-color: rgba(226, 232, 240, .9);
            box-shadow: none;
        }

        .mfip-button--danger {
            background: linear-gradient(180deg, #ef4444, #dc2626);
            border-color: rgba(0, 0, 0, .02);
            color: #fff;
            box-shadow: 0 18px 40px rgba(239, 68, 68, .18);
        }

        /* Upload bar */
        .mfip-uploadBar {
            border: 1px solid var(--mf-border);
            background: #fff;
            border-radius: 14px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .mfip-uploadBarTop {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }

        .mfip-uploadBarTitle {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 900;
            color: var(--mf-text-2);
        }

        .mfip-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--mf-accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .14);
        }

        .mfip-uploadBarPct {
            font-size: 12px;
            font-weight: 900;
            color: var(--mf-text-3);
        }

        .mfip-progressTrack {
            width: 100%;
            height: 10px;
            background: rgba(226, 232, 240, .8);
            border-radius: 999px;
            overflow: hidden;
        }

        .mfip-progressFill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--mf-accent), var(--mf-accent-2));
            border-radius: 999px;
            transition: width 120ms linear;
        }

        .mfip-uploadBarNote {
            margin-top: 8px;
            font-size: 12px;
            color: var(--mf-text-3);
            font-weight: 900;
        }

        /* Skeleton shimmer */
        .mfip-imgSlot {
            position: relative;
            width: 100%;
            height: 100%;
            background: var(--mf-muted);
            overflow: hidden;
        }

        /* ✅ فقط وقتی loading است shimmer را نشان بده */
        .mfip-imgSlot.is-loading::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                90deg,
                rgba(226, 232, 240, .55) 0%,
                rgba(226, 232, 240, .28) 35%,
                rgba(226, 232, 240, .55) 70%
            );
            transform: translateX(-60%);
            animation: mfip-shimmer 1.15s linear infinite;
        }

        .mfip-imgSlot.is-loading::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(248, 250, 252, .6);
        }

        /* hide image until loaded */
        .mfip-imgSlot > img {
            opacity: 0;
            transition: opacity var(--mf-dur) var(--mf-ease);
        }

        /* loaded */
        .mfip-imgSlot.is-loaded > img {
            opacity: 1;
        }

        .mfip-pickerBtn .mfip-imgSlot > img {
            opacity: 1 !important;
        }

        .mfip-imgSlot.is-loaded::before,
        .mfip-imgSlot.is-loaded::after {
            display: none;
        }

        /* error */
        .mfip-imgSlot.is-error::before,
        .mfip-imgSlot.is-error::after {
            display: none;
        }

        .mfip-imgSlot.is-error > img {
            opacity: 0;
        }

        @keyframes mfip-shimmer {
            0% {
                transform: translateX(-60%);
            }
            100% {
                transform: translateX(160%);
            }
        }

        /* ========= Preview Polishing ========= */

        .mfip-previewPanel {
            border-radius: 18px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .10);
        }

        .mfip-previewTop {
            padding: 14px 12px 12px;
            border-bottom: 1px solid rgba(226, 232, 240, .75);
            background: linear-gradient(180deg, rgba(248, 250, 252, .9), rgba(255, 255, 255, 1));
        }

        .mfip-previewTitleRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .mfip-previewTitle {
            font-size: 14px;
            font-weight: 1000;
            color: var(--mf-text);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mfip-previewBadge {
            font-size: 11px;
            font-weight: 900;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--mf-border);
            background: rgba(59, 130, 246, .08);
            color: var(--mf-accent-2);
            flex: 0 0 auto;
        }

        .mfip-previewMetaGrid {
            margin-top: 10px;
            display: grid;
            gap: 8px;
        }

        .mfip-metaItem {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 10px;
            align-items: center;
        }

        .mfip-metaKey {
            font-size: 12px;
            font-weight: 900;
            color: var(--mf-text-2);
        }

        .mfip-metaVal {
            font-size: 12px;
            color: var(--mf-text-3);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mfip-previewMedia {
            border-bottom: 1px solid rgba(226, 232, 240, .75);
        }

        .mfip-titleInline {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }

    </style>
</div>
