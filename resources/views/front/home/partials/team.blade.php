@php
    $teamMemberIds = collect(get_setting('team', 'member_ids') ?? [])->filter()->map(fn($id) => (int)$id);
    $teamMembers = App\Domain\Organization\Models\TeamMember::query()
        ->with('picture')
        ->when($teamMemberIds->isNotEmpty(), fn($query) => $query->whereIn('id', $teamMemberIds->all()))
        ->orderByDesc('id')
        ->limit(8)
        ->get();
@endphp

@if($teamMembers->isNotEmpty())
    <section class="mw-team-slider">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                    @include('icons.user-multiple-02')
                </span>

                <h3 class="text-base font-extrabold dark:text-slate-100">تیم مودینگ‌وی</h3>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" class="mw-team-nav" data-mw-team-prev aria-label="قبلی">
                    @include('icons.arrow-right-01')
                </button>
                <button type="button" class="mw-team-nav" data-mw-team-next aria-label="بعدی">
                    @include('icons.arrow-left-02')
                </button>
            </div>
        </div>

        <div class="mw-team-track" data-mw-team-track>
            @foreach($teamMembers as $member)
                <div class="mw-team-card">
                    @if($member->picture?->path)
                        <img src="{{ $member->picture->path }}"
                             alt="{{ $member->full_name }}"
                             loading="lazy"
                             class="h-16 w-16 rounded-2xl border border-slate-200 object-cover dark:border-slate-700">
                    @else
                        <span class="grid h-16 w-16 place-items-center rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-black text-white dark:border-slate-700">
                            {{ mb_substr(trim($member->first_name ?: 'م'), 0, 1) }}
                        </span>
                    @endif

                    <div class="mt-3 text-sm font-extrabold text-slate-900 dark:text-slate-100">
                        {{ $member->full_name }}
                    </div>

                    <span class="mt-1.5 inline-flex items-center rounded-xl border border-brand-100 bg-brand-50 px-2.5 py-1 text-[10.5px] font-extrabold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                        {{ $member->job_title }}
                    </span>

                    @if($member->bio)
                        <p class="mt-2 text-[11px] leading-6 text-slate-500 dark:text-slate-400">
                            {{ Str::limit($member->bio, 70) }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif
