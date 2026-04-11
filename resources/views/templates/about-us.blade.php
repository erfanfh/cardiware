{{--
Template Name: صفحه درباره ما
--}}

@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        <section class="relative overflow-hidden rounded-xl2 border border-slate-200 bg-slate-900 shadow-xl">
            <div
                class="absolute inset-0 opacity-90 bg-[radial-gradient(700px_circle_at_15%_20%,rgba(249,115,22,.35),transparent_55%),radial-gradient(700px_circle_at_80%_60%,rgba(34,197,94,.18),transparent_60%),radial-gradient(700px_circle_at_55%_10%,rgba(59,130,246,.15),transparent_60%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/70 via-slate-950/40 to-slate-950/10"></div>

            <div class="relative p-6 sm:p-10">
                <nav class="text-xs text-white/70 flex flex-wrap gap-2">
                    <a href="/" class="hover:text-brand-300 font-bold transition">خانه</a>
                    <span>—</span>
                    <span class="text-white/90 font-semibold">درباره ما</span>
                </nav>

                <div class="mt-4 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                    <div class="max-w-2xl">
                        <div
                            class="inline-flex items-center gap-2 rounded-2xl bg-white/10 px-3 py-2 text-white/90 border border-white/10 backdrop-blur">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-400"></span>
                            {{ get_setting('aboutUs', 'hero_badge') }}
                        </div>

                        <h1 class="mt-4 text-2xl sm:text-3xl font-black leading-tight text-white">
                            {{ get_setting('aboutUs', 'hero_title') }}
                        </h1>

                        <div class="mt-4 text-white/80 text-sm sm:text-base leading-7">
                            {!! get_setting('aboutUs', 'hero_description')  !!}
                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <a href="#projects"
                               class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                                پروژه‌های ما <span class="font-black">←</span>
                            </a>
                            <a href="#contact"
                               class="inline-flex items-center gap-2 rounded-2xl bg-white/10 text-white border border-white/10 backdrop-blur px-5 py-3 text-sm font-extrabold hover:bg-white/15 transition">
                                ارتباط با ما
                                @include('icons.bubble-chat')
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 w-full lg:w-[380px]">
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">پروژه‌های شروع شده</div>
                            <div
                                class="mt-2 text-2xl font-black text-white">{{ \App\Domain\Project\Models\Project::whereBetween('progress_percent', [1,99])->count() }}</div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">پروژه‌های منتشرشده</div>
                            <div
                                class="mt-2 text-2xl font-black text-white">{{ \App\Domain\Project\Models\Project::where('progress_percent', 100)->count() . ' از ' .  \App\Domain\Project\Models\Project::all()->count() }}</div>
                            <div class="mt-2 h-2 rounded-full bg-white/15 overflow-hidden">
                                <div class="h-full bg-brand-400"
                                     style="width: {{ (\App\Domain\Project\Models\Project::where('progress_percent', 100)->count() / \App\Domain\Project\Models\Project::all()->count()) * 100 . '%' }}"></div>
                            </div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">کاربران فعال</div>
                            <div
                                class="mt-2 text-2xl font-black text-white">{{ number_format(\App\Domain\Identity\Models\User::all()->count()) }}</div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">پشتیبانی</div>
                            <div class="mt-2 text-2xl font-black text-white">24/7</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div
                class="lg:col-span-7 rounded-xl2 border border-slate-200 bg-white p-5 sm:p-6 shadow-sm relative overflow-hidden">
                <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                      @include('icons.award-03')
                    </span>
                    <h2 class="text-base font-extrabold">{{ get_setting('aboutUs', 'our_mission_box_title') }}</h2>
                </div>

                <div class="mt-4 text-sm leading-7 text-slate-700">
                    {!! get_setting('aboutUs', 'our_mission_description') !!}
                </div>
            </div>

            <div class="lg:col-span-5 space-y-6">
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                            @include('icons.legal-hammer')
                        </span>
                        <h2 class="text-base font-extrabold">{{ get_setting('aboutUs', 'what_we_build_box_title') }}</h2>
                    </div>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach(get_setting('aboutUs', 'what_we_build_items') as $item)
                            <div class="rounded-2xl border border-slate-200 p-4 hover:bg-slate-50 transition">
                                <div class="text-sm font-extrabold">{{ $item['title'] }}</div>
                                <div class="mt-1 text-xs text-slate-500 leading-6">{{ $item['subtitle'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="projects" class="mt-10">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                        @include('icons.dashboard-square-02')
                    </span>
                    <div>
                        <h2 class="text-base font-extrabold">{{ get_setting('aboutUs', 'projects_box_title') }}</h2>
                    </div>
                </div>
                <a href="{{ route('projects.list') }}"
                   class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2">
                    مشاهده همه <span class="font-black">←</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach(\App\Domain\Project\Models\Project::orderBy('created_at')->take(3)->get() as $project)
                    <div
                        class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm hover:shadow-lg transition relative overflow-hidden">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-extrabold truncate">{{ $project->title }}</div>
                                <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2">
                                    {!! $project->body !!}
                                </div>
                            </div>
                            <span
                                class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">{{ $project->progress_percent . '%' }}</span>
                        </div>
                        <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-brand-500 w-[62%]"></div>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ 'آخرین آپدیت: ' . jalali_date($project->last_updated_at, 'Y/m/d') }}</span>
                            <a class="font-extrabold text-brand-700 hover:text-brand-800 transition"
                               href="{{ route('projects.detail', $project) }}">جزئیات</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-10">
            <div class="flex items-center gap-3 mb-4">
                <span
                    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                    @include('icons.user-multiple')
                </span>
                <div>
                    <h2 class="text-base font-extrabold">{{ get_setting('aboutUs', 'our_team_box_title') }}</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm hover:shadow-lg transition">
                    <div class="flex items-center gap-3">
                        <img class="h-12 w-12 rounded-2xl object-cover border border-slate-200"
                             src="https://i.pravatar.cc/120?img=12" alt="">
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold truncate">Mod Lead</div>
                            <div class="text-xs text-slate-500">مدیریت پروژه و انتشار</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-700 leading-7">
                        مسئول برنامه‌ریزی نسخه‌ها، چنج‌لاگ و هماهنگی انتشار پایدار.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">Release</span>
                        <span
                            class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">QA</span>
                    </div>
                </div>

                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm hover:shadow-lg transition">
                    <div class="flex items-center gap-3">
                        <img class="h-12 w-12 rounded-2xl object-cover border border-slate-200"
                             src="https://i.pravatar.cc/120?img=32" alt="">
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold truncate">Patch Engineer</div>
                            <div class="text-xs text-slate-500">توسعه پچ‌ها و رفع باگ</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-700 leading-7">
                        تمرکز روی اصلاح باگ‌های رایج و بهبود سازگاری نسخه‌ها با سیستم‌های مختلف.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">Fix</span>
                        <span
                            class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">Performance</span>
                    </div>
                </div>

                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm hover:shadow-lg transition">
                    <div class="flex items-center gap-3">
                        <img class="h-12 w-12 rounded-2xl object-cover border border-slate-200"
                             src="https://i.pravatar.cc/120?img=47" alt="">
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold truncate">Support</div>
                            <div class="text-xs text-slate-500">پشتیبانی و مدیریت جامعه</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-700 leading-7">
                        پاسخ‌گویی به دیدگاه‌ها، جمع‌آوری گزارش‌ها و پیگیری مشکلات تا انتشار نسخه اصلاحی.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">Community</span>
                        <span
                            class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">Support</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-10" x-data="{ open: 1 }">
            <div class="flex items-center gap-3 mb-4">
                <span
                    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                    @include('icons.help-circle')
                </span>
                <div>
                    <h2 class="text-base font-extrabold">{{ get_setting('aboutUs', 'faqs_box_title') }}</h2>
                </div>
            </div>

            <div class="rounded-xl2 border border-slate-200 bg-white shadow-sm overflow-hidden">
                @foreach(get_setting('aboutUs', 'faqs_items') as $item)
                    <button @click="open = open === {{ $item['sort_order'] }} ? 0 : {{ $item['sort_order'] }}"
                            class="w-full text-right p-5 sm:p-6 flex items-center justify-between gap-3 hover:bg-slate-50 transition">
                        <div class="font-extrabold">{{ $item['title'] }}</div>
                        <span class="h-10 w-10 rounded-2xl border border-slate-200 bg-white grid place-items-center">
                          <span x-text="open==={{ $item['sort_order'] }} ? '−' : '+'"></span>
                        </span>
                    </button>
                    <div x-show="open==={{ $item['sort_order'] }}" x-transition
                         class="px-5 sm:px-6 pb-5 sm:pb-6 text-sm text-slate-700 leading-7">
                        {{ $item['answer'] }}
                    </div>

                    @if(!$loop->last)
                        <div class="border-t border-slate-200"></div>
                    @endif
                @endforeach
            </div>
        </section>

        <section id="contact" class="mt-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-7 rounded-xl2 border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                            @include('icons.telephone')
                        </span>
                        <div>
                            <h2 class="text-base font-extrabold">{{ get_setting('aboutUs', 'contact_us_box_title') }}</h2>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none
                         focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                               placeholder="نام شما"/>
                        <input type="email" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none
                         focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                               placeholder="ایمیل"/>
                    </div>
                    <textarea rows="4" class="mt-3 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none
                         focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                              placeholder="پیام شما…"></textarea>
                    <div class="mt-3 flex items-center justify-end">
                        <button
                            class="rounded-2xl bg-brand-500 px-6 py-3 text-sm font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                            ارسال پیام
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-5 space-y-6">
                    <div class="rounded-xl2 border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                        <div class="text-sm font-extrabold">{{ get_setting('aboutUs', 'contact_box_title') }}</div>
                        <div class="mt-4 space-y-3 text-sm text-slate-700">
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded bg-brand-600"></span>
                                <span>تلگرام: <span
                                        class="font-extrabold text-brand-700">{{ get_setting('aboutUs', 'contact_telegram') }}</span></span>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded bg-brand-600"></span>
                                <span>اینستاگرام: <span
                                        class="font-extrabold text-brand-700">{{ get_setting('aboutUs', 'contact_instagram') }}</span></span>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded bg-brand-600"></span>
                                <span>ایمیل: <span
                                        class="font-extrabold text-brand-700">{{ get_setting('aboutUs', 'contact_email') }}</span></span>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded bg-brand-600"></span>
                                <span>شماره تماس: <span
                                        class="font-extrabold text-brand-700">{{ get_setting('aboutUs', 'contact_phone') }}</span></span>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded bg-brand-600"></span>
                                <span>ساعات پاسخ‌گویی: <span
                                        class="font-extrabold text-brand-700">{{ get_setting('aboutUs', 'contact_hour') }}</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl2 border border-dashed border-slate-300 bg-white p-5 shadow-sm">
                        <div class="font-extrabold text-slate-700">تبلیغات / بنر</div>
                        <div class="mt-2 text-sm text-slate-500">جایگاه پیشنهادی برای بنر یا اطلاعیه</div>
                        <div class="mt-3 text-xs text-slate-500">size: 320×250</div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
