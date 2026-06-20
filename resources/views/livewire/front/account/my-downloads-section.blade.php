<section
    id="my-downloads-section"
    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
>
    <div class="flex flex-col gap-1">
        <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">دانلودهای من</div>
        <div class="text-xs leading-5 text-slate-500 dark:text-slate-400">فایل‌های خریداری‌شده شما</div>
    </div>

    <div class="mt-4 space-y-3 transition sm:mt-5 sm:space-y-4" wire:loading.class="opacity-40">
        @forelse($this->purchaseAccesses as $access)
            @php
                $files = $access->productSaleItem?->files ?? collect();
                $productUrl = $this->productUrl($access);
                $hasInstallationSteps = $this->hasInstallationSteps($access);
            @endphp

            <article
                x-data="{ open: false, installationModal: false }"
                wire:key="purchase-access-{{ $access->id }}"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl dark:border-slate-700 dark:bg-slate-800/70 dark:shadow-black/20"
            >
                <div class="p-3 sm:p-4">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex min-w-0 flex-col gap-3 text-right sm:flex-row sm:gap-4"
                        >
                            <x-front.responsive-image
                                :media="$access->product?->picture"
                                :fallback="$access->product?->picture?->url ?? asset('img/placeholder.png')"
                                :alt="$this->accessTitle($access)"
                                :title="$this->accessTitle($access)"
                                class="aspect-video w-full rounded-2xl object-cover ring-1 ring-slate-200 sm:h-28 sm:w-40 sm:shrink-0 dark:ring-slate-700"
                                wrapper-class="aspect-video w-full sm:h-28 sm:w-40 sm:shrink-0 rounded-2xl overflow-hidden"
                                :lazy="true"
                                :skeleton="true"
                                fetchpriority="low"
                            />

                            <div class="min-w-0 flex-1">
                                <a
                                    href="{{ $productUrl }}"
                                    @click.stop
                                    class="block text-sm font-black leading-7 text-slate-900 transition hover:text-brand-700 sm:mt-1 sm:text-base sm:leading-8 dark:text-white dark:hover:text-brand-300"
                                >
                                    {{ $this->accessTitle($access) }}
                                </a>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-700 sm:text-[11px] dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                                        {{ $this->expiresLabel($access) }}
                                    </span>

                                    <span
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-500 sm:text-[11px] dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-400">
                                        {{ $this->lastDownloadedLabel($access) }}
                                    </span>

                                    <span
                                        class="cursor-pointer inline-flex items-center gap-1 rounded-xl border border-brand-200 bg-brand-50 px-2.5 py-1.5 text-[10px] font-extrabold text-brand-700 sm:text-[11px] dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                        <span x-text="open ? 'بستن فایل‌ها' : 'مشاهده فایل‌ها'"></span>
                                        <svg class="h-4 w-4 transition" :class="open ? 'rotate-180' : ''"
                                             viewBox="0 0 24 24" fill="none">
                                            <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </button>

                        <div class="flex shrink-0">
                            <button
                                type="button"
                                @click="installationModal = true"
                                class="inline-flex h-10 w-full cursor-pointer items-center justify-center gap-1.5 rounded-2xl border px-4 text-xs font-extrabold transition sm:w-auto
                                {{ $hasInstallationSteps
                                    ? 'border-brand-200 bg-brand-50 text-brand-700 hover:bg-brand-100 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/15'
                                    : 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-500' }}"
                                @disabled(! $hasInstallationSteps)
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 17v-6M12 7h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                                راهنمای نصب
                            </button>
                        </div>
                    </div>

                    <div x-show="open" x-collapse class="mt-4">
                        @if($files->isNotEmpty())
                            <div
                                class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/60">
                                @foreach($files as $file)
                                    @php
                                        $fileSize = $this->fileSizeLabel($file);
                                        $fileIsUsable = $this->fileIsUsable($access, $file);
                                    @endphp

                                    <div
                                        wire:key="purchase-access-{{ $access->id }}-file-{{ $file->id }}"
                                        class="flex flex-col gap-3 border-b border-slate-200 p-3 last:border-b-0 sm:flex-row sm:items-center sm:justify-between sm:p-4 dark:border-slate-700"
                                    >
                                        <div class="min-w-0">
                                            <div
                                                class="text-sm font-extrabold leading-7 text-slate-900 dark:text-white">
                                                {{ $this->fileTitle($file) }}
                                            </div>

                                            <div
                                                class="mt-1 flex flex-wrap items-center gap-2 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                                                @if($fileSize)
                                                    <span>{{ $fileSize }}</span>
                                                    <span class="text-slate-300 dark:text-slate-600">•</span>
                                                @endif

                                                <span>{{ $this->fileRemainingDownloadsLabel($access, $file) }}</span>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0">
                                            @if($fileIsUsable)
                                                <a
                                                    href="{{ $this->downloadUrl($access->id, $file->id) }}"
                                                    class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-brand-500 px-6 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 dark:hover:bg-brand-400 sm:w-auto"
                                                >
                                                    دانلود
                                                </a>
                                            @else
                                                <span
                                                    class="inline-flex h-11 w-full cursor-not-allowed items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 px-6 text-sm font-extrabold text-slate-400 sm:w-auto dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                                                    غیرفعال
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs font-bold leading-6 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                                برای این خرید هنوز فایلی ثبت نشده است.
                            </div>
                        @endif
                    </div>
                </div>

                <div
                    x-show="installationModal"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4"
                    aria-modal="true"
                    role="dialog"
                >
                    <div
                        x-show="installationModal"
                        x-transition.opacity
                        @click="installationModal = false"
                        class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm dark:bg-slate-950/75"
                    ></div>

                    <div
                        x-show="installationModal"
                        x-transition
                        @click.stop
                        class="relative max-h-[92vh] w-full overflow-hidden rounded-t-3xl bg-white shadow-2xl ring-1 ring-slate-200 sm:max-w-xl sm:rounded-3xl dark:bg-slate-900 dark:ring-slate-800"
                    >
                        <div
                            class="flex items-start justify-between gap-4 border-b border-slate-100 p-4 sm:p-5 dark:border-slate-800">
                            <div class="min-w-0">
                                <div class="text-base font-black text-slate-900 dark:text-white">راهنمای نصب</div>
                                <div
                                    class="mt-1 line-clamp-1 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                                    {{ $this->accessTitle($access) }}
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="installationModal = false"
                                class="grid h-10 w-10 shrink-0 cursor-pointer place-items-center rounded-2xl bg-slate-50 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2"
                                          stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        <div class="max-h-[68vh] overflow-y-auto p-4 sm:max-h-[60vh] sm:p-5">
                            @if($hasInstallationSteps)
                                <div class="space-y-3">
                                    @foreach($access->product->installationSteps as $step)
                                        <div
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:p-4 dark:border-slate-700 dark:bg-slate-800/70">
                                            <div class="flex gap-3">
                                                <div
                                                    class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-brand-50 text-xs font-black text-brand-700 ring-1 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20">
                                                    {{ $loop->iteration }}
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">
                                                        {{ $step->title }}
                                                    </div>

                                                    <div
                                                        class="mt-2 text-sm font-medium leading-8 text-slate-600 dark:text-slate-300">
                                                        {!! nl2br(e($step->value)) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center text-sm font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400">
                                    راهنمای نصب برای این محصول ثبت نشده است.
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                            <button
                                type="button"
                                @click="installationModal = false"
                                class="inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200"
                            >
                                متوجه شدم
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center sm:rounded-3xl sm:p-8 dark:border-slate-700 dark:bg-slate-800/70">
                <div
                    class="mx-auto grid h-14 w-14 place-items-center rounded-3xl bg-slate-50 text-slate-400 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-500 dark:ring-slate-700">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                        <path d="M12 4v11m0 0 4-4m-4 4-4-4M5 20h14" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <div class="mt-4 text-sm font-extrabold text-slate-800 dark:text-white">دانلودی یافت نشد</div>

                <p class="mx-auto mt-2 max-w-sm text-xs leading-6 text-slate-500 dark:text-slate-400">
                    بعد از خرید محصولات دیجیتال، فایل‌های قابل دانلود شما در این بخش نمایش داده می‌شوند.
                </p>
            </div>
        @endforelse

        @if($this->purchaseAccesses->hasPages())
            <div class="pt-2 dark:text-slate-300">
                {{ $this->purchaseAccesses->links(data: ['scrollTo' => '#my-downloads-section']) }}
            </div>
        @endif
    </div>
</section>
