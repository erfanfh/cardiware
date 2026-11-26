@php
    $teamMemberIds = collect(get_setting('team', 'member_ids') ?? [])
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $teamMembers = App\Domain\Organization\Models\TeamMember::query()
        ->with(['picture.primaryVariant', 'picture.variants'])
        ->when($teamMemberIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $teamMemberIds->all()))
        ->orderByDesc('id')
        ->limit($teamMemberIds->isNotEmpty() ? 24 : 8)
        ->get();

    if ($teamMemberIds->isNotEmpty()) {
        $teamMembers = $teamMembers
            ->sortBy(fn ($member) => $teamMemberIds->search($member->getKey()))
            ->values();
    }
@endphp

@if($teamMembers->isNotEmpty())
    <section class="mw-team-slider">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                    @include('icons.user-multiple-02')
                </span>

                <h3 class="text-base font-extrabold dark:text-slate-100">Team Cardiware</h3>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" class="mw-team-nav" data-mw-team-prev aria-label="Previous">
                    @include('icons.arrow-left-01')
                </button>
                <button type="button" class="mw-team-nav" data-mw-team-next aria-label="Next">
                    <x-front.arrow-right-02/>
                </button>
            </div>
        </div>

        <div class="mw-team-track" data-mw-team-track>
            @foreach($teamMembers as $member)
                <div class="mw-team-flip relative text-center"
                     x-data="{ flipped: false, hovering: false, get showDetails() { return this.flipped || this.hovering } }"
                     @pointerenter="hovering = $event.pointerType === 'mouse'"
                     @pointerleave="hovering = false">
                    <button type="button"
                            class="absolute bottom-2 start-1/2 z-10 -translate-x-1/2 rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200 rtl:translate-x-1/2"
                            @click="flipped = !showDetails; hovering = false"
                            :aria-expanded="showDetails"
                            aria-controls="team-member-details-{{ $member->getKey() }}"
                            x-text="showDetails ? 'Back' : 'More information'">More information</button>
                    <div class="mw-team-flip-inner" :class="{ 'is-flipped': showDetails }">

                        {{-- ═══ Front: avatar + Name + Role ═══ --}}
                        <div :inert="showDetails" class="mw-team-flip-front flex flex-col items-center justify-center border border-slate-200 bg-white p-5 pb-9 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            @if($member->picture?->path)
                                <x-front.responsive-image
                                    :media="$member->picture"
                                    :alt="$member->full_name"
                                    :title="$member->full_name"
                                    variant="medium_large"
                                    class="h-20 w-20 rounded-3xl border-2 border-brand-100 object-cover dark:border-slate-600"
                                    wrapper-class="h-20 w-20 rounded-3xl overflow-hidden border-2 border-brand-100 dark:border-slate-600"
                                    sizes="80px"
                                />
                            @else
                                <span class="grid h-20 w-20 place-items-center rounded-3xl bg-gradient-to-br from-brand-500 to-brand-700 text-2xl font-black text-white shadow-lg shadow-brand-500/20">
                                    {{ mb_substr(trim($member->first_name ?: 'M'), 0, 1) }}
                                </span>
                            @endif

                            <div class="mt-4 text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                {{ $member->full_name }}
                            </div>

                            <span class="mt-2 inline-flex items-center rounded-xl border border-brand-100 bg-brand-50 px-3 py-1 text-[10.5px] font-extrabold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                {{ $member->job_title }}
                            </span>

                        </div>

                        {{-- ═══ Back: biography + Links ═══ --}}
                        <div id="team-member-details-{{ $member->getKey() }}" inert :inert="!showDetails"
                             class="mw-team-flip-back flex flex-col items-center justify-center border border-brand-500/30 bg-gradient-to-br from-brand-500/10 via-white to-brand-500/5 p-5 pb-9 dark:from-brand-500/15 dark:via-slate-900 dark:to-slate-900">
                            <div class="text-sm font-extrabold text-brand-700 dark:text-brand-300">
                                {{ $member->full_name }}
                            </div>
                            <div class="mt-1 text-[10.5px] font-bold text-slate-500 dark:text-slate-400">
                                {{ $member->job_title }}
                            </div>

                            @if($member->bio)
                                <p class="mt-3 line-clamp-5 text-center text-[11px] leading-6 text-slate-600 dark:text-slate-300">
                                    {{ $member->bio }}
                                </p>
                            @endif

                            @if($member->website_url || $member->email)
                                <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                    @if($member->website_url)
                                        <a href="{{ $member->website_url }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 rounded-xl border border-brand-200 bg-white/60 px-2.5 py-1 text-[10px] font-bold text-brand-700 transition hover:bg-brand-50 dark:border-brand-500/20 dark:bg-slate-900/60 dark:text-brand-300">
                                            🌐 Website
                                        </a>
                                    @endif
                                    @if($member->email)
                                        <a href="mailto:{{ $member->email }}"
                                           class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white/60 px-2.5 py-1 text-[10px] font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
                                            ✉️ Contact
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
