<section id="comments"
         x-data="commentsBlock()"
         class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    <div class="p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
<span
    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
   <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
 </svg>
</span>
                <div>
                    <div class="text-base font-extrabold">دیدگاه‌ها</div>
                </div>
                <span
                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl"
                    x-text="comments.length"></span>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">مرتب‌سازی:</span>
                <select x-model="sort"
                        class="h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none
              focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                    <option value="new">جدیدترین</option>
                    <option value="top">محبوب‌ترین</option>
                    <option value="high">بیشترین امتیاز</option>
                </select>
            </div>
        </div>
        <!-- Add comment -->
        <div class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:p-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Name -->
                <input required
                       class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none
                    focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                       placeholder="نام شما (الزامی)"/>

                <!-- Email -->
                <input type="email" required
                       class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none
                    focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                       placeholder="ایمیل شما (الزامی)"/>
            </div>

            <textarea rows="3"
                      class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none
             focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                      placeholder="دیدگاه شما…"></textarea>

            <!-- Bottom row: Stars + Submit button -->
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                <!-- Rating Stars -->
                <div class="flex items-center gap-2"
                     x-data="{ hoverRating: 0 }"
                     @mouseleave="hoverRating = 0">

                    <span class="text-xs text-slate-500 ml-2">امتیاز شما:</span>

                    <template x-for="n in 5" :key="'rate'+n">
                        <button
                            type="button"
                            @mouseenter="hoverRating = n"
                            @click="newRating = n"
                            class="p-0.5 rounded-lg transition-transform duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-brand-100"
                        >
                            <svg viewBox="0 0 20 20"
                                 class="h-7 w-7 drop-shadow-sm"
                                 :class="(hoverRating ? hoverRating : newRating) >= n ? 'text-amber-400' : 'text-slate-300'">
                                <path fill="currentColor"
                                      d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"/>
                            </svg>
                        </button>
                    </template>

                </div>

                <!-- Submit -->
                <button
                    class="rounded-2xl bg-slate-900 text-white px-6 py-3 text-sm font-extrabold hover:opacity-95 transition">
                    ارسال دیدگاه
                </button>
            </div>
        </div>
        <!-- list -->
        <div class="mt-5 space-y-4">
            <template x-for="c in sortedComments()" :key="c.id">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">

                        <div class="flex items-start gap-3 min-w-0 w-full">

                            <!-- Avatar -->
                            <div
                                class="h-11 w-11 rounded-2xl bg-slate-100 border border-slate-200 grid place-items-center font-black text-slate-700 shrink-0"
                                x-text="c.name.charAt(0)"></div>

                            <div class="min-w-0 w-full">

                                <!-- Header -->
                                <div class="flex items-center justify-between gap-3">

                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="text-sm font-extrabold truncate"
                                             x-text="c.name"></div>
                                        <span class="text-xs text-slate-500" x-text="c.time"></span>
                                    </div>

                                    <!-- ⭐ Rating pushed fully to left -->
                                    <div class="flex items-center gap-0.5">
                                        <template x-for="n in 5" :key="'cstar'+c.id+'-'+n">
                                            <svg viewBox="0 0 20 20"
                                                 class="h-4 w-4"
                                                 :class="c.rating >= n ? 'text-amber-400' : 'text-slate-300'">
                                                <path fill="currentColor"
                                                      d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"/>
                                            </svg>
                                        </template>
                                    </div>

                                </div>

                                <div class="mt-2 text-sm text-slate-700 leading-7"
                                     x-text="c.text"></div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <button
                                        @click="toggleLike(c)"
                                        class="h-10 px-3 rounded-2xl border transition inline-flex items-center gap-2 text-sm font-extrabold"
                                        :class="c.liked ? 'border-brand-200 bg-brand-50 text-brand-700' : 'border-slate-200 bg-white text-slate-800 hover:bg-slate-50'">
                                        <span>♥</span>
                                        <span x-text="c.likes"></span>
                                    </button>

                                    <button
                                        @click="startReply(c)"
                                        class="h-10 px-3 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-800 hover:bg-slate-50 transition inline-flex items-center gap-2">
                                        پاسخ <span class="text-slate-400">↩</span>
                                    </button>
                                </div>

                                <!-- Reply box (wider) -->
                                <div x-show="replyTo === c.id" x-transition
                                     class="mt-4 w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-sm font-extrabold text-slate-800">
                                            پاسخ به <span class="text-brand-700" x-text="c.name"></span>
                                        </div>
                                        <button @click="replyTo=null"
                                                class="h-9 w-9 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center">
                                            ×
                                        </button>
                                    </div>

                                    <textarea rows="3"
                                              class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none
                                     focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                                              placeholder="پاسخ شما…"></textarea>

                                    <div class="mt-3 flex items-center justify-end gap-2">
                                        <button @click="replyTo=null"
                                                class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold hover:bg-slate-50 transition">
                                            لغو
                                        </button>
                                        <button
                                            class="h-10 px-4 rounded-2xl bg-brand-500 text-white text-sm font-extrabold shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                                            ارسال پاسخ
                                        </button>
                                    </div>
                                </div>


                                <div class="mt-4 space-y-3" x-show="c.replies?.length">
                                    <template x-for="r in c.replies" :key="r.id">
                                        <div
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <div
                                                        class="h-9 w-9 rounded-2xl bg-white border border-slate-200 grid place-items-center font-black text-slate-700"
                                                        x-text="r.name.charAt(0)"></div>
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-extrabold truncate"
                                                             x-text="r.name"></div>
                                                        <div class="text-xs text-slate-500"
                                                             x-text="r.time"></div>
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-[11px] font-extrabold text-slate-700 bg-white border border-slate-200 px-2 py-1 rounded-xl">پاسخ</span>
                                            </div>
                                            <div class="mt-2 text-sm text-slate-700 leading-7"
                                                 x-text="r.text"></div>
                                        </div>
                                    </template>
                                </div>

                            </div>
                        </div>

                        <div class="hidden sm:flex flex-col items-end gap-2">

                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-xs text-slate-500">برای گزارش مشکل لینک‌ها، از بخش دانلود استفاده کنید.
            </div>
            <a href="#"
               class="text-sm font-extrabold text-brand-700 hover:text-brand-800 transition inline-flex items-center gap-2">
                قوانین دیدگاه‌ها <span class="font-black">←</span>
            </a>
        </div>
    </div>
</section>
