@props([
    'errorBag' => 'default',
    'mode' => 'default',
    'darkMode' => true,
    'layout' => 'inline',
])

@php
    $inputId = 'ticket-images-' . \Illuminate\Support\Str::uuid();
    $maxFiles = app(\App\Infrastructure\Services\Setting\Groups\TicketSettings::class)->attachmentsPerMessage();
    $maxSizeMb = app(\App\Infrastructure\Services\Setting\Groups\TicketSettings::class)->attachmentMaxSizeMegabytes();
    $attachmentError = $errors->getBag($errorBag)->first('attachments')
        ?: $errors->getBag($errorBag)->first('attachments.*');
    $isComposer = $mode === 'composer';
    $usesStackedActions = $layout === 'stacked-actions';
    $composerDraggingClasses = 'border-brand-400 bg-brand-50 text-brand-600 ring-4 ring-brand-100'
        . ($darkMode ? ' dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/10' : '');
    $composerIdleClasses = 'border-slate-200 bg-white text-slate-500 hover:border-brand-300 hover:text-brand-600'
        . ($darkMode ? ' dark:border-slate-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:border-brand-500/50 dark:hover:text-brand-300' : '');
@endphp

<div
    x-data="{
        files: [],
        previews: [],
        dragging: false,
        clientError: '',
        maxFiles: @js($maxFiles),
        maxSizeBytes: @js($maxSizeMb * 1024 * 1024),
        allowedTypes: ['image/jpeg', 'image/png', 'image/webp'],

        destroy() {
            this.clearPreviews();
        },

        chooseFiles() {
            this.$refs.input.click();
        },

        addFiles(fileList) {
            this.clientError = '';

            const combined = [...this.files];

            for (const file of Array.from(fileList || [])) {
                const duplicate = combined.some((current) =>
                    current.name === file.name &&
                    current.size === file.size &&
                    current.lastModified === file.lastModified
                );

                if (duplicate) continue;

                if (! this.allowedTypes.includes(file.type)) {
                    this.clientError = 'فقط تصاویر JPG، PNG و WebP قابل ارسال هستند.';
                    continue;
                }

                if (file.size > this.maxSizeBytes) {
                    this.clientError = `حجم تصویر «${file.name}» بیشتر از @js($maxSizeMb) مگابایت است.`;
                    continue;
                }

                if (combined.length >= this.maxFiles) {
                    this.clientError = `حداکثر ${this.maxFiles} تصویر برای هر پیام قابل انتخاب است.`;
                    break;
                }

                combined.push(file);
            }

            this.files = combined;
            this.syncInput();
            this.rebuildPreviews();
        },

        removeFile(index) {
            this.files.splice(index, 1);
            this.clientError = '';
            this.syncInput();
            this.rebuildPreviews();
        },

        syncInput() {
            const transfer = new DataTransfer();
            this.files.forEach((file) => transfer.items.add(file));
            this.$refs.input.files = transfer.files;
        },

        rebuildPreviews() {
            this.clearPreviews();
            this.previews = this.files.map((file) => ({
                name: file.name,
                size: this.formatBytes(file.size),
                url: URL.createObjectURL(file),
            }));
        },

        clearPreviews() {
            this.previews.forEach((preview) => URL.revokeObjectURL(preview.url));
            this.previews = [];
        },

        formatBytes(bytes) {
            if (bytes < 1024 * 1024) {
                return `${Math.max(1, Math.round(bytes / 1024))} KB`;
            }

            return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
        },
    }"
    @class([
        'contents' => $isComposer,
        'space-y-2.5' => !$isComposer,
    ])
