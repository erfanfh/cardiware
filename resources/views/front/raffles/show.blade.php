@extends('front.master')

@php
    use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
    use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
    use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;

    $capacityPercent = $campaign->ticket_limit
        ? min(100, round(((int)$campaign->active_tickets_count / max(1, (int)$campaign->ticket_limit)) * 100, 1))
        : null;

    $countdownTarget = match ($campaign->status) {
        DrawCampaignStatusEnum::SCHEDULED => $campaign->starts_at,
        DrawCampaignStatusEnum::OPEN => $campaign->closes_at,
        DrawCampaignStatusEnum::CLOSED, DrawCampaignStatusEnum::DRAWING => $campaign->draw_at,
        default => null,
    };

    $countdownLabel = match ($campaign->status) {
        DrawCampaignStatusEnum::SCHEDULED => 'Until ticket collection begins',
        DrawCampaignStatusEnum::OPEN => 'Until ticket collection ends',
        DrawCampaignStatusEnum::CLOSED => 'Until scheduled draw',
        DrawCampaignStatusEnum::DRAWING => 'Scheduled draw time',
        default => null,
    };
@endphp

@section('head')
    <title>{{ $campaign->title }} | {{ get_setting('general', 'title') }}</title>
    @if($campaign->short_description)
        <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($campaign->short_description), 155) }}">
    @endif
