<x-front.cart>
    <x-front.cart-title title="ارسال دیدگاه" iconName="comment-add-01"/>

    <form
        action="{{ route('posts.comments.store', $post) }}"
        method="POST"
        class="space-y-4"
        x-data="{ newRating: {{ (int) old('rating_percent', 0) / 20 }}, hoverRating: 0 }"
    >
        @csrf

        <input type="hidden" name="parent_id" value="{{ old('parent_id') }}">
        <input type="hidden" name="rating_percent" :value="newRating ? newRating * 20 : ''">

        @guest
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold text-slate-600">نام شما</label>
                    <input
                        name="author_name"
                        value="{{ old('author_name') }}"
                        required
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                        placeholder="نام شما (الزامی)"
                    >

                    @error('author_name')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold text-slate-600">ایمیل شما</label>
                    <input
                        type="email"
                        name="author_email"
                        value="{{ old('author_email') }}"
                        required
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                        placeholder="ایمیل شما (الزامی)"
                    >

                    @error('author_email')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endguest

        <div>
            <label class="mb-2 block text-xs font-bold text-slate-600">متن دیدگاه</label>
            <textarea
                name="content"
                rows="4"
                required
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-7 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                placeholder="متن دیدگاه خود را بنویسید…"
            >{{ old('content') }}</textarea>

            @error('content')
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-1.5" @mouseleave="hoverRating = 0">
                <template x-for="n in 5" :key="'rate'+n">
                    <button
                        type="button"
                        @mouseenter="hoverRating = n"
                        @click="newRating = n"
                        class="rounded-xl p-1 transition duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-brand-100"
                        :aria-label="'امتیاز ' + n"
                    >
                        <svg
                            viewBox="0 0 20 20"
                            class="h-7 w-7 transition"
                            :class="(hoverRating ? hoverRating : newRating) >= n ? 'text-amber-400' : 'text-slate-200'"
                        >
                            <path
                                fill="currentColor"
                                d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                            />
                        </svg>
                    </button>
                </template>

                <span
                    class="mr-2 text-xs font-bold text-slate-500"
                    x-text="newRating ? `${newRating} از 5` : 'بدون امتیاز'"
                ></span>
            </div>

            <button
                type="submit"
                class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-900 px-6 text-sm font-extrabold text-white transition hover:opacity-95 active:scale-[0.99]"
            >
                ارسال دیدگاه
            </button>
        </div>

        @error('rating_percent')
        <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </form>
</x-front.cart>
