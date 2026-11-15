@php
    $projectsBlock = app(\App\Application\System\Actions\Home\GetHomeProjectsBlockAction::class)->execute();
@endphp

<section class="mw-home-projects">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                @include('icons.folder-library')
            </span>

            <h3 class="text-base font-extrabold dark:text-slate-100">
                {{ $projectsBlock['title'] }}
            </h3>
        </div>

        <a href="{{ front_route('projects.list') }}"
           class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2 dark:text-brand-300 dark:hover:text-brand-200">
            مشاهده همه
            <span class="[&_svg]:w-4 [&_svg]:h-4">
                @include('icons.arrow-left-02')
            </span>
        </a>
    </div>

    {{--
        سه کارت در ردیف؛ کارت‌های بعدی با اسکرول افقی (بدون نوار اسکرول دیده‌شده).
        اسکرول با درگ ماوس/لمس هم کار می‌کند؛ برای دسترسی، فوکوس با کیبورد هم
        کارت‌ها را به دید می‌آورد.
    --}}
    <div class="mw-projects-rail" dir="rtl" tabindex="0" aria-label="پروژه‌های مودینگ‌وی">
        @foreach($projectsBlock['items'] as $project)
            <div class="mw-projects-rail-item">
                <x-front.project.card :project="$project" :animated="false"/>
            </div>
        @endforeach
    </div>
</section>
