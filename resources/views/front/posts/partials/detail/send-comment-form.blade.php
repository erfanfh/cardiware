<form
    action="{{ route('posts.comments.store', $post) }}"
    method="POST"
    class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:p-5"
    x-data="{ newRating: {{ (int) old('rating_percent', 0) }}, hoverRating: 0 }"
>
    @csrf

    <input type="hidden" name="parent_id" value="{{ old('parent_id') }}">
    <input type="hidden" name="rating_percent" :value="newRating ? newRating * 20 : ''">

    @guest
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Name -->
            <div>
                <input
                    name="author_name"
                    value="{{ old('author_name') }}"
                    required
                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                    placeholder="نام شما (الزامی)"
                >

                @error('author_name')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <input
                    type="email"
                    name="author_email"
                    value="{{ old('author_email') }}"
                    required
                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                    placeholder="ایمیل شما (الزامی)"
                >

                @error('author_email')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    @endguest

    <div>
        <textarea
            name="content"
            rows="3"
            required
            class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
            placeholder="دیدگاه شما…"
        >{{ old('content') }}</textarea>

        @error('content')
        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Bottom row: Stars + Submit button -->
    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <!-- Rating Stars -->
        <div
            class="flex items-center gap-2"
            @mouseleave="hoverRating = 0"
        >
            <span class="text-xs text-slate-500 ml-2">امتیاز شما:</span>

            <template x-for="n in 5" :key="'rate'+n">
                <button
                    type="button"
                    @mouseenter="hoverRating = n"
                    @click="newRating = n"
                    class="p-0.5 rounded-lg transition-transform duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-brand-100"
                >
                    <svg
                        viewBox="0 0 20 20"
                        class="h-7 w-7 drop-shadow-sm"
                        :class="(hoverRating ? hoverRating : newRating) >= n ? 'text-amber-400' : 'text-slate-300'"
                    >
                        <path
                            fill="currentColor"
                            d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                        />
                    </svg>
                </button>
            </template>
        </div>

        <!-- Submit -->
        <button
            type="submit"
            class="rounded-2xl bg-slate-900 text-white px-6 py-3 text-sm font-extrabold hover:opacity-95 transition"
        >
            ارسال دیدگاه
        </button>
    </div>

    @error('rating_percent')
    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
    @enderror
</form>
