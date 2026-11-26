<div class="min-w-0">
    <form wire:submit.prevent="submit" class="space-y-4 sm:space-y-5">
        <input type="hidden" wire:model="parentId">
        <input type="hidden" wire:model="ratingPercent">
        <input type="hidden" wire:model="startedAt">

        <div class="hidden" aria-hidden="true">
            <label for="product-comment-website">Website</label>
            <input
                id="product-comment-website"
                type="text"
                wire:model.defer="website"
                tabindex="-1"
                autocomplete="off"
            >
        </div>

        @if($generalError || $errors->any())
            <div
                class="rounded-2xl border border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/30 px-4 py-3 text-xs leading-6 text-red-700 dark:text-red-300 sm:text-sm">
                <div class="font-extrabold">Please review the following:</div>

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
                <div class="min-w-0">
                    <label class="mb-2 block text-xs font-bold text-slate-600 dark:text-slate-300">
                        Your name
                    </label>

                    <input
                        type="text"
                        wire:model.defer="authorName"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        class="h-11 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 dark:disabled:bg-slate-800"
                    >
                </div>

                <div class="min-w-0">
                    <label class="mb-2 block text-xs font-bold text-slate-600 dark:text-slate-300">
                        Your email
                    </label>

                    <input
                        type="email"
                        wire:model.defer="authorEmail"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        dir="ltr"
                        class="h-11 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 text-left text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 dark:disabled:bg-slate-800"
                    >
                </div>
            </div>
        @endguest

        <div class="min-w-0">
            <label class="mb-2 block text-xs font-bold text-slate-600 dark:text-slate-300">
                Comment text
            </label>

            <textarea
                rows="5"
                wire:model.defer="content"
                wire:loading.attr="disabled"
                wire:target="submit"
                class="w-full resize-none rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm leading-7 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 dark:disabled:bg-slate-800 sm:rounded-3xl"
            ></textarea>
        </div>

        <div
            x-data="{
                hoverRating: 0,
                selectedRating: @entangle('ratingPercent'),
                get currentStars() {
                    return this.selectedRating ? Math.floor(this.selectedRating / 20) : 0;
                },
                setRating(value) {
                    const nextValue = this.currentStars === value ? null : value * 20;

                    this.selectedRating = nextValue;
                    $wire.generalError = null;
                }
            }"
            class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:rounded-3xl"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-black text-slate-700 dark:text-slate-200">
                        Your rating
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1" @mouseleave="hoverRating = 0">
                    @for($n = 1; $n <= 5; $n++)
                        <button
                            type="button"
                            @mouseenter="hoverRating = {{ $n }}"
                            @click="setRating({{ $n }})"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            class="cursor-pointer rounded-xl p-1 transition duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-brand-100 disabled:cursor-not-allowed disabled:opacity-60"
                            aria-label="points {{ $n }}"
                        >
                            <svg
                                viewBox="0 0 20 20"
                                class="h-7 w-7 transition"
                                :class="(hoverRating ? hoverRating : currentStars) >= {{ $n }} ? 'text-amber-400' : 'text-slate-200 dark:text-slate-600'"
                            >
                                <path
                                    fill="currentColor"
                                    d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                                />
                            </svg>
                        </button>
                    @endfor

                    <span
                        class="mr-1 text-xs font-extrabold text-slate-500 dark:text-slate-400 sm:mr-2"
                        x-text="currentStars ? `${currentStars} of 5` : 'No rating'"
                    ></span>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col-reverse gap-3 border-t border-slate-100 dark:border-slate-800 pt-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                Comments appear after review.
            </p>

            <div class="grid grid-cols-1 gap-2 sm:flex sm:items-center">
                <button
                    type="button"
                    x-data
                    @click="$dispatch('close-modal', { name: @js($modalName) })"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 text-sm font-extrabold text-slate-600 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-6 text-sm font-extrabold text-white transition hover:bg-brand-600 hover:opacity-95 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto"
                >
                    <svg wire:loading wire:target="submit" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                stroke-width="4"></circle>
                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                              stroke-linecap="round"></path>
                    </svg>

                    <span wire:loading.remove wire:target="submit">
                        Submit comment
                    </span>

                    <span wire:loading wire:target="submit">
                        Submitting...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