@endsection

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-6 sm:py-8">
        <section class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 shadow-2xl shadow-slate-950/15 dark:border-slate-800 dark:shadow-black/30">
            @if($campaign->coverMediaFile?->url)
                <div class="absolute inset-0">
                    <img src="{{ $campaign->coverMediaFile->url }}" alt="{{ $campaign->title }}" class="h-full w-full object-cover opacity-55">
                </div>
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-brand-950 to-orange-950"></div>
            @endif

            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/95 via-slate-950/80 to-slate-950/40"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(249,115,22,.22),transparent_28%),radial-gradient(circle_at_78%_80%,rgba(59,130,246,.12),transparent_30%)]"></div>

            <div class="relative p-5 sm:p-8 lg:p-10">
                <x-front.breadcrumbs
                    nav-class="flex flex-wrap items-center gap-2 text-[11px] font-bold text-white/55 sm:text-xs"
                    link-class="transition hover:text-brand-300"
                    current-class="max-w-[220px] truncate text-white/90 sm:max-w-none"
                />

                <div class="mt-7 grid gap-7 lg:grid-cols-[minmax(0,1fr)_390px] lg:items-end">
                    <div class="max-w-3xl">
                        <x-front.raffle.status-badge :status="$campaign->status"/>

                        <h1 class="mt-4 text-2xl font-black leading-tight text-white sm:text-3xl lg:text-4xl">
                            {{ $campaign->title }}
                        </h1>

                        @if($campaign->short_description)
                            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base sm:leading-8">
                                {{ $campaign->short_description }}
                            </p>
                        @endif

                        <div class="mt-6 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[0.07] px-3 py-2 text-xs font-extrabold text-white/80 backdrop-blur">
                                <span class="text-brand-300 [&_svg]:h-4 [&_svg]:w-4">@include('icons.award-03')</span>
                                {{ number_format($campaign->prizes->sum('quantity')) }} Reward
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[0.07] px-3 py-2 text-xs font-extrabold text-white/80 backdrop-blur">
                                <span class="text-sky-300 [&_svg]:h-4 [&_svg]:w-4">@include('icons.user-multiple-02')</span>
                                {{ number_format($campaign->participants_count) }} Participants
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[0.07] px-3 py-2 text-xs font-extrabold text-white/80 backdrop-blur">
                                <span class="text-emerald-300 [&_svg]:h-4 [&_svg]:w-4">@include('icons.star')</span>
                                {{ number_format($campaign->active_tickets_count) }} Active ticket
                            </span>
                        </div>
                    </div>

                    <div>
                        @if($countdownTarget)
                            <x-front.raffle.countdown :target="$countdownTarget" :label="$countdownLabel"/>
                        @elseif($campaign->status === DrawCampaignStatusEnum::COMPLETED)
                            <div class="rounded-3xl border border-emerald-300/15 bg-emerald-500/10 p-5 backdrop-blur-md">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-400/15 text-emerald-300 [&_svg]:h-6 [&_svg]:w-6">
                                        @include('icons.medal-06')
                                    </span>
                                    <div>
                                        <div class="text-sm font-black text-white">Raffle completed</div>
                                        <p class="mt-1 text-xs leading-6 text-white/60">
                                            The final result is recorded; if publicly released, winners appear below.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="min-w-0 space-y-6 lg:col-span-8 xl:col-span-9">
                <div class="rounded-2xl border border-brand-500/25 bg-brand-500/5 px-4 py-3 text-xs font-bold leading-6 text-brand-700 dark:text-brand-300">
                    Earn points from visits, comments, media submissions and purchases.
                    <a href="{{ route('account.loyalty') }}#ways-to-earn" class="font-black underline">How to earn points</a>
                </div>
                @auth
                    <x-front.cart class="relative overflow-hidden !p-0">
                        <div class="absolute inset-0 bg-gradient-to-l from-brand-50/80 via-white to-white dark:from-brand-950/30 dark:via-slate-900 dark:to-slate-900"></div>
                        <div class="absolute -left-12 -top-12 h-36 w-36 rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-500/10"></div>

                        <div class="relative p-4 sm:p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-500 text-white shadow-sm [&_svg]:h-6 [&_svg]:w-6">
                                        @include('icons.award-03')
                                    </span>
                                    <div>
                                        <h2 class="text-sm font-black text-slate-900 dark:text-white sm:text-base">Your participation status</h2>
                                        <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                            Tickets issued to your account for this raffle.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 sm:min-w-[240px]">
                                    <div class="rounded-2xl border border-brand-100 bg-white/80 p-3 text-center dark:border-brand-500/20 dark:bg-slate-800/80">
                                        <div class="text-[10px] font-bold text-slate-400">Active ticket</div>
                                        <div class="mt-1 text-lg font-black text-brand-600 dark:text-brand-300">{{ number_format($myActiveTicketsCount) }}</div>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-white/80 p-3 text-center dark:border-slate-700 dark:bg-slate-800/80">
                                        <div class="text-[10px] font-bold text-slate-400">Total tickets</div>
                                        <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">{{ number_format($myTotalTicketsCount) }}</div>
                                    </div>
                                </div>
                            </div>

                            @if($myWinningRecords->isNotEmpty())
                                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 [&_svg]:h-5 [&_svg]:w-5">
                                            @include('icons.medal-06')
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-black text-amber-900 dark:text-amber-200">Congratulations! You have been selected in the draw</div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($myWinningRecords as $winningRecord)
                                                    <div class="rounded-2xl border border-amber-200 bg-white p-3 dark:border-amber-500/20 dark:bg-slate-900/60">
                                                        <div class="text-[11px] font-extrabold text-amber-800 dark:text-amber-300">{{ $winningRecord->prize?->title }} · {{ $winningRecord->status->label() }}</div>
                                                        @if($winningRecord->confirmation_deadline_at && $winningRecord->status === DrawWinnerStatusEnum::PENDING)
                                                            <div class="mt-1 text-[10px] text-amber-700">Confirmation deadline: {{ (($winningRecord->confirmation_deadline_at) ? \Illuminate\Support\Carbon::parse($winningRecord->confirmation_deadline_at)->format('Y/m/d H:i') : '') }}</div>
                                                        @endif
                                                        @include('front.raffles.partials.winner-claim-actions', ['winner' => $winningRecord])
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($myTickets->isNotEmpty())
                                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                    @foreach($myTickets as $ticket)
                                        <div class="rounded-2xl border px-3 py-3 {{ $ticket->status === DrawTicketStatusEnum::ACTIVE ? 'border-emerald-100 bg-emerald-50/70 dark:border-emerald-500/20 dark:bg-emerald-500/10' : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/60' }}">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-[10px] font-bold {{ $ticket->status === DrawTicketStatusEnum::ACTIVE ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400' }}">
                                                    {{ $ticket->status->label() }}
                                                </span>
                                                <span class="text-[10px] text-slate-400">#{{ $ticket->id }}</span>
                                            </div>
                                            <div class="mt-1.5 truncate text-xs font-black tracking-wide text-slate-800 dark:text-slate-100" dir="ltr" title="{{ $ticket->code }}">
                                                {{ $ticket->code }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <a href="{{ route('account.raffles', ['campaign' => $campaign->public_token]) }}#my-tickets" class="mt-4 inline-flex items-center gap-2 text-xs font-extrabold text-brand-600 transition hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">
                                    View all my tickets
                                    <span class="[&_svg]:h-4 [&_svg]:w-4"><x-front.arrow-right-02/></span>
                                </a>
                            @else
                                <div class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-white/60 p-4 text-center dark:border-slate-700 dark:bg-slate-800/40">
                                    <div class="text-xs font-extrabold text-slate-700 dark:text-slate-300">You do not have a ticket for this raffle yet</div>
                                    <p class="mt-1 text-[11px] leading-6 text-slate-400">
                                        Ticket methods are set by each campaign. Issued ticket codes will appear here.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </x-front.cart>
                @else
                    <x-front.cart class="relative overflow-hidden">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-sm font-black text-slate-900 dark:text-white">View your tickets</h2>
                                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">Sign in to see your entries and ticket codes for this raffle.</p>
                            </div>
                            <a href="{{ route('auth.index') }}" class="inline-flex h-11 shrink-0 items-center justify-center rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white transition hover:bg-brand-600 dark:hover:bg-brand-400">
                                Sign in
                            </a>
                        </div>
                    </x-front.cart>
                @endauth

                @if($campaign->eligibility_rules || $campaign->vip_monthly_ticket)
                    <div id="raffle-eligibility" class="scroll-mt-24 space-y-4">
                        @include('front.raffles.partials.eligibility-status', ['campaign' => $campaign])
                    </div>
                @endif

                @include('front.raffles.partials.loyalty-rewards')

                <section id="prizes" class="scroll-mt-24">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <div class="text-[11px] font-extrabold text-brand-500 dark:text-brand-300">What you can win</div>
                            <h2 class="mt-1 text-lg font-black text-slate-900 dark:text-white sm:text-xl">Raffle prizes</h2>
                        </div>
                        <span class="rounded-xl bg-slate-100 px-2.5 py-1.5 text-[11px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            {{ number_format($campaign->prizes->sum('quantity')) }} Reward
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach($campaign->prizes as $prize)
                            <x-front.raffle.prize-card :prize="$prize"/>
                        @endforeach
                    </div>
                </section>

                @if($campaign->body)
                    <x-front.cart id="about-raffle" class="scroll-mt-24">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">
                                @include('icons.star')
                            </span>
                            <div>
                                <div class="text-[10px] font-bold text-brand-500 dark:text-brand-300">About this campaign</div>
                                <h2 class="mt-1 text-base font-black text-slate-900 dark:text-white">About the raffle</h2>
                            </div>
                        </div>

                        <div class="mt-5 text-sm leading-8 text-slate-600 dark:text-slate-300 [&_a]:font-bold [&_a]:text-brand-600 dark:[&_a]:text-brand-300 [&_h2]:mt-6 [&_h2]:text-lg [&_h2]:font-black [&_h2]:text-slate-900 dark:[&_h2]:text-white [&_h3]:mt-5 [&_h3]:font-black [&_h3]:text-slate-900 dark:[&_h3]:text-white [&_li]:my-1 [&_ol]:mr-5 [&_ol]:list-decimal [&_p]:my-3 [&_ul]:mr-5 [&_ul]:list-disc">
                            {!! $campaign->body !!}
                        </div>
                    </x-front.cart>
                @endif

                @if($campaign->rules)
                    <x-front.cart id="rules" class="scroll-mt-24">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300 [&_svg]:h-5 [&_svg]:w-5">
                                @include('icons.award-03')
                            </span>
                            <div>
                                <div class="text-[10px] font-bold text-amber-600 dark:text-amber-300">Transparent and clear</div>
                                <h2 class="mt-1 text-base font-black text-slate-900 dark:text-white">Entry rules</h2>
                            </div>
                        </div>

                        <div class="mt-5 text-sm leading-8 text-slate-600 dark:text-slate-300 [&_a]:font-bold [&_a]:text-brand-600 dark:[&_a]:text-brand-300 [&_h2]:mt-6 [&_h2]:text-lg [&_h2]:font-black [&_h2]:text-slate-900 dark:[&_h2]:text-white [&_h3]:mt-5 [&_h3]:font-black [&_h3]:text-slate-900 dark:[&_h3]:text-white [&_li]:my-1 [&_ol]:mr-5 [&_ol]:list-decimal [&_p]:my-3 [&_ul]:mr-5 [&_ul]:list-disc">
                            {!! $campaign->rules !!}
                        </div>
                    </x-front.cart>
                @endif

                @if($campaign->status === DrawCampaignStatusEnum::COMPLETED)
                    <section id="winners" class="scroll-mt-24">
                        <x-front.cart class="relative overflow-hidden">
                            <div class="absolute -left-14 -top-14 h-36 w-36 rounded-full bg-amber-100/70 blur-3xl dark:bg-amber-500/10"></div>
                            <div class="relative">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <div class="text-[11px] font-extrabold text-amber-600 dark:text-amber-300">Final result</div>
                                        <h2 class="mt-1 text-lg font-black text-slate-900 dark:text-white sm:text-xl">Raffle winners</h2>
                                    </div>
                                    <div class="text-[11px] leading-6 text-slate-400">User information is limited to protect privacy.</div>
                                </div>

                                @if($campaign->show_winners_publicly && $publicWinners->isNotEmpty())
                                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                        @foreach($publicWinners as $winner)
                                            <div class="rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-4 dark:border-amber-500/15 dark:from-amber-500/10 dark:to-slate-900">
                                                <div class="flex items-start gap-3">
                                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-sm [&_svg]:h-5 [&_svg]:w-5">
                                                        @include('icons.medal-06')
                                                    </span>
                                                    <div class="min-w-0">
                                                        <div class="text-[10px] font-bold text-amber-600 dark:text-amber-300">{{ $winner['prize_title'] }}</div>
                                                        <div class="mt-1 truncate text-sm font-black text-slate-900 dark:text-white">{{ $winner['name'] }}</div>
                                                        @if($winner['ticket_code'])
                                                            <div class="mt-2 text-[10px] font-bold tracking-wide text-slate-400" dir="ltr">{{ $winner['ticket_code'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($winner['delivered'])
                                                    <div class="mt-3 inline-flex rounded-xl bg-emerald-50 px-2.5 py-1.5 text-[10px] font-extrabold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Prize delivered</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(!$campaign->show_winners_publicly)
                                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center text-xs leading-7 text-slate-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                                        The result is recorded but public winner names are disabled for this campaign.
                                    </div>
                                @else
                                    <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center text-xs leading-7 text-slate-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                                        Result recorded; winners will appear after final approval.
                                    </div>
                                @endif
                            </div>
                        </x-front.cart>
                    </section>
                @endif

                @if($publicDrawRuns->isNotEmpty())
                    <section id="verification" class="scroll-mt-24">
                        <x-front.cart>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-300">Transparency and verification</div>
                                    <h2 class="mt-1 text-lg font-black text-slate-900 dark:text-white">Verifiable raffle report</h2>
                                    <p class="mt-2 max-w-2xl text-xs leading-7 text-slate-500 dark:text-slate-400">Commitment seed is recorded before the result is announced, and upon completion, seed and the eligible set are published for independent reproduction.</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3">
                                @foreach($publicDrawRuns as $proof)
                                    @php($run = $proof['run'])
                                    @php($verification = $proof['verification'])
                                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <span class="rounded-lg bg-white px-2 py-1 text-[10px] font-extrabold text-slate-600 dark:bg-slate-900 dark:text-slate-300">{{ $run->type->label() }}</span>
                                                <span class="text-[10px] font-bold text-slate-400">{{ $run->status->label() }}</span>
                                            </div>
                                            @if($verification)
                                                <span class="rounded-xl px-2.5 py-1.5 text-[10px] font-extrabold {{ $verification->valid ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300' }}">
                                                    {{ $verification->valid ? 'Result verified' : 'Needs review' }}
                                                </span>
                                            @endif
                                        </div>
                                        <dl class="mt-3 grid grid-cols-1 gap-3 text-[10px] sm:grid-cols-2">
                                            <div><dt class="font-bold text-slate-400">Algorithm</dt><dd class="mt-1 break-all font-mono text-slate-700 dark:text-slate-300" dir="ltr">{{ $run->algorithm }}</dd></div>
                                            <div><dt class="font-bold text-slate-400">Eligible ticket count</dt><dd class="mt-1 font-black text-slate-700 dark:text-slate-300">{{ number_format($run->eligible_ticket_count) }}</dd></div>
                                            <div class="sm:col-span-2"><dt class="font-bold text-slate-400">Seed commitment</dt><dd class="mt-1 break-all font-mono text-slate-700 dark:text-slate-300" dir="ltr">{{ $run->seed_commitment }}</dd></div>
                                            @if($run->seed_reveal)
                                                <div class="sm:col-span-2"><dt class="font-bold text-slate-400">Seed reveal</dt><dd class="mt-1 break-all font-mono text-slate-700 dark:text-slate-300" dir="ltr">{{ $run->seed_reveal }}</dd></div>
                                                <div class="sm:col-span-2"><dt class="font-bold text-slate-400">Eligible set hash</dt><dd class="mt-1 break-all font-mono text-slate-700 dark:text-slate-300" dir="ltr">{{ $run->eligible_set_hash }}</dd></div>
                                            @endif
                                        </dl>
                                        @if($run->status === \App\Domain\Raffle\Enums\DrawRunStatusEnum::COMPLETED)
                                            <a href="{{ route('raffles.verification', [$campaign->slug, $run]) }}" class="mt-4 inline-flex rounded-xl bg-slate-900 px-3 py-2 text-[10px] font-extrabold text-white hover:bg-brand-600 dark:bg-slate-700">Download full report JSON</a>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </x-front.cart>
                    </section>
                @endif
            </div>

            <aside class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="lg:sticky lg:top-24 lg:space-y-4">
                    <x-front.cart>
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-black text-slate-900 dark:text-white">Raffle information</h2>
                            <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 [&_svg]:h-4 [&_svg]:w-4">
                                @include('icons.calendar-03')
                            </span>
                        </div>

                        <div class="mt-4 divide-y divide-slate-100 text-xs dark:divide-slate-800">
                            @foreach([
                                ['label' => 'Start', 'value' => $campaign->starts_at ? (($campaign->starts_at) ? \Illuminate\Support\Carbon::parse($campaign->starts_at)->format('Y/m/d H:i') : '') : 'Unlimited'],
                                ['label' => 'Ticket collection ends', 'value' => $campaign->closes_at ? (($campaign->closes_at) ? \Illuminate\Support\Carbon::parse($campaign->closes_at)->format('Y/m/d H:i') : '') : 'Unlimited'],
                                ['label' => 'Draw time', 'value' => $campaign->draw_at ? (($campaign->draw_at) ? \Illuminate\Support\Carbon::parse($campaign->draw_at)->format('Y/m/d H:i') : '') : 'To be announced'],
                                ['label' => 'Limit per user', 'value' => $campaign->ticket_limit_per_user ? number_format($campaign->ticket_limit_per_user) . ' Ticket' : 'Unlimited'],
                                ['label' => 'Multiple wins per person', 'value' => $campaign->allow_multiple_wins_per_user ? 'Allowed' : 'Not allowed'],
                            ] as $item)
                                <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                    <span class="text-slate-400">{{ $item['label'] }}</span>
                                    <span class="text-left font-extrabold text-slate-700 dark:text-slate-300">{{ $item['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-front.cart>

                    @if($campaign->ticket_limit)
                        <x-front.cart>
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white">Ticket capacity</div>
                                    <div class="mt-1 text-[10px] text-slate-400">{{ number_format($campaign->active_tickets_count) }} of {{ number_format($campaign->ticket_limit) }}</div>
                                </div>
                                <div class="text-lg font-black text-brand-600 dark:text-brand-300">{{ number_format($capacityPercent, 0) }}%</div>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-l from-brand-500 via-orange-400 to-amber-400 transition-all" style="width: {{ $capacityPercent }}%"></div>
                            </div>
                            @if($campaign->remainingTicketCapacity() !== null)
                                <p class="mt-3 text-[11px] leading-6 text-slate-400">{{ number_format($campaign->remainingTicketCapacity()) }} active tickets remain before capacity is reached.</p>
                            @endif
                        </x-front.cart>
                    @endif

                    <x-front.cart>
                        <div class="text-xs font-black text-slate-900 dark:text-white">Quick access</div>
                        <div class="mt-3 grid gap-2">
                            <a href="#prizes" class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5 text-xs font-extrabold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                Prizes
                                <span class="[&_svg]:h-4 [&_svg]:w-4"><x-front.arrow-right-02/></span>
                            </a>
                            @if($campaign->rules)
                                <a href="#rules" class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5 text-xs font-extrabold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                    Entry rules
                                    <span class="[&_svg]:h-4 [&_svg]:w-4"><x-front.arrow-right-02/></span>
                                </a>
                            @endif
                            @if($campaign->status === DrawCampaignStatusEnum::COMPLETED)
                                <a href="#winners" class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5 text-xs font-extrabold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                    Winners
                                    <span class="[&_svg]:h-4 [&_svg]:w-4"><x-front.arrow-right-02/></span>
                                </a>
                            @endif
                            @if($publicDrawRuns->isNotEmpty())
                                <a href="#verification" class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2.5 text-xs font-extrabold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                    Verify result
                                    <span class="[&_svg]:h-4 [&_svg]:w-4"><x-front.arrow-right-02/></span>
                                </a>
                            @endif
                            @auth
                                <a href="{{ route('account.raffles', ['campaign' => $campaign->public_token]) }}" class="flex items-center justify-between rounded-2xl bg-brand-500 px-3 py-2.5 text-xs font-extrabold text-white transition hover:bg-brand-600 dark:hover:bg-brand-400">
                                    All my tickets
                                    <span class="[&_svg]:h-4 [&_svg]:w-4"><x-front.arrow-right-02/></span>
                                </a>
                            @endauth
                        </div>
                    </x-front.cart>
                </div>
            </aside>
        </section>
    </main>
@endsection