>
    <input
        x-ref="input"
        id="{{ $inputId }}"
        type="file"
        name="attachments[]"
        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
        multiple
        class="sr-only"
        x-on:change="addFiles($event.target.files)"
    />

    @if($isComposer)
        <button
            type="button"
            x-on:click="chooseFiles()"
            x-on:dragenter.prevent="dragging = true"
            x-on:dragover.prevent="dragging = true"
            x-on:dragleave.prevent="dragging = false"
            x-on:drop.prevent="dragging = false; addFiles($event.dataTransfer.files)"
            x-bind:class="dragging ? @js($composerDraggingClasses) : @js($composerIdleClasses)"
            @class([
                'group relative grid h-12 w-12 cursor-pointer place-items-center rounded-2xl border transition focus:outline-none focus:ring-4 focus:ring-brand-100',
                'col-start-2 row-start-1' => $usesStackedActions,
                'dark:focus:ring-brand-500/20' => $darkMode,
            ])
            title="افزودن تصویر"
            aria-label="افزودن تصویر به پیام"
        >
            <svg class="h-5 w-5 -rotate-45 transition group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none"
                 aria-hidden="true">
                <path
                    d="m20.5 11.5-8.7 8.7a6 6 0 0 1-8.5-8.5l9.4-9.4a4 4 0 0 1 5.7 5.7L9 17.4a2 2 0 0 1-2.8-2.8l8.7-8.7"
                    stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

            <span
                x-show="files.length"
                x-cloak
                x-text="files.length"
                @class([
                    'absolute -left-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full border-2 border-slate-50 bg-brand-600 px-1 text-[9px] font-black leading-none text-white',
                    'dark:border-slate-800' => $darkMode,
                ])
            ></span>
        </button>

        <div
            x-show="previews.length || clientError"
            x-cloak
            @class([
                'order-last mt-1 w-full border-t border-slate-200/80 px-1 pt-2',
                'col-span-2 row-start-3' => $usesStackedActions,
                'dark:border-slate-700' => $darkMode,
            ])
        >
            <div class="flex flex-wrap items-center gap-1.5">
                <template x-for="(preview, index) in previews" :key="preview.url">
                    <div
                        @class([
                            'group relative h-12 w-12 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm',
                            'dark:border-slate-600 dark:bg-slate-800' => $darkMode,
                        ])
                        x-bind:title="`${preview.name} — ${preview.size}`">
                        <img x-bind:src="preview.url" x-bind:alt="preview.name" class="h-full w-full object-cover"/>
                        <button
                            type="button"
                            x-on:click="removeFile(index)"
                            class="absolute left-0.5 top-0.5 grid h-[18px] w-[18px] cursor-pointer place-items-center rounded-full bg-slate-950/75 text-white backdrop-blur transition hover:bg-rose-600"
                            aria-label="حذف تصویر"
                        >
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="2.4"
                                      stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <button
                    type="button"
                    x-show="previews.length && files.length < maxFiles"
                    x-on:click="chooseFiles()"
                    @class([
                        'grid h-12 w-12 cursor-pointer place-items-center rounded-xl border border-dashed border-slate-300 text-slate-400 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600',
                        'dark:border-slate-600 dark:hover:border-brand-500/50 dark:hover:bg-brand-500/10 dark:hover:text-brand-300' => $darkMode,
                    ])
                    aria-label="افزودن تصویر دیگر"
                    title="افزودن تصویر دیگر"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div
                x-show="clientError"
                x-text="clientError"
                @class([
                    'mt-1.5 text-[10px] font-bold leading-5 text-rose-600',
                    'dark:text-rose-300' => $darkMode,
                ])
            ></div>
        </div>

        @if($attachmentError)
            <div @class([
                'order-last mt-1 w-full px-1 text-[10px] font-bold leading-5 text-rose-600',
                'col-span-2 row-start-3' => $usesStackedActions,
                'dark:text-rose-300' => $darkMode,
            ])>
                {{ $attachmentError }}
            </div>
        @endif
    @else
        <div
            x-on:dragenter.prevent="dragging = true"
            x-on:dragover.prevent="dragging = true"
            x-on:dragleave.prevent="dragging = false"
            x-on:drop.prevent="dragging = false; addFiles($event.dataTransfer.files)"
            x-bind:class="dragging ? 'border-brand-400 bg-brand-50 ring-4 ring-brand-100 dark:border-brand-400 dark:bg-brand-500/10 dark:ring-brand-500/10' : 'border-slate-200 bg-slate-50/70 dark:border-slate-700 dark:bg-slate-800/40'"
            class="group rounded-2xl border border-dashed p-2.5 transition"
        >
            <button type="button" x-on:click="chooseFiles()"
                    class="flex w-full cursor-pointer items-center gap-2.5 text-right">
                <span
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-brand-600 shadow-sm ring-1 ring-slate-200 transition group-hover:ring-brand-200 dark:bg-slate-800 dark:text-brand-300 dark:ring-slate-700 [&_svg]:h-4 [&_svg]:w-4">
                    @include('icons.image-03')
                </span>

                <span class="min-w-0 flex-1">
                    <span class="block text-[11px] font-black text-slate-700 dark:text-slate-200">افزودن تصویر</span>
                    <span class="mt-0.5 block text-[10px] font-bold leading-5 text-slate-400 dark:text-slate-500">
                        حداکثر {{ $maxFiles }} تصویر، هرکدام {{ $maxSizeMb }} مگابایت
                    </span>
                </span>

                <span
                    class="hidden text-[10px] font-bold text-brand-600 sm:block dark:text-brand-300">انتخاب فایل</span>
            </button>
        </div>

        <div x-show="previews.length" x-cloak class="flex flex-wrap gap-2">
            <template x-for="(preview, index) in previews" :key="preview.url">
                <div
                    class="group relative flex h-16 w-16 items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                    x-bind:title="`${preview.name} — ${preview.size}`">
                    <img x-bind:src="preview.url" x-bind:alt="preview.name" class="h-full w-full object-cover"/>
                    <button
                        type="button"
                        x-on:click="removeFile(index)"
                        class="absolute left-1 top-1 grid h-5 w-5 cursor-pointer place-items-center rounded-full bg-slate-950/75 text-white backdrop-blur transition hover:bg-rose-600"
                        aria-label="حذف تصویر"
                    >
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="2.4"
                                  stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <div x-show="clientError" x-cloak x-text="clientError"
             class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[10px] font-bold leading-5 text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300"></div>

        @if($attachmentError)
            <div
                class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[10px] font-bold leading-5 text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                {{ $attachmentError }}
            </div>
        @endif
    @endif
</div>
