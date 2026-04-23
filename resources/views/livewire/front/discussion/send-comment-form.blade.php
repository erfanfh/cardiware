<x-front.cart id="send-comment-form" class="scroll-mt-40">
    <x-front.cart-title title="ارسال دیدگاه" iconName="comment-add-01"/>

    <div
        x-data
        x-on:discussion-comment-form-submitted.window="
            $nextTick(() => {
                $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
        "
    >
        <form
            wire:submit.prevent="submit"
            class="space-y-4"
        >
            <input type="hidden" wire:model="parentId">
            <input type="hidden" wire:model="ratingPercent">

            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input
                    id="website"
                    type="text"
                    wire:model.defer="website"
                    tabindex="-1"
                    autocomplete="off"
                >
            </div>

            <input type="hidden" wire:model="startedAt">

            @if($successMessage)
                <div
                    class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                    {{ $successMessage }}
                </div>
            @endif

            @if($generalError || $errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="font-extrabold">لطفاً موارد زیر را بررسی کنید:</div>

                    @if($generalError)
                        <p class="mt-2 font-medium">{{ $generalError }}</p>
                    @endif

                    @if($errors->any())
                        <ul class="mt-2 list-disc space-y-1 pr-5">
                            @foreach($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @guest
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold text-slate-600">نام شما</label>
                        <input
                            type="text"
                            wire:model.defer="authorName"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50"
                            placeholder="نام شما (الزامی)"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold text-slate-600">ایمیل شما</label>
                        <input
                            type="email"
                            wire:model.defer="authorEmail"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50"
                            placeholder="ایمیل شما (الزامی)"
                        >
                    </div>
                </div>
            @endguest

            <div>
                <label class="mb-2 block text-xs font-bold text-slate-600">متن دیدگاه</label>
                <textarea
                    rows="4"
                    wire:model.defer="content"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-7 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50"
                    placeholder="متن دیدگاه خود را بنویسید…"
                ></textarea>
            </div>

            <div
                x-data="{ hoverRating: 0 }"
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center" @mouseleave="hoverRating = 0">
                    @for($n = 1; $n <= 5; $n++)
                        <button
                            type="button"
                            @mouseenter="hoverRating = {{ $n }}"
                            wire:click="setRating({{ $n }})"
                            wire:loading.attr="disabled"
                            wire:target="setRating,submit"
                            class="cursor-pointer rounded-xl p-1 transition duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-brand-100 disabled:cursor-not-allowed disabled:opacity-60"
                            aria-label="امتیاز {{ $n }}"
                        >
                            <svg
                                viewBox="0 0 20 20"
                                class="h-6 w-6 transition"
                                :class="(hoverRating ? hoverRating : {{ $this->currentRatingStars }}) >= {{ $n }} ? 'text-amber-400' : 'text-slate-200'"
                            >
                                <path
                                    fill="currentColor"
                                    d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                                />
                            </svg>
                        </button>
                    @endfor

                    <span class="mr-2 text-xs font-bold text-slate-500">
                        {{ $this->currentRatingStars ? $this->currentRatingStars . ' از 5' : 'بدون امتیاز' }}
                    </span>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white transition hover:bg-brand-600 hover:opacity-95 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <svg
                        wire:loading
                        wire:target="submit"
                        class="h-4 w-4 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-opacity="0.25"
                            stroke-width="4"
                        ></circle>
                        <path
                            d="M22 12a10 10 0 0 1-10 10"
                            stroke="currentColor"
                            stroke-width="4"
                            stroke-linecap="round"
                        ></path>
                    </svg>

                    <span wire:loading.remove wire:target="submit">ارسال دیدگاه</span>
                    <span wire:loading wire:target="submit">در حال ارسال...</span>
                </button>
            </div>
        </form>
    </div>
</x-front.cart>
