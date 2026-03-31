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

        @include('front.posts.partials.detail.download-box')

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
