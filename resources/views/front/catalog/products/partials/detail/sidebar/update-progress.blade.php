@if($product->updateProgress?->is_visible)
    @php
        $update = $product->updateProgress;

        $progress = max(
            0,
            min(100, (int) $update->progress_percentage)
        );

        $isPublished = $progress >= 100;

        $startedAt = $update->started_at
            ? jalali_date($update->started_at, 'Y/m/d')
            : null;

        $releaseAt = $update->release_at
            ? jalali_date($update->release_at, 'Y/m/d')
            : null;
    @endphp

    <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/90"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="text-sm font-extrabold text-slate-900 dark:text-white">
                    {{ $update->display_title }}
                </div>

                <div class="mt-1 whitespace-pre-line text-xs leading-6 text-slate-500 dark:text-slate-400">
                    {{ $update->display_description }}
                </div>
            </div>

            <span
            @class([
                'w-fit rounded-xl border px-2.5 py-1 text-[11px] font-extrabold',

                'border-emerald-200 bg-emerald-50 text-emerald-700' => $isPublished,
                'dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' => $isPublished,

                'border-brand-100 bg-brand-50 text-brand-800' => !$isPublished,
                'dark:border-brand-500/20 dark:bg-brand-950/40 dark:text-brand-300' => !$isPublished,
            ])
        >
            {{ $isPublished ? 'منتشر شد' : 'در حال آماده‌سازی' }}
        </span>
        </div>

        <div class="mt-5">
            <div class="grid gap-2 text-xs text-slate-500 dark:text-slate-400 sm:grid-cols-2">
                <div class="flex items-center justify-between gap-1.5 sm:justify-start">
                    @if($startedAt)
                        <span>شروع توسعه:</span>

                        <span class="font-bold text-slate-700 dark:text-slate-200">
                            {{ $startedAt }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-1.5 sm:justify-end">
                    @if($releaseAt)
                        <span>تاریخ انتشار:</span>

                        <span class="font-bold text-slate-700 dark:text-slate-200">
                            {{ $releaseAt }}
                        </span>
                    @endif
                </div>
            </div>

            <div
                    class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                    role="progressbar"
                    aria-label="{{ $update->display_title }}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $progress }}"
            >
                <div
                        @class([
                            'h-full rounded-full transition-all duration-500',
                            'bg-emerald-500 dark:bg-emerald-400' => $isPublished,
                            'bg-brand-500 dark:bg-brand-400' => !$isPublished,
                        ])
                        style="width: {{ $progress }}%"
                ></div>
            </div>

            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                <span class="font-extrabold text-slate-800 dark:text-slate-100">
                    {{ $progress }}٪
                </span>

                    تکمیل شده
                </div>

                <div
                        @class([
                            'text-xs font-extrabold',
                            'text-emerald-700 dark:text-emerald-300' => $isPublished,
                            'text-brand-700 dark:text-brand-300' => !$isPublished,
                        ])
                >
                    {{ $isPublished
                        ? 'آپدیت منتشر شد'
                        : 'آپدیت در مسیر انتشار'
                    }}
                </div>
            </div>
        </div>
    </section>
@endif
