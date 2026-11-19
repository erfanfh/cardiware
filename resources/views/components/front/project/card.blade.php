@props(['project', 'animated' => false])

@php
    $progress = max(0, min(100, (int) ($project->progress_percent ?? 0)));
    $isDone = $progress >= 100;

    // رنگ پیوسته: ۰٪ قرمز → ۵۰٪ نارنجی/زرد → ۱۰۰٪ سبز
    $hue = (int) round($progress * 1.42); // 0 → 0deg (قرمز)، 100 → 142deg (سبز)
    $hueEnd = min(160, $hue + 16);
    $barGradient = "linear-gradient(to left, hsl({$hueEnd} 82% 46%), hsl({$hue} 84% 52%))";
    $barGlow = "hsl({$hue} 84% 52% / .45)";
    $tipColor = "hsl({$hue} 80% 45%)";

    if ($isDone) {
        $barGradient = 'linear-gradient(to left, hsl(160 80% 38%), hsl(152 78% 45%))';
        $barGlow = 'hsl(152 78% 45% / .5)';
        $tipColor = 'hsl(152 78% 40%)';
    }

    $statusLabel = $isDone ? 'تکمیل شده' : 'در حال توسعه';
@endphp

<article x-data
         class="mw-prj-card {{ $isDone ? 'is-done' : '' }} {{ $animated ? '' : 'is-static' }}">
    @if($animated)
        <span class="mw-prj-sheen" aria-hidden="true"></span>
    @endif

    <div class="relative z-10 flex h-full flex-col">
        <div class="flex items-start justify-between gap-3">
            <div
                class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20 [&_svg]:h-5 [&_svg]:w-5">
                @include('icons.folder-library')
            </div>

            <span class="mw-prj-status {{ $isDone ? 'is-done' : '' }}">
                <span class="mw-prj-status-dot"></span>
                {{ $statusLabel }}
            </span>
        </div>

        <a href="{{ front_route('projects.detail', $project) }}" class="mt-4 block">
            <h3 class="text-base font-black leading-7 {{ $animated ? 'transition' : '' }} group-hover:text-brand-600 dark:text-slate-100 dark:group-hover:text-brand-300">
                {{ $project->displayTitle() }}
            </h3>
        </a>

        @if($project->excerpt())
            <p class="mt-2 line-clamp-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                {{ $project->excerpt() }}
            </p>
        @endif

        {{-- نوار پیشرفت با رنگ پیوسته قرمز→سبز --}}
        <div class="mt-auto pt-5">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">پیشرفت پروژه</span>
                <b class="text-xs font-black" style="color: {{ $isDone ? 'rgb(16 185 129)' : $tipColor }};">
                    {{ $progress }}٪
                </b>
            </div>

            <div class="mw-prj-bar">
                <div class="mw-prj-fill {{ $isDone ? 'is-done' : '' }} {{ $animated ? 'mw-fill-anim' : '' }}"
                     @if($animated) data-fill-target="{{ $progress }}%" @endif
                     style="width: {{ $animated ? '0' : $progress . '%' }}; background: {{ $barGradient }}; box-shadow: 0 0 12px {{ $barGlow }};"></div>

                @if($progress > 3 && $progress < 97)
                    <span class="mw-prj-tip {{ $animated ? 'mw-fill-tip' : '' }}"
                          @if($animated) data-fill-target="{{ $progress }}%" @endif
                          style="right: calc({{ $animated ? 0 : $progress }}% - {{ $animated ? 9 : 11 }}px); color: {{ $tipColor }}; border-color: {{ $tipColor }}; box-shadow: 0 0 0 4px {{ $barGlow }}, 0 0 14px {{ $barGlow }};"
                          aria-hidden="true">
                        @include('icons.arrow-left-02')
                    </span>
                @endif

                @if($isDone)
                    <span class="mw-prj-flag" aria-hidden="true">
                        @include('icons.target-01')
                    </span>
                @endif
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
            <a href="{{ front_route('projects.detail', $project) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-xs font-extrabold text-white shadow-md shadow-brand-500/20 {{ $animated ? 'transition' : '' }} hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-400">
                اطلاعات پروژه
                <span class="[&_svg]:h-3.5 [&_svg]:w-3.5">
                    @include('icons.arrow-left-02')
                </span>
            </a>

            @if($project->last_updated_at)
                <span class="text-[10.5px] font-semibold text-slate-400 dark:text-slate-500">
                    بروزرسانی: {{ time_ago($project->last_updated_at) }}
                </span>
            @endif
        </div>
    </div>
</article>
