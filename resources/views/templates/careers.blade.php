{{--
Template Name: صفحه استخدام و همکاری
--}}

@extends('front.master')

@php
    $whyItems = collect($careers['why_items'])->filter(fn ($item) => filled($item['title'] ?? null));
    $benefitItems = collect($careers['benefits_items'])->filter(fn ($item) => filled($item['title'] ?? null));
    $benefitIconSvgs = app(\App\Application\Icon\Actions\ResolveIconSvgAction::class)
        ->executeMany($benefitItems->pluck('icon')->filter()->all());
    $processItems = collect($careers['process_items'])->filter(fn ($item) => filled($item['title'] ?? null));
    $skillItems = collect($careers['skills_items'])->filter(fn ($item) => filled($item['title'] ?? null));
    $tipItems = collect($careers['tips_items'])->filter(fn ($item) => filled($item['title'] ?? null));
    $reviewItems = collect($careers['review_items'])->filter(fn ($item) => filled($item['title'] ?? null));
    $faqItems = collect($careers['faq_items'])->filter(fn ($item) => filled($item['title'] ?? null) && filled($item['answer'] ?? null));
    $inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900 dark:focus:ring-brand-500/15';
@endphp

@section('content')
    <main data-career-page class="mx-auto max-w-7xl px-4 py-6 sm:py-8">
        <section class="relative overflow-hidden rounded-[1.75rem] bg-slate-950 text-white shadow-xl" aria-labelledby="career-title">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,146,60,.23),transparent_36%),radial-gradient(circle_at_bottom_left,rgba(249,115,22,.16),transparent_32%)]"></div>
            <div class="relative grid gap-8 p-6 sm:p-8 lg:grid-cols-12 lg:p-10">
                <div class="lg:col-span-7">
                    @if(filled($careers['hero_badge']))<span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-2 text-xs font-extrabold text-white/90"><span class="h-2 w-2 rounded-full bg-brand-400"></span>{{ $careers['hero_badge'] }}</span>@endif
                    <h1 id="career-title" class="mt-4 text-2xl font-black leading-relaxed sm:text-3xl lg:text-4xl">{{ $careers['hero_title'] }}</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-8 text-white/75 sm:text-base">{{ $careers['hero_description'] }}</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#jobs" data-career-scroll class="cursor-pointer rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-brand-600">{{ $careers['hero_primary_button'] }} <span aria-hidden="true">←</span></a>
                        <a href="#apply" data-career-scroll class="cursor-pointer rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-white/15">{{ $careers['hero_secondary_button'] }}</a>
                    </div>
                    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-xs font-bold text-white/60">{{ $careers['stats_positions_title'] }}</div><div class="mt-2 text-2xl font-black">{{ number_format($positions->count()) }}</div></div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-xs font-bold text-white/60">{{ $careers['stats_teams_title'] }}</div><div class="mt-2 text-2xl font-black">{{ number_format($teamCount) }}</div></div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-xs font-bold text-white/60">{{ $careers['stats_cooperation_title'] }}</div><div class="mt-2 text-sm font-extrabold">{{ $careers['stats_cooperation_value'] }}</div></div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-xs font-bold text-white/60">{{ $careers['stats_review_title'] }}</div><div class="mt-2 text-sm font-extrabold">{{ $careers['stats_review_value'] }}</div></div>
                    </div>
                </div>
                <div class="lg:col-span-5">
                    <div class="h-full rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                        <h2 class="text-base font-extrabold">{{ $careers['why_title'] }}</h2>
                        <p class="mt-1 text-xs text-white/60">{{ $careers['why_subtitle'] }}</p>
                        <div class="mt-5 space-y-3">
                            @foreach($whyItems as $item)
                                <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><h3 class="text-sm font-extrabold">{{ $item['title'] }}</h3>@if(filled($item['description'] ?? null))<p class="mt-2 text-sm leading-7 text-white/75">{{ $item['description'] }}</p>@endif</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if($benefitItems->isNotEmpty())
            <section class="mt-9" aria-labelledby="career-benefits-title">
                <div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">✨</span><div><h2 id="career-benefits-title" class="text-base font-extrabold">{{ $careers['benefits_title'] }}</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $careers['benefits_subtitle'] }}</p></div></div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($benefitItems as $item)
                        <article class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-50 text-xl dark:bg-brand-500/10" aria-hidden="true">
                                @if($svg = $benefitIconSvgs[$item['icon'] ?? ''] ?? null)
                                    <x-icon :svg="$svg" class="h-6 w-6 text-brand-700 dark:text-brand-300"/>
                                @else
                                    {{ mb_strlen((string) ($item['icon'] ?? '')) <= 8 && filled($item['icon'] ?? null) ? $item['icon'] : '✨' }}
                                @endif
                            </span>
                            <h3 class="mt-4 text-sm font-extrabold">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item['description'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="jobs" class="mt-10 grid gap-6 lg:grid-cols-12" style="scroll-margin-top: var(--career-scroll-offset, 9rem)" aria-labelledby="career-jobs-title">
            <div class="lg:col-span-8">
                <div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">📌</span><div><h2 id="career-jobs-title" class="text-base font-extrabold">{{ $careers['jobs_title'] }}</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $careers['jobs_subtitle'] }}</p></div></div>
                <div class="mt-4 space-y-4">
                    @forelse($positions as $position)
                        <article class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex flex-col gap-5 md:flex-row md:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2"><h3 class="text-base font-extrabold">{{ $position->title }}</h3><span class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-extrabold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300">فعال</span>@if($position->employment_type)<span class="rounded-xl bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $position->employment_type }}</span>@endif @if($position->department)<span class="rounded-xl bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-800 dark:bg-brand-500/10 dark:text-brand-300">{{ $position->department }}</span>@endif</div>
                                    <p class="mt-3 text-sm leading-8 text-slate-600 dark:text-slate-300">{{ $position->summary }}</p>
                                    @if(count($position->requirements ?? []))<ul class="mt-4 grid gap-3 sm:grid-cols-2">@foreach($position->requirements as $requirement)<li class="flex items-start gap-2 text-sm leading-7 text-slate-700 dark:text-slate-200"><span class="mt-3 h-2 w-2 shrink-0 rounded bg-brand-600"></span><span>{{ $requirement }}</span></li>@endforeach</ul>@endif
                                    <a href="{{ $page->url }}?position={{ $position->id }}#apply" data-career-scroll data-career-position="{{ $position->id }}" class="mt-5 inline-flex cursor-pointer items-center gap-2 text-sm font-extrabold text-brand-700 transition-colors hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200">ارسال درخواست برای این موقعیت <span aria-hidden="true">←</span></a>
                                </div>
                                <dl class="grid shrink-0 grid-cols-2 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm md:w-48 md:grid-cols-1 dark:border-slate-700 dark:bg-slate-800">
                                    @if($position->cooperation_type)<div><dt class="text-xs text-slate-500 dark:text-slate-400">نوع همکاری</dt><dd class="mt-1 font-extrabold">{{ $position->cooperation_type }}</dd></div>@endif
                                    @if($position->location)<div><dt class="text-xs text-slate-500 dark:text-slate-400">مکان</dt><dd class="mt-1 font-extrabold">{{ $position->location }}</dd></div>@endif
                                    @if($position->level)<div><dt class="text-xs text-slate-500 dark:text-slate-400">سطح</dt><dd class="mt-1 font-extrabold">{{ $position->level }}</dd></div>@endif
                                    @if($position->detail_label && $position->detail_value)<div><dt class="text-xs text-slate-500 dark:text-slate-400">{{ $position->detail_label }}</dt><dd class="mt-1 font-extrabold">{{ $position->detail_value }}</dd></div>@endif
                                </dl>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.4rem] border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">{{ $careers['jobs_empty_text'] }}</div>
                    @endforelse
                </div>
            </div>
            <aside class="space-y-4 lg:col-span-4">
                @if($processItems->isNotEmpty())<div class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="font-extrabold">{{ $careers['process_title'] }}</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $careers['process_subtitle'] }}</p><ol class="mt-5 space-y-4">@foreach($processItems as $item)<li class="flex gap-3"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-brand-500 text-xs font-black text-white">{{ $loop->iteration }}</span><div><h3 class="text-sm font-extrabold">{{ $item['title'] }}</h3><p class="mt-1 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item['description'] ?? '' }}</p></div></li>@endforeach</ol></div>@endif
                @if($skillItems->isNotEmpty())<div class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="font-extrabold">{{ $careers['skills_title'] }}</h2><div class="mt-4 flex flex-wrap gap-2">@foreach($skillItems as $item)<span class="rounded-xl bg-brand-50 px-2 py-1 text-xs font-bold text-brand-800 dark:bg-brand-500/10 dark:text-brand-300">{{ $item['title'] }}</span>@endforeach</div></div>@endif
                <div class="rounded-[1.4rem] border border-dashed border-slate-300 bg-white p-5 dark:border-slate-700 dark:bg-slate-900"><h2 class="text-sm font-extrabold">{{ $careers['general_title'] }}</h2><p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $careers['general_description'] }}</p><a href="#apply" data-career-scroll class="mt-4 inline-flex cursor-pointer gap-2 text-sm font-extrabold text-brand-700 transition-colors hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200">{{ $careers['general_button'] }} <span aria-hidden="true">←</span></a></div>
            </aside>
        </section>

        <section id="apply" class="mt-10" style="scroll-margin-top: var(--career-scroll-offset, 9rem)" aria-labelledby="career-form-title">
            <div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">📝</span><div><h2 id="career-form-title" class="text-base font-extrabold">{{ $careers['form_title'] }}</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $careers['form_subtitle'] }}</p></div></div>
            <div class="mt-4 grid gap-6 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <form method="post" action="{{ route('careers.apply') }}" enctype="multipart/form-data" class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900">
                        @csrf
                        <input type="hidden" name="career_page_id" value="{{ $page->id }}">
                        @if($errors->any())<div role="alert" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"><p class="font-extrabold">لطفاً موارد زیر را اصلاح کنید:</p><ul class="mt-2 list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label for="career-full-name" class="text-sm font-extrabold">نام و نام خانوادگی <span class="text-rose-600">*</span></label><input id="career-full-name" name="full_name" type="text" autocomplete="name" required maxlength="255" value="{{ old('full_name') }}" class="{{ $inputClass }}"></div>
                            <div><label for="career-phone" class="text-sm font-extrabold">شماره تماس <span class="text-rose-600">*</span></label><input id="career-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required value="{{ old('phone') }}" placeholder="0912..." class="{{ $inputClass }}"></div>
                            <div><label for="career-email" class="text-sm font-extrabold">ایمیل</label><input id="career-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" class="{{ $inputClass }}"></div>
                            <div><label for="career-position" class="text-sm font-extrabold">موقعیت مورد نظر</label><select id="career-position" name="career_position_id" class="{{ $inputClass }} cursor-pointer"><option value="general" @selected(old('career_position_id', $selectedPositionId ?? 'general') === 'general')>درخواست همکاری عمومی</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected((string) old('career_position_id', $selectedPositionId) === (string) $position->id)>{{ $position->title }}</option>@endforeach</select></div>
                            <div><label for="career-type" class="text-sm font-extrabold">نوع همکاری دلخواه</label><select id="career-type" name="preferred_cooperation_type" class="{{ $inputClass }} cursor-pointer"><option value="">انتخاب کنید</option>@foreach(['تمام‌وقت', 'پاره‌وقت', 'پروژه‌ای', 'منعطف'] as $type)<option value="{{ $type }}" @selected(old('preferred_cooperation_type') === $type)>{{ $type }}</option>@endforeach</select></div>
                            <div><label for="career-portfolio" class="text-sm font-extrabold">لینک رزومه / نمونه‌کار</label><input id="career-portfolio" name="portfolio_url" type="url" value="{{ old('portfolio_url') }}" placeholder="https://..." class="{{ $inputClass }}"></div>
                        </div>
                        <div class="mt-4"><label for="career-experience" class="text-sm font-extrabold">مهارت‌ها و سابقه کوتاه <span class="text-rose-600">*</span></label><textarea id="career-experience" name="skills_and_experience" required rows="6" placeholder="تجربه، مهارت‌ها و دلیل علاقه‌مندی به همکاری را بنویسید..." class="{{ $inputClass }}">{{ old('skills_and_experience') }}</textarea></div>
                        <div class="mt-4"><label for="career-resume" class="text-sm font-extrabold">فایل رزومه</label><input id="career-resume" name="resume" type="file" accept=".pdf,.doc,.docx" class="{{ $inputClass }} cursor-pointer file:ml-4 file:cursor-pointer file:rounded-xl file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:font-bold file:text-brand-700 dark:file:bg-slate-700 dark:file:text-slate-100"><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">PDF، DOC یا DOCX، حداکثر ۲ مگابایت. فایل فقط برای مدیران مجاز قابل دریافت است.</p></div>
                        <div class="hidden" aria-hidden="true"><label for="career-website">وب‌سایت</label><input id="career-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><label class="flex cursor-pointer items-start gap-3 text-sm leading-7 text-slate-600 dark:text-slate-300"><input name="consent" type="checkbox" value="1" required @checked(old('consent')) class="mt-1.5 cursor-pointer accent-orange-500"><span>{{ $careers['form_consent_text'] }}</span></label><button type="submit" class="shrink-0 cursor-pointer rounded-2xl bg-brand-500 px-6 py-3 text-sm font-extrabold text-white transition hover:bg-brand-600">ارسال درخواست <span aria-hidden="true">←</span></button></div>
                    </form>
                </div>
                <aside class="space-y-4 lg:col-span-4">
                    @if($tipItems->isNotEmpty())<div class="rounded-[1.4rem] border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h2 class="font-extrabold">{{ $careers['tips_title'] }}</h2><ul class="mt-4 space-y-3">@foreach($tipItems as $item)<li class="flex items-start gap-2 text-sm leading-7 text-slate-700 dark:text-slate-300"><span class="mt-3 h-2 w-2 shrink-0 rounded bg-brand-600"></span>{{ $item['title'] }}</li>@endforeach</ul></div>@endif
                    @if($reviewItems->isNotEmpty())<div class="rounded-[1.4rem] border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h2 class="font-extrabold">{{ $careers['review_title'] }}</h2><p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">مراحل کلی بررسی درخواست‌ها</p><ol class="mt-4 space-y-4">@foreach($reviewItems as $item)<li class="flex items-start gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-black text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ $loop->iteration }}</span><div><div class="text-sm font-extrabold">{{ $item['title'] }}</div><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $item['description'] ?? '' }}</p></div></li>@endforeach</ol></div>@endif
                    <div class="rounded-[1.4rem] border border-brand-200 bg-brand-50 p-5 text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-white"><h2 class="font-extrabold">{{ $careers['general_cta_title'] }}</h2><p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $careers['general_cta_description'] }}</p></div>
                </aside>
            </div>
        </section>

        @if($faqItems->isNotEmpty())
            <section class="mt-10" aria-labelledby="career-faq-title">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">❓</span>
                    <div>
                        <h2 id="career-faq-title" class="text-base font-extrabold">{{ $careers['faq_title'] }}</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $careers['faq_subtitle'] }}</p>
                    </div>
                </div>
                <div class="mt-4 space-y-3">
                    @foreach($faqItems as $item)
                        <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                             class="rounded-[1.3rem] border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                            <button type="button"
                                    class="flex w-full cursor-pointer items-center justify-between gap-3 text-right text-sm font-extrabold"
                                    @click="open = !open"
                                    :aria-expanded="open.toString()"
                                    aria-controls="career-faq-answer-{{ $loop->index }}">
                                <span>{{ $item['title'] }}</span>
                                <span class="shrink-0 text-brand-600 transition-transform duration-300 dark:text-brand-400"
                                      :class="open ? 'rotate-45' : ''" aria-hidden="true">+</span>
                            </button>
                            <div id="career-faq-answer-{{ $loop->index }}" x-show="open" x-collapse.duration.300ms x-cloak>
                                <p class="pt-3 text-sm leading-8 text-slate-600 dark:text-slate-300">{{ $item['answer'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
        @if(session('career_application_success'))
            <dialog data-career-success-modal aria-labelledby="career-success-title" aria-describedby="career-success-message"
                    class="m-auto w-[calc(100%_-_2rem)] max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white p-0 text-right text-slate-900 shadow-2xl backdrop:bg-slate-950/65 backdrop:backdrop-blur-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <div class="p-6 sm:p-8">
                    <div class="mb-5 grid h-14 w-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300" aria-hidden="true">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <h2 id="career-success-title" class="text-lg font-black">درخواست شما ثبت شد</h2>
                    <p id="career-success-message" class="mt-3 text-sm leading-8 text-slate-600 dark:text-slate-300">{{ session('career_application_success') }}</p>
                    <button type="button" data-career-modal-close class="mt-7 w-full cursor-pointer rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/25">متوجه شدم</button>
                </div>
            </dialog>
        @endif
    </main>
@endsection
