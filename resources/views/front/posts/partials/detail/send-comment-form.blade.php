<x-front.cart>
    <x-front.cart-title title="ارسال دیدگاه" iconName="comment-add-01"/>

    <form
        action="{{ route('posts.comments.store', $post) }}"
        method="POST"
        class="space-y-4"
        x-data="commentForm({
            endpoint: '{{ route('posts.comments.store', $post) }}',
            csrf: '{{ csrf_token() }}',
            isGuest: {{ auth()->guest() ? 'true' : 'false' }},
            initialParentId: '{{ old('parent_id') }}',
            initialAuthorName: @js(old('author_name', '')),
            initialAuthorEmail: @js(old('author_email', '')),
            initialContent: @js(old('content', '')),
            initialRating: {{ (int) old('rating_percent', 0) / 20 }},
        })"
        @submit.prevent="submit"
    >
        @csrf

        <input type="hidden" name="parent_id" x-model="form.parent_id">
        <input type="hidden" name="rating_percent" :value="form.rating_percent">

        @if(session('success'))
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div
            x-show="successMessage"
            x-cloak
            x-transition.opacity.duration.300ms
            class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"
            x-text="successMessage"
        ></div>

        <div
            x-show="generalError"
            x-cloak
            x-transition.opacity.duration.300ms
            class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"
            x-text="generalError"
        ></div>

        @guest
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold text-slate-600">نام شما</label>
                    <input
                        name="author_name"
                        x-model="form.author_name"
                        @input="clearFieldError('author_name')"
                        required
                        :disabled="loading"
                        class="h-11 w-full rounded-2xl border bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50"
                        :class="errors.author_name ? 'border-red-300' : 'border-slate-200'"
                        placeholder="نام شما (الزامی)"
                    >

                    @error('author_name')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <p
                        x-show="errors.author_name"
                        x-cloak
                        class="mt-2 text-xs text-red-600"
                        x-text="errors.author_name"
                    ></p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold text-slate-600">ایمیل شما</label>
                    <input
                        type="email"
                        name="author_email"
                        x-model="form.author_email"
                        @input="clearFieldError('author_email')"
                        required
                        :disabled="loading"
                        class="h-11 w-full rounded-2xl border bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50"
                        :class="errors.author_email ? 'border-red-300' : 'border-slate-200'"
                        placeholder="ایمیل شما (الزامی)"
                    >

                    @error('author_email')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <p
                        x-show="errors.author_email"
                        x-cloak
                        class="mt-2 text-xs text-red-600"
                        x-text="errors.author_email"
                    ></p>
                </div>
            </div>
        @endguest

        <div>
            <label class="mb-2 block text-xs font-bold text-slate-600">متن دیدگاه</label>
            <textarea
                name="content"
                rows="4"
                required
                x-model="form.content"
                @input="clearFieldError('content')"
                :disabled="loading"
                class="w-full rounded-2xl border bg-white px-4 py-3 text-sm leading-7 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50"
                :class="errors.content ? 'border-red-300' : 'border-slate-200'"
                placeholder="متن دیدگاه خود را بنویسید…"
            ></textarea>

            @error('content')
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <p
                x-show="errors.content"
                x-cloak
                class="mt-2 text-xs text-red-600"
                x-text="errors.content"
            ></p>
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-1.5" @mouseleave="hoverRating = 0">
                <template x-for="n in 5" :key="'rate'+n">
                    <button
                        type="button"
                        @mouseenter="hoverRating = n"
                        @click="setRating(n)"
                        :disabled="loading"
                        class="rounded-xl p-1 transition duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-brand-100 disabled:cursor-not-allowed disabled:opacity-60"
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
                :disabled="loading"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 text-sm font-extrabold text-white transition hover:opacity-95 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg
                    x-show="loading"
                    x-cloak
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

                <span x-text="loading ? 'در حال ارسال...' : 'ارسال دیدگاه'"></span>
            </button>
        </div>

        @error('rating_percent')
        <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
        @enderror

        <p
            x-show="errors.rating_percent"
            x-cloak
            class="mt-3 text-xs text-red-600"
            x-text="errors.rating_percent"
        ></p>
    </form>
</x-front.cart>
