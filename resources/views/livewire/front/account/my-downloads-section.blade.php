<section
    id="my-downloads-section"
    wire:poll.60s
    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
>
    <div class="flex flex-col gap-1">
        <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">دانلودهای من</div>
        <div class="text-xs leading-5 text-slate-500 dark:text-slate-400">
            محصولات خریداری‌شده و نسخه‌هایی که به دانلودهای VIP خود اضافه کرده‌اید
        </div>
    </div>

    <div class="mt-4 space-y-3 transition sm:mt-5 sm:space-y-4" wire:loading.class="opacity-40">
        @if($this->vipSaleItems->isNotEmpty())
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-violet-500"></span>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white">دانلودهای افزوده‌شده با VIP</h2>
                </div>
            </div>

            @foreach($this->vipSaleItems as $claim)
                @php
                    $vipSaleItem = $claim->saleItem;
                    $vipUsable = $this->vipClaimIsUsable($claim);
                    $vipHasInstallationSteps = $vipSaleItem?->product?->installationSteps?->isNotEmpty() ?? false;
                @endphp
                <article
                    id="vip-claim-{{ $claim->id }}"
                    x-data="{ open: false, installationModal: false }"
                    x-on:keydown.escape.window="installationModal = false"
                    x-init="if ({{ $vipItem && (int) $vipItem === (int) $claim->product_sale_item_id ? 'true' : 'false' }}) {
                        open = true;
                        $nextTick(() => {
                            const header = document.getElementById('header');
                            const stickyTop = header ? parseFloat(getComputedStyle(header).top) || 0 : 0;
                            $el.style.scrollMarginTop = (header ? header.offsetHeight + stickyTop : 0) + 16 + 'px';
                            $el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    }"
                    wire:key="vip-claim-{{ $claim->id }}"
                    class="scroll-mt-36 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl dark:border-slate-700 dark:bg-slate-800/70 dark:shadow-black/20"
                >
                    <div class="p-3 sm:p-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <button type="button" @click="open = !open" class="group flex min-w-0 flex-1 cursor-pointer flex-col gap-3 text-right transition hover:opacity-80 sm:flex-row sm:gap-4">
                                <x-front.responsive-image
                                    :media="$vipSaleItem?->product?->picture"
                                    :fallback="$vipSaleItem?->product?->picture?->url ?? asset('img/placeholder.png')"
                                    :alt="$claim->product_title"
                                    class="aspect-video w-full rounded-2xl object-cover ring-1 ring-slate-200 sm:h-28 sm:w-40 sm:shrink-0 dark:ring-slate-700"
                                    wrapper-class="aspect-video w-full sm:h-28 sm:w-40 sm:shrink-0 rounded-2xl overflow-hidden"
                                    :lazy="true"
                                />
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-black leading-7 text-slate-900 sm:mt-1 sm:text-base dark:text-white">{{ $vipSaleItem?->product?->title ?? $claim->product_title }}</div>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $vipSaleItem?->title ?? $claim->sale_item_title ?? 'نسخه اصلی محصول' }}</div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="rounded-xl border border-violet-200 bg-violet-50 px-2.5 py-1.5 text-[10px] font-extrabold text-violet-700 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-300">دسترسی VIP</span>
                                        <span class="rounded-xl border px-2.5 py-1.5 text-[10px] font-extrabold {{ $vipUsable ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300' : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300' }}">{{ $vipUsable ? 'فعال با اشتراک فعلی' : 'در حال حاضر غیرفعال' }}</span>
                                        <span class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[10px] font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-400">افزوده‌شده در {{ jalali_date($claim->claimed_at, 'Y/m/d') }}</span>
                                        <span class="inline-flex items-center gap-1 rounded-xl border border-orange-200 bg-orange-50 px-2.5 py-1.5 text-[10px] font-extrabold text-orange-700 transition-colors group-hover:bg-orange-100 sm:text-[11px] dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-300 dark:group-hover:bg-orange-500/15">
                                            <span x-text="open ? 'بستن فایل‌ها' : 'مشاهده فایل‌ها'"></span>
                                            <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </button>
                            <button type="button" @click="installationModal = true" @disabled(!$vipHasInstallationSteps)
                                class="inline-flex h-10 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-2xl border px-4 text-xs font-extrabold transition disabled:cursor-not-allowed sm:w-auto {{ $vipHasInstallationSteps ? 'border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-300 dark:hover:bg-orange-500/15' : 'border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-500' }}">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 17v-6M12 7h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                راهنمای نصب
                            </button>
                        </div>
                        <div x-show="open" x-collapse class="mt-4">
                            @if(!$vipUsable)
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs font-bold leading-6 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">{{ auth()->user()?->hasActiveVipSubscription() ? 'این نسخه یا فایل‌های آن دیگر برای دانلود VIP فعال نیست.' : 'برای دانلود دوباره، اشتراک VIP خود را تمدید کنید.' }}
                                    @unless(auth()->user()?->hasActiveVipSubscription())
                                        <a href="{{ route('vip.index') }}" class="mr-1 cursor-pointer font-black underline transition hover:text-amber-950 dark:hover:text-amber-100">مشاهده پلن‌ها</a>
                                    @endunless
                                </div>
                            @elseif($vipSaleItem->files->isNotEmpty())
                                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/60">
                                    @foreach($vipSaleItem->files as $file)
                                        <div wire:key="vip-claim-{{ $claim->id }}-file-{{ $file->id }}" class="flex flex-col gap-3 border-b border-slate-200 p-3 last:border-b-0 sm:flex-row sm:items-center sm:justify-between sm:p-4 dark:border-slate-700">
                                            <div class="min-w-0">
                                                <div class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $this->fileTitle($file) }}</div>
                                                <div class="mt-1 flex flex-wrap gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                                    @if($this->fileSizeLabel($file))<span>{{ $this->fileSizeLabel($file) }}</span><span aria-hidden="true">•</span>@endif
                                                    <span>{{ $this->vipFileRemainingDownloadsLabel($claim, $file) }}</span>
                                                </div>
                                            </div>
                                            @if($this->vipFileIsUsable($claim, $file))
                                                <a href="{{ $this->vipDownloadUrl($vipSaleItem, $file) }}" x-on:click="setTimeout(() => $wire.$refresh(), 5000)" class="inline-flex h-11 shrink-0 cursor-pointer items-center justify-center rounded-2xl bg-violet-700 px-6 text-sm font-extrabold text-white transition hover:bg-violet-800 dark:bg-violet-600 dark:hover:bg-violet-500">دانلود</a>
                                            @else
                                                <span class="inline-flex h-11 shrink-0 cursor-not-allowed items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 px-6 text-sm font-extrabold text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">سهمیه پایان یافته</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div x-show="installationModal" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4" aria-modal="true" role="dialog">
                        <div x-show="installationModal" x-transition.opacity @click="installationModal = false" class="absolute inset-0 cursor-pointer bg-slate-950/60 backdrop-blur-sm dark:bg-slate-950/75"></div>
                        <div x-show="installationModal" x-transition @click.stop class="relative max-h-[92vh] w-full overflow-hidden rounded-t-3xl bg-white shadow-2xl ring-1 ring-slate-200 sm:max-w-xl sm:rounded-3xl dark:bg-slate-900 dark:ring-slate-800">
                            <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-4 sm:p-5 dark:border-slate-800">
                                <div class="min-w-0">
                                    <div class="text-base font-black text-slate-900 dark:text-white">راهنمای نصب</div>
                                    <div class="mt-1 line-clamp-1 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">{{ $vipSaleItem?->product?->title ?? $claim->product_title }}</div>
                                </div>
                                <button type="button" @click="installationModal = false" aria-label="بستن راهنمای نصب" class="grid h-10 w-10 shrink-0 cursor-pointer place-items-center rounded-2xl bg-slate-50 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                            <div class="max-h-[68vh] overflow-y-auto p-4 sm:max-h-[60vh] sm:p-5">
                                @if($vipHasInstallationSteps)
                                    <div class="tinymce-content dark:text-slate-300">{!! $vipSaleItem->product->installationSteps->first()?->body !!}</div>
                                @endif
                            </div>
                            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                                <button type="button" @click="installationModal = false" class="inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">متوجه شدم</button>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach

            @if($this->purchaseAccesses->total() > 0)
                <div class="pt-3 text-sm font-black text-slate-900 dark:text-white">خریدهای من</div>
            @endif
        @endif

        @forelse($this->purchaseAccesses as $access)
            @php
                if($access->productSaleItem->launcher_enabled){
                    $launcherEnabled = true;
                    $files = collect();
                }else{
                    $launcherEnabled = false;
                    $files = $access->productSaleItem?->files ?? collect();
                }
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
                            class="flex min-w-0 cursor-pointer flex-col gap-3 text-right transition hover:opacity-80 sm:flex-row sm:gap-4"
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
                        @if($launcherEnabled)
                            <div
                                class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs font-bold leading-6 text-amber-800 sm:flex-row sm:items-center sm:justify-between dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                                <span>دسترسی به این محصول فقط از طریق لانچر رسمی Cardiware امکان پذیر می باشد.</span>
                                <a href="{{ route('launcher.open-product', ['saleItem' => $access->productSaleItem->getKey()]) }}"
                                   class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-2xl bg-orange-500 px-4 text-xs font-extrabold text-white shadow-soft transition hover:bg-orange-400">
                                    باز کردن محصول در لانچر
                                </a>
                            </div>
                        @else
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
                                                        class="inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl bg-brand-500 px-6 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 dark:hover:bg-brand-400 sm:w-auto"
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
                                <div class="tinymce-content dark:text-slate-300">
                                    {!! $access->product->installationSteps->first()?->body !!}
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
            @if($this->vipSaleItems->isEmpty())
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
                    بعد از خرید محصول یا افزودن یک نسخه با اشتراک VIP، آن را اینجا خواهید دید.
                </p>
            </div>
            @endif
        @endforelse

        @if($this->purchaseAccesses->hasPages())
            <div class="pt-2 dark:text-slate-300">
                {{ $this->purchaseAccesses->links(data: ['scrollTo' => '#my-downloads-section']) }}
            </div>
        @endif
    </div>
</section>
