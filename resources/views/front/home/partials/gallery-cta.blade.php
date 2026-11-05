@php
    $ctaImagePoints = (int) get_setting('gallery', 'image_points', 80);
    $ctaVideoPoints = (int) get_setting('gallery', 'video_points', 200);
    $ctaSubmittedCount = \App\Domain\Content\Models\UserMediaSubmission::query()->approved()->count();
@endphp

<section class="mw-gallery-cta">
    <a href="{{ route('gallery.index') }}" class="mw-gallery-cta-link group">
        <span class="mw-gallery-cta-icon">
            @include('icons.medal-06')
        </span>

        <span class="min-w-0 flex-1">
            <span class="block text-sm font-black text-slate-900 transition group-hover:text-brand-700 sm:text-base dark:text-slate-100 dark:group-hover:text-brand-300">
                گیم‌پلی یا اسکرین‌شات داری؟ بفرستش، امتیاز بگیر!
            </span>
            <span class="mt-0.5 block text-[11px] leading-6 text-slate-500 sm:text-xs dark:text-slate-400">
                تصویر تأییدشده <b class="font-black text-amber-600 dark:text-amber-400">+{{ number_format($ctaImagePoints) }} امتیاز</b>
                · ویدیوی تأییدشده <b class="font-black text-amber-600 dark:text-amber-400">+{{ number_format($ctaVideoPoints) }} امتیاز</b>
                @if($ctaSubmittedCount > 0)
                    · {{ number_format($ctaSubmittedCount) }} رسانه توسط کاربران
                @endif
            </span>
        </span>

        <span class="inline-flex h-11 shrink-0 items-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-lg shadow-brand-500/25 transition group-hover:bg-brand-600 dark:shadow-black/20 dark:group-hover:bg-brand-400">
            ارسال و گرفتن امتیاز
            <span class="[&_svg]:h-4 [&_svg]:w-4">
                @include('icons.arrow-left-02')
            </span>
        </span>
    </a>
</section>
