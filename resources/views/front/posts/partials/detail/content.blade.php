<article class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    <div class="p-5 pb-0 sm:p-6 sm:pb-0">
        <div class="text-center">
            <div class="relative inline-block overflow-hidden rounded-2xl border border-slate-200">
                <img
                    src="{{ $post->picture->url }}"
                    alt="{{ $post->title }}"
                    title="{{ $post->title }}"
                    class="block max-w-full h-auto"
                />

                <div class="absolute inset-0 bg-gradient-to-t from-black/0 via-black/30 to-transparent"></div>

                <div class="absolute top-4 right-4 left-4 flex flex-wrap justify-start items-center gap-2">
                    @php $post->categories @endphp
                    @foreach($post->categories as $category)
                        <span
                            class="text-xs font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">
                            {{ $category->title }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- body -->
    <div class="p-5 sm:p-6">
        <p class="text-sm leading-7 text-slate-700">
            در این نسخه تمرکز اصلی روی پایداری، سرعت لود، کاهش کرش‌ها و سازگاری بهتر با برخی سیستم‌ها
            بوده
            است.
            در ادامه تغییرات را به صورت دقیق مرور می‌کنیم.
        </p>

        <div class="mt-6 space-y-6">
            <div>
                <div class="text-base font-extrabold">مهم‌ترین تغییرات</div>
                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                    <li class="flex items-start gap-3"><span
                            class="mt-2 h-2 w-2 rounded bg-brand-600"></span>بهبود سرعت لود اولیه و کاهش
                        زمان ورود به منوها
                    </li>
                    <li class="flex items-start gap-3"><span
                            class="mt-2 h-2 w-2 rounded bg-brand-600"></span>رفع کرش‌های مرتبط با
                        Alt+Tab در
                        برخی سیستم‌ها
                    </li>
                    <li class="flex items-start gap-3"><span
                            class="mt-2 h-2 w-2 rounded bg-brand-600"></span>بهبود سازگاری با مودهای
                        گرافیکی
                        رایج
                    </li>
                    <li class="flex items-start gap-3"><span
                            class="mt-2 h-2 w-2 rounded bg-brand-600"></span>بهینه‌سازی مصرف رم در
                        صحنه‌های
                        شلوغ
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
                <div class="text-sm font-extrabold text-emerald-900">نکته مهم</div>
                <p class="mt-1 text-sm text-emerald-800 leading-7">
                    اگر از نسخه‌های قدیمی‌تر استفاده می‌کنید، قبل از آپدیت حتماً از تنظیمات و فایل‌های
                    مهم
                    بکاپ بگیرید.
                </p>
            </div>

            <div>
                <div class="text-base font-extrabold">تصویر نمونه</div>
                <img class="mt-3 rounded-2xl border border-slate-200 shadow-sm"
                     src="https://picsum.photos/seed/mw-single-inline/1200/700" alt=""/>
            </div>
        </div>

        <!-- DOWNLOADS (moved ABOVE tags, list-only, not segmented into parts) -->
        <section x-data="downloadsBlock()"
                 class="mt-6 rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <div class="p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                  <span
                      class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                      <path d="M7 10l5 5 5-5"></path>
                      <path d="M12 15V3"></path>
                    </svg>
                  </span>
                        <div>
                            <div class="text-base font-extrabold">لینک‌های دانلود این نوشته</div>
                            <div class="mt-1 text-xs text-slate-500">چندین Mirror برای یک فایل (در صورت
                                کندی
                                یکی دیگر را امتحان کنید)
                            </div>
                        </div>
                    </div>

                    <!-- password copy only (no showing MW-093) -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="copyPassword($event)"
                            class="h-11 px-4 rounded-2xl bg-slate-900 text-white text-sm font-extrabold hover:opacity-95 transition inline-flex items-center gap-2">
                            کپی رمز فایل
                            <span
                                class="inline-flex h-7 w-7 rounded-xl bg-white/10 border border-white/10 items-center justify-center">🔒</span>
                        </button>

                        <button
                            @click="copyText('https://example.com/post/slug', $event)"
                            class="h-11 px-4 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-brand-700 hover:bg-brand-50 hover:border-brand-200 transition inline-flex items-center gap-2">
                            کپی لینک صفحه
                            <span
                                class="inline-flex h-7 w-7 rounded-xl bg-brand-50 border border-brand-100 items-center justify-center">⧉</span>
                        </button>
                    </div>
                </div>

                <!-- unified list of mirrors -->
                <div class="mt-5 space-y-2">
                    <template x-for="(m, i) in mirrors" :key="'m'+i">
                        <div
                            class="rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-sm font-extrabold truncate"
                                                          x-text="m.title"></span>
                                        <span
                                            class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl"
                                            x-text="m.badge"></span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500" x-text="m.meta"></div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a :href="m.url"
                                       class="rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 active:bg-brand-700 transition">
                                        دانلود
                                    </a>

                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- toast -->
                <div x-show="toast.show" x-transition
                     class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-3 text-sm text-emerald-900 flex items-center justify-between">
                    <div class="font-extrabold" x-text="toast.text"></div>
                    <button @click="toast.show=false"
                            class="h-9 w-9 rounded-2xl border border-emerald-200 bg-white hover:bg-emerald-50 transition grid place-items-center">
                        ×
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        اگر لینک‌ها مشکل داشتند، در دیدگاه‌ها گزارش دهید تا سریع‌تر Mirror جدید اضافه
                        شود.
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="#"
                           class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-800 hover:bg-slate-50 transition">
                            راهنمای نصب
                        </a>
                        <a href="#comments"
                           class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-extrabold text-white hover:opacity-95 transition">
                            گزارش مشکل
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tags (NOW after downloads) -->
        <div class="mt-6 border-t border-slate-200 pt-5 flex flex-wrap items-center gap-2">
            <span class="text-xs text-slate-500">برچسب‌ها:</span>
            <a href="#"
               class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl hover:bg-brand-100 transition">Patch</a>
            <a href="#"
               class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl hover:bg-slate-50 transition">Performance</a>
            <a href="#"
               class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl hover:bg-slate-50 transition">Fix</a>
        </div>
    </div>
</article>
