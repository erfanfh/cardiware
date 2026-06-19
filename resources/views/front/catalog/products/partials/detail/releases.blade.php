@if($product->releases->isNotEmpty())
    <x-front.cart id="releases" class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
        <x-front.cart-title
            title="لیست تغییرات"
            subtitle="ویژگی‌ها و تغییرات هر آپدیت"
            iconName="reload"
        />

        <div
            class="mt-4 space-y-3 sm:mt-5"
            x-data="{
                openRelease: null,

                toggleRelease(index) {
                    this.openRelease = this.openRelease === index ? null : index;
                },

                isReleaseOpen(index) {
                    return this.openRelease === index;
                }
            }"
        >
            @foreach($product->releases as $i => $release)
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white sm:rounded-3xl dark:border-slate-700 dark:bg-slate-800/70">
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-between gap-3 bg-white px-4 py-4 text-right transition hover:bg-slate-50 sm:px-5 dark:bg-slate-800/70 dark:hover:bg-slate-800"
                        @click="toggleRelease({{ $i }})"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-extrabold leading-7 text-slate-900 dark:text-white">
                                    {{ $release->title }}
                                </span>

                                <span
                                    class="rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                    {{ jalali_date($release->released_at, 'Y/m/d') }}
                                </span>
                            </div>

                            <div class="mt-1 line-clamp-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                {{ $release->excerpt }}
                            </div>
                        </div>

                        <span
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            <svg
                                class="h-4 w-4 transition-transform duration-300"
                                :class="isReleaseOpen({{ $i }}) ? 'rotate-180' : ''"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </button>

                    <div
                        x-show="isReleaseOpen({{ $i }})"
                        x-collapse.duration.300ms
                        x-cloak
                        class="bg-slate-50 dark:bg-slate-900/60"
                    >
                        <div class="px-4 py-4 sm:px-5 sm:py-5">
                            <ul class="space-y-3">
                                @foreach($release->releaseChanges as $change)
                                    <li class="flex items-start gap-3 text-sm leading-7 text-slate-700 dark:text-slate-300">
                                        <span
                                            @class([
                                                'mt-2 shrink-0 rounded-full',
                                                'h-2 w-2 bg-brand-600 dark:bg-brand-400' => ! $change->is_special,
                                                'relative h-2.5 w-2.5 bg-brand-600 shadow-[0_0_0_4px_rgba(249,115,22,0.12)] animate-pulse dark:bg-brand-400 dark:shadow-[0_0_0_4px_rgba(251,146,60,0.16)]' => $change->is_special,
                                            ])
                                        >
                                            @if($change->is_special)
                                                <span
                                                    class="absolute inset-0 animate-ping rounded-full bg-brand-500 opacity-75 dark:bg-brand-400"></span>
                                                <span
                                                    class="absolute inset-0 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                                            @endif
                                        </span>

                                        <span class="min-w-0">{{ $change->title }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-front.cart>
@endif
