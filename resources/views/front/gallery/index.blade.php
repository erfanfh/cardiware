@extends('front.master')

@section('head')
    <title>{{ get_setting('gallery', 'page_title', 'رسانه‌های ارسالی کاربران') }} | {{ get_setting('general', 'title') }}</title>
@endsection

@php
    $galleryImagePoints = (int) get_setting('gallery', 'image_points', 80);
    $galleryVideoPoints = (int) get_setting('gallery', 'video_points', 200);
    $gallerySecondPercent = (int) get_setting('gallery', 'second_points_percent', 50);
    $galleryRewardedLimit = (int) get_setting('gallery', 'monthly_rewarded_limit', 3);
@endphp

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-6 sm:py-8">
        {{-- سربرگ --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
            <div class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] dark:bg-brand-500/10"></div>
            <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

            <x-front.breadcrumbs
                nav-class="flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-500 sm:text-xs dark:text-slate-400"
                link-class="transition hover:text-brand-600 dark:hover:text-brand-400"
                current-class="text-slate-800 dark:text-slate-200"
            />

            <div class="relative mt-5">
                <span class="inline-flex items-center gap-2 rounded-2xl bg-brand-50 border border-brand-100 px-3 py-1.5 text-[11px] font-extrabold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-3.5 [&_svg]:w-3.5">
                    @include('icons.image-03')
                    از جامعه Cardiware
                </span>

                <h1 class="mt-3 text-2xl font-black leading-relaxed text-slate-900 sm:text-3xl dark:text-white">
                    {{ get_setting('gallery', 'page_title', 'رسانه‌های ارسالی کاربران') }}
                </h1>

                @if(filled(get_setting('gallery', 'page_subtitle')))
                    <p class="mt-3 max-w-2xl text-sm leading-8 text-slate-600 dark:text-slate-300">
                        {{ get_setting('gallery', 'page_subtitle') }}
                    </p>
                @endif
            </div>
        </section>

        {{-- بنر امتیاز (برجسته) --}}
        <section class="mt-4 relative overflow-hidden rounded-3xl border border-amber-200 bg-gradient-to-l from-amber-50 via-orange-50 to-white p-5 sm:p-6 dark:border-amber-500/25 dark:from-amber-500/10 dark:via-orange-500/10 dark:to-slate-900">
            <div class="pointer-events-none absolute -top-16 -left-16 h-48 w-48 rounded-full bg-amber-300/25 blur-3xl dark:bg-amber-500/15"></div>

            <div class="relative flex flex-col items-center gap-4 text-center sm:flex-row sm:text-right">
                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-amber-300 to-orange-500 text-white shadow-lg shadow-amber-500/30 [&_svg]:h-7 [&_svg]:w-7">
                    @include('icons.medal-06')
                </span>

                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-black text-amber-700 sm:text-lg dark:text-amber-300">
                        با هر ارسال، امتیاز باشگاه مشتریان بگیر!
                    </h2>
                    <p class="mt-1 text-xs leading-7 text-amber-700/80 sm:text-sm dark:text-amber-200/70">
                        اولین تصویر تأییدشده <b class="font-black">{{ number_format($galleryImagePoints) }} امتیاز</b>
                        و اولین ویدیوی تأییدشده <b class="font-black text-orange-600 dark:text-orange-400">{{ number_format($galleryVideoPoints) }} امتیاز</b>؛
                        ارسال دوم {{ number_format($gallerySecondPercent) }}٪ و بعدی‌ها کمتر
                        @if($galleryRewardedLimit > 0)
                            (حداکثر {{ number_format($galleryRewardedLimit) }} رسانه امتیازدار در ماه)
                        @endif
                        — امتیازها بلافاصله بعد از تأیید مدیر به حساب شما اضافه می‌شوند.
                    </p>
                </div>

                <a href="{{ route('loyalty.leaderboard') }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-gradient-to-l from-amber-400 to-orange-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/30 transition hover:brightness-110">
                    جدول امتیازات
                    <span class="[&_svg]:h-4 [&_svg]:w-4">
                        @include('icons.arrow-left-02')
                    </span>
                </a>
            </div>
        </section>

        @if(session('success'))
            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- باکس افقی ارسال --}}
        @if((bool) get_setting('gallery', 'submit_enabled', 1))
            <section class="mt-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                <h2 class="flex items-center gap-2 text-sm font-black dark:text-slate-100 [&_svg]:h-4 [&_svg]:w-4 [&_svg]:text-brand-600 dark:[&_svg]:text-brand-400">
                    @include('icons.cloud-download')
                    ارسال تصویر یا ویدیو
                </h2>

                <p class="mt-2 text-[11px] leading-6 text-slate-500 dark:text-slate-400">
                    فایل را مستقیم آپلود نکنید؛ عکس یا ویدیوی خود را در سایت‌های آپلود (مانند <b class="font-bold">پیکوفایل</b>، آپارات و…) قرار دهید و
                    <b class="font-bold">لینکش</b> را اینجا بفرستید.
                </p>

                @auth
                    <form action="{{ route('gallery.store') }}" method="post" class="mt-4">
                        @csrf

                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                            <div class="grid grid-cols-2 gap-2 lg:col-span-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="image" class="peer sr-only" checked
                                           onchange="document.getElementById('g-img').hidden=false;document.getElementById('g-vid').hidden=true;">
                                    <span class="flex h-11 items-center justify-center rounded-2xl border border-slate-200 text-xs font-extrabold text-slate-600 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 dark:border-slate-700 dark:text-slate-300 dark:peer-checked:border-brand-500 dark:peer-checked:bg-brand-500/10 dark:peer-checked:text-brand-300">
                                        تصویر
                                    </span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="video" class="peer sr-only"
                                           onchange="document.getElementById('g-img').hidden=true;document.getElementById('g-vid').hidden=false;">
                                    <span class="flex h-11 items-center justify-center rounded-2xl border border-slate-200 text-xs font-extrabold text-slate-600 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 dark:border-slate-700 dark:text-slate-300 dark:peer-checked:border-brand-500 dark:peer-checked:bg-brand-500/10 dark:peer-checked:text-brand-300">
                                        ویدیو
                                    </span>
                                </label>
                            </div>

                            <label class="lg:col-span-3">
                                <input type="text" name="title" value="{{ old('title') }}" required maxlength="120" placeholder="عنوان (مثلا: گیم‌پلی دربی)"
                                       class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:focus:bg-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10">
                            </label>

                            <label class="lg:col-span-4" id="g-img">
                                <input type="url" name="image_url" value="{{ old('image_url') }}" dir="ltr" placeholder="https://picofile.com/..." required
                                       class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:focus:bg-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10">
                            </label>

                            <label class="lg:col-span-4" id="g-vid" hidden>
                                <input type="url" name="video_url" value="{{ old('video_url') }}" dir="ltr" placeholder="https://www.aparat.com/v/..."
                                       class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:focus:bg-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10">
                            </label>

                            <label class="lg:col-span-3">
                                <input type="text" name="description" value="{{ old('description') }}" maxlength="500" placeholder="توضیح (اختیاری)"
                                       class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:focus:bg-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10">
                            </label>

                            <label class="lg:col-span-5">
                                <input type="text" name="order_number" value="{{ old('order_number') }}" maxlength="64" placeholder="شماره سفارش (اختیاری؛ برای رسانه‌ی محصول خریداری‌شده)"
                                       class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:focus:bg-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10">
                            </label>

                            <label class="lg:col-span-5">
                                <input type="tel" name="purchaser_mobile" value="{{ old('purchaser_mobile') }}" maxlength="20" dir="auto" inputmode="numeric" placeholder="موبایل خریدار، مثل ۰۹۱۲۳۴۵۶۷۸۹ (اختیاری)"
                                       class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:focus:bg-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10">
                            </label>

                            <div class="lg:col-span-2">
                                <button type="submit"
                                        class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white shadow-lg transition hover:bg-brand-600 dark:shadow-black/20 dark:hover:bg-brand-400">
                                    ارسال
                                </button>
                            </div>
                        </div>

                        @if($errors->any())
                            <div class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </form>
                @else
                    <div class="mt-4 flex flex-col items-center justify-between gap-3 rounded-2xl border border-dashed border-slate-200 px-4 py-4 sm:flex-row dark:border-slate-700">
                        <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">
                            برای ارسال و گرفتن امتیاز، ابتدا وارد حساب کاربری شوید.
                        </p>
                        <a href="{{ front_route('auth.index') }}"
                           class="inline-flex h-10 shrink-0 items-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-lg transition hover:bg-brand-600 dark:shadow-black/20 dark:hover:bg-brand-400">
                            ورود / ثبت‌نام
                        </a>
                    </div>
                @endauth
            </section>
        @endif

        {{-- گالری --}}
        <section class="mt-6">
            @if($submissions->isNotEmpty())
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($submissions as $submission)
                        <figure class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-500/40 hover:shadow-xl dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/40">
                            @if($submission->type->value === 'image')
                                <div class="relative aspect-video overflow-hidden bg-slate-100 dark:bg-slate-800">
                                    <img src="{{ $submission->image_url ?: asset($submission->image_path ?: '') }}"
                                         alt="{{ $submission->title }}"
                                         loading="lazy"
                                         class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                </div>
                            @else
                                <div class="aspect-video bg-slate-950">
                                    @if($submission->videoEmbedUrl())
                                        <iframe src="{{ $submission->videoEmbedUrl() }}"
                                                title="{{ $submission->title }}"
                                                class="h-full w-full"
                                                frameborder="0"
                                                allowfullscreen
                                                loading="lazy"></iframe>
                                    @elseif($submission->mediaUrl())
                                        <video controls preload="none" poster="{{ $submission->videoThumbnailUrl() }}"
                                               aria-label="{{ $submission->title }}" class="h-full w-full object-contain">
                                            <source src="{{ $submission->mediaUrl() }}">
                                            <a href="{{ $submission->mediaUrl() }}">مشاهده ویدیو</a>
                                        </video>
                                    @endif
                                </div>
                            @endif

                            <figcaption class="p-4">
                                <div class="text-sm font-extrabold leading-7 text-slate-900 dark:text-slate-100">
                                    {{ $submission->title }}
                                </div>

                                @if(filled($submission->description))
                                    <p class="mt-1.5 line-clamp-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        {{ $submission->description }}
                                    </p>
                                @endif

                                <div class="mt-3 flex items-center gap-2.5 border-t border-slate-100 pt-3 dark:border-slate-800">
                                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-[11px] font-black text-white">
                                        {{ mb_substr($submission->submitterName(), 0, 1) }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="truncate text-xs font-bold text-slate-800 dark:text-slate-200">
                                            {{ $submission->submitterName() }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ time_ago($submission->approved_at ?? $submission->created_at) }}
                                        </div>
                                    </div>

                                    @if($submission->points_awarded)
                                        <span class="mr-auto inline-flex shrink-0 items-center gap-1 rounded-xl border border-amber-200 bg-amber-50 px-2 py-1 text-[10.5px] font-black text-amber-700 dark:border-amber-500/25 dark:bg-amber-500/10 dark:text-amber-300 [&_svg]:h-3 [&_svg]:w-3">
                                            @include('icons.medal-06')
                                            +{{ number_format($submission->points_awarded) }} امتیاز
                                        </span>
                                    @endif
                                </div>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                @if($submissions->hasPages())
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <x-front.list.pagination :model="$submissions"/>
                    </div>
                @endif
            @else
                <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:p-14">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 [&_svg]:h-7 [&_svg]:w-7">
                        @include('icons.image-03')
                    </div>
                    <h3 class="mt-4 text-sm font-black text-slate-800 dark:text-slate-200">هنوز رسانه‌ای منتشر نشده است</h3>
                    <p class="mx-auto mt-2 max-w-md text-xs leading-6 text-slate-500 dark:text-slate-400">
                        اولین نفری باشید که گیم‌پلی یا اسکرین‌شات خودش را بفرستد و امتیاز بگیرد.
                    </p>
                </div>
            @endif
        </section>

        {{-- CTA پایانی: باشگاه مشتریان --}}
        <section class="mt-6 relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-l from-brand-50 via-white to-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:from-brand-500/10 dark:via-slate-900 dark:to-slate-900">
            <div class="pointer-events-none absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-brand-200/40 blur-3xl dark:bg-brand-500/15"></div>

            <div class="relative flex flex-col items-center gap-4 text-center sm:flex-row sm:text-right">
                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/30 [&_svg]:h-7 [&_svg]:w-7">
                    @include('icons.medal-06')
                </span>

                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-white">
                        امتیازهایت را در باشگاه مشتریان دنبال کن
                    </h2>
                    <p class="mt-1 text-xs leading-7 text-slate-600 sm:text-sm dark:text-slate-300">
                        با هر ارسال، خرید و فعالیت امتیاز بگیر؛ سطح برو بالا و در قرعه‌کشی‌ها و تخفیف‌های فروشگاه از امتیاز استفاده کن.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap justify-center gap-2">
                    <a href="{{ route('loyalty.leaderboard') }}"
                       class="inline-flex h-11 items-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-lg transition hover:bg-brand-600 dark:shadow-black/20 dark:hover:bg-brand-400">
                        جدول امتیازات
                        <span class="[&_svg]:h-4 [&_svg]:w-4">
                            @include('icons.arrow-left-02')
                        </span>
                    </a>

                    @auth
                        <a href="{{ route('account.loyalty') }}"
                           class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 px-5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            باشگاه من
                        </a>
                    @endauth
                </div>
            </div>
        </section>
    </main>
@endsection
