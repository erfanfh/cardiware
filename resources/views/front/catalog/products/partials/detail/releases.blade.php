@if($product->releases->isNotEmpty())
    <x-front.cart id="releases">
        <x-front.cart-title
            title="لیست تغییرات"
            subtitle="ویژگی‌ها و تغییرات هر آپدیت"
            iconName="reload"
        />

        <div
            class="space-y-3"
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
                <div class="rounded-3xl border border-slate-200 overflow-hidden">
                    <button
                        type="button"
                        class="cursor-pointer w-full px-4 sm:px-5 py-4 flex items-center justify-between gap-3 bg-white hover:bg-slate-50 transition text-right"
                        @click="toggleRelease({{ $i }})"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-extrabold text-slate-900">{{ $release->title }}</span>
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
                                    {{ jalali_date($release->released_at, 'Y/m/d') }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-slate-500">{{ $release->excerpt }}</div>
                        </div>

                        <span
                            class="shrink-0 h-9 w-9 rounded-2xl border border-slate-200 bg-slate-50 grid place-items-center text-slate-600">
                            <svg
                                class="h-4 w-4 transition-transform duration-300"
                                :class="isReleaseOpen({{ $i }}) ? 'rotate-180' : ''"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M6 9l6 6 6-6"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </span>
                    </button>

                    <div
                        x-show="isReleaseOpen({{ $i }})"
                        x-collapse.duration.300ms
                        x-cloak
                        class="bg-slate-50"
                    >
                        <div class="px-4 sm:px-5 py-5">
                            <ul class="space-y-3">
                                @foreach($release->releaseChanges as $change)
                                    <li class="flex items-center gap-3 text-sm text-slate-700">
                                        <span
                                            @class([
                                                'shrink-0 rounded-full',
                                                'h-2 w-2 bg-brand-600' => ! $change->is_special,
                                                'relative h-2.5 w-2.5 bg-brand-600 shadow-[0_0_0_4px_rgba(249,115,22,0.12)] animate-pulse' => $change->is_special,
                                            ])
                                        >
                                            @if($change->is_special)
                                                <span
                                                    class="absolute inset-0 rounded-full bg-brand-500 animate-ping opacity-75"></span>
                                                <span class="absolute inset-0 rounded-full bg-brand-600"></span>
                                            @endif
                                        </span>
                                        <span>{{ $change->title }}</span>
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
