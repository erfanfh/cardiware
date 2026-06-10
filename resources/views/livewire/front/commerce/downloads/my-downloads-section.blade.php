<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-1">
        <div class="text-base font-extrabold text-slate-900">دانلودهای من</div>
        <div class="text-xs text-slate-500">فایل‌های خریداری‌شده شما به ترتیب آخرین خرید</div>
    </div>

    <div class="mt-5 space-y-4">
        @forelse($this->purchaseAccesses as $access)
            @php
                $files = $access->productSaleItem?->files ?? collect();
                $productUrl = $this->productUrl($access);
                $hasInstallationSteps = $this->hasInstallationSteps($access);
            @endphp

            <article
                x-data="{ open: false, installationModal: false }"
                wire:key="purchase-access-{{ $access->id }}"
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex min-w-0 flex-1 gap-4 text-right"
                        >
                            <img
                                src="{{ $this->accessImage($access) }}"
                                alt="{{ $this->accessTitle($access) }}"
                                class="h-24 w-32 shrink-0 rounded-2xl object-cover ring-1 ring-slate-200 sm:h-28 sm:w-40"
                            >

                            <div class="min-w-0 flex-1">
                                <a
                                    href="{{ $productUrl }}"
                                    @click.stop
                                    class="mt-2 block text-sm font-black leading-8 text-slate-900 transition hover:text-brand-700 sm:text-base"
                                >
                                    {{ $this->accessTitle($access) }}
                                </a>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[11px] font-extrabold text-slate-700">
                                        {{ $this->expiresLabel($access) }}
                                    </span>

                                    <span
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[11px] font-extrabold text-slate-500">
                                        {{ $this->lastDownloadedLabel($access) }}
                                    </span>

                                    <div
                                        class="cursor-pointer inline-flex gap-1 rounded-xl border border-brand-200 bg-brand-50 px-2.5 py-1.5 text-[11px] font-extrabold text-brand-700">
                                        <span x-text="open ? 'بستن فایل‌ها' : 'مشاهده فایل‌های دانلود'"></span>
                                        <svg
                                            class="h-4 w-4 transition"
                                            :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                        >
                                            <path d="m6 9 6 6 6-6"
                                                  stroke="currentColor"
                                                  stroke-width="2"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </button>

                        <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                            <button
                                type="button"
                                @click="installationModal = true"
                                class="cursor-pointer inline-flex h-10 items-center justify-center gap-1.5 rounded-2xl border px-4 text-xs font-extrabold transition
                                {{ $hasInstallationSteps ? 'border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100' : 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400' }}"
                                @disabled(! $hasInstallationSteps)
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 17v-6M12 7h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                          stroke="currentColor"
                                          stroke-width="2"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                                راهنمای نصب
                            </button>
                        </div>
                    </div>

                    <div
                        x-show="open"
                        x-collapse
                        class="mt-4"
                    >
                        @if($files->isNotEmpty())
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                @foreach($files as $file)
                                    @php
                                        $fileSize = $this->fileSizeLabel($file);
                                        $fileIsUsable = $this->fileIsUsable($access, $file);
                                    @endphp

                                    <div
                                        wire:key="purchase-access-{{ $access->id }}-file-{{ $file->id }}"
                                        class="flex flex-col gap-3 border-b border-slate-200 p-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div class="min-w-0">
                                            <div class="text-sm font-extrabold leading-7 text-slate-900">
                                                {{ $this->fileTitle($file) }}
                                            </div>

                                            <div
                                                class="mt-1 flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-500">
                                                @if($fileSize)
                                                    <span>{{ $fileSize }}</span>
                                                    <span class="text-slate-300">•</span>
                                                @endif

                                                <span>{{ $this->fileRemainingDownloadsLabel($access, $file) }}</span>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0">
                                            @if($fileIsUsable)
                                                <a
                                                    href="{{ $this->downloadUrl($access->id, $file->id) }}"
                                                    class="inline-flex h-11 items-center justify-center rounded-2xl bg-orange-500 px-6 text-sm font-extrabold text-white shadow-soft transition hover:bg-orange-600"
                                                >
                                                    دانلود
                                                </a>
                                            @else
                                                <span
                                                    class="inline-flex h-11 cursor-not-allowed items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 px-6 text-sm font-extrabold text-slate-400">
                                                    غیرفعال
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs font-bold leading-6 text-amber-800">
                                برای این خرید هنوز فایلی ثبت نشده است.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Installation Modal --}}
                <div
                    x-show="installationModal"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    aria-modal="true"
                    role="dialog"
                >
                    <div
                        x-show="installationModal"
                        x-transition.opacity
                        @click="installationModal = false"
                        class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                    ></div>

                    <div
                        x-show="installationModal"
                        x-transition
                        @click.stop
                        class="relative w-full max-w-xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200"
                    >
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-5">
                            <div>
                                <div class="text-base font-black text-slate-900">راهنمای نصب</div>
                                <div class="mt-1 text-xs font-bold leading-6 text-slate-500 line-clamp-1">
                                    {{ $this->accessTitle($access) }}
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="installationModal = false"
                                class="cursor-pointer grid h-10 w-10 place-items-center rounded-2xl bg-slate-50 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M18 6 6 18M6 6l12 12"
                                          stroke="currentColor"
                                          stroke-width="2"
                                          stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto p-5">
                            @if($hasInstallationSteps)
                                <div class="space-y-3">
                                    @foreach($access->product->installationSteps as $step)
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <div class="flex gap-3">
                                                <div
                                                    class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-brand-50 text-xs font-black text-brand-700 ring-1 ring-brand-100">
                                                    {{ $loop->iteration }}
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="text-sm font-extrabold text-slate-900">
                                                        {{ $step->title }}
                                                    </div>

                                                    <div class="mt-2 text-sm font-medium leading-8 text-slate-600">
                                                        {!! nl2br(e($step->value)) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center text-sm font-bold text-slate-500">
                                    راهنمای نصب برای این محصول ثبت نشده است.
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-slate-100 p-4">
                            <button
                                type="button"
                                @click="installationModal = false"
                                class="cursor-pointer inline-flex h-11 w-full items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800"
                            >
                                متوجه شدم
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <div
                    class="mx-auto grid h-14 w-14 place-items-center rounded-3xl bg-slate-50 text-slate-400 ring-1 ring-slate-200">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                        <path d="M12 4v11m0 0 4-4m-4 4-4-4M5 20h14"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </div>

                <div class="mt-4 text-sm font-extrabold text-slate-800">
                    دانلودی یافت نشد
                </div>

                <p class="mt-2 text-xs leading-6 text-slate-500">
                    بعد از خرید محصولات دیجیتال، فایل‌های قابل دانلود شما در این بخش نمایش داده می‌شوند.
                </p>
            </div>
        @endforelse
    </div>
</section>
