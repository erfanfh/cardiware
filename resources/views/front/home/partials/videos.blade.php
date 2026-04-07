<section class="mt-10" id="video-tutorials">
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700">
                @include('icons.play')
            </span>
            <h3 class="text-base font-extrabold">{{ get_setting('home', 'videos_title') }}</h3>
        </div>

        <a href="{{ get_setting('home', 'videos_button_url') }}"
           class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2">
            {{ get_setting('home', 'videos_button_title') }}
            <span class="font-black">←</span>
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @for($i = 0; $i < 4; $i++)
                    <a href="#"
                       class="group rounded-2xl border border-slate-200 bg-slate-900 overflow-hidden shadow-sm hover:shadow-lg transition">
                        <div class="relative h-40">
                            <img class="absolute inset-0 h-full w-full object-cover opacity-90"
                                 src="https://moddingway.ir/wp-content/uploads/2022/11/%D8%AA%D8%B3%D8%AA-%D8%A7%D8%AC%D8%B1%D8%A7%DB%8C-efootball-2023-%D8%B1%D9%88%DB%8C-%DA%A9%D8%A7%D9%85%D9%BE%DB%8C%D9%88%D8%AA%D8%B1-%DB%8C%D8%A7-%D9%84%D9%BE-%D8%AA%D8%A7%D9%BE.jpg"
                                 alt="آموزش نصب پچ"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>

                            <div class="absolute top-3 right-3 flex items-center gap-2">
                          <span
                              class="inline-flex items-center gap-2 rounded-2xl bg-white/90 px-3 py-1 text-xs font-extrabold text-slate-900">
                            🎬 آموزش
                          </span>
                                <span
                                    class="inline-flex items-center gap-2 rounded-2xl bg-black/40 px-3 py-1 text-xs font-bold text-white ring-1 ring-white/15">
                            🕒 ۹ دقیقه
                          </span>
                            </div>

                            <div class="absolute bottom-3 right-3">
                          <span
                              class="inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-3 py-2 text-xs font-extrabold text-white shadow-sm group-hover:bg-brand-700 transition">
                            ▶ پخش کن
                          </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-white truncate">نصب و فعال‌سازی Patch برای PES
                                        2021
                                    </div>
                                    <div class="mt-2 text-xs text-white/70 line-clamp-2 leading-6">
                                        قدم‌به‌قدم نصب پچ + رفع خطاهای رایج و نکات قبل از اجرا.
                                    </div>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-2xl bg-white/10 px-3 py-1 text-xs font-extrabold text-white ring-1 ring-white/10">
                                Beginner
                            </span>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-xs text-white/60">آپدیت: امروز</span>
                                <span class="inline-flex items-center gap-2 text-xs font-extrabold text-brand-200">
                                مشاهده <span class="font-black">←</span>
                            </span>
                            </div>
                        </div>
                    </a>
                @endfor
            </div>

            <div
                class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="text-sm text-slate-700 leading-7">
                    <span class="font-extrabold">پیشنهاد:</span>
                    {!! get_setting('home', 'videos_description') !!}
                </div>
                <a href="#"
                   class="inline-flex items-center gap-2 rounded-2xl bg-white border border-slate-200 px-4 py-2 text-sm font-extrabold text-brand-700 hover:bg-brand-50 transition">
                    رفتن به صفحه آموزش‌ها <span class="font-black">←</span>
                </a>
            </div>
        </div>
    </div>
</section>
