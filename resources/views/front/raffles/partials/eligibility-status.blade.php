@php($eligibility = app(\App\Application\Raffle\Actions\CheckRaffleEligibilityAction::class)->execute($campaign, auth()->user()))

@if($campaign->vip_monthly_ticket)
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-300 bg-gradient-to-l from-amber-100/80 to-amber-50/60 p-4 dark:border-amber-500/30 dark:from-amber-500/15 dark:to-amber-500/5">
        <div class="flex min-w-0 items-center gap-3">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-amber-400/90 text-sm font-black text-slate-950">👑</span>
            <div class="min-w-0">
                <div class="text-sm font-black text-amber-900 dark:text-amber-200">Monthly gift for members of VIP</div>
                <p class="mt-0.5 text-xs leading-6 text-amber-800/80 dark:text-amber-200/70">Active members VIP who meet raffle requirements receive one free ticket per month, subject to availability.</p>
            </div>
        </div>
        @unless(auth()->user()?->hasActiveVipSubscription())
                <a href="{{ route('vip.index') }}" class="inline-flex shrink-0 items-center gap-1 rounded-xl bg-amber-500 px-3.5 py-2.5 text-xs font-extrabold text-slate-950 transition hover:bg-amber-400">
                    Activate VIP
                    <span aria-hidden="true">←</span>
                </a>
        @endunless
    </div>
@endif

@if(!empty($eligibility['rules']))
    <div class="rounded-2xl border {{ $eligibility['eligible'] ? 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-500/25 dark:bg-emerald-500/5' : 'border-amber-200 bg-amber-50/70 dark:border-amber-500/25 dark:bg-amber-500/5' }} p-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="grid h-8 w-8 place-items-center rounded-xl {{ $eligibility['eligible'] ? 'bg-emerald-500/15 text-emerald-600' : 'bg-amber-500/15 text-amber-600' }} text-sm">
                {{ $eligibility['eligible'] ? '✓' : '⚠' }}
            </span>
            <span class="text-sm font-black {{ $eligibility['eligible'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                {{ $eligibility['eligible'] ? 'You are eligible to enter this raffle' : 'Raffle entry requirements' }}
            </span>
        </div>

        <ul class="space-y-2">
            @foreach($eligibility['rules'] as $rule)
                <li class="flex items-center gap-3 text-xs">
                    <span class="grid h-5 w-5 shrink-0 place-items-center rounded-lg {{ $rule['passed'] ? 'bg-emerald-500/20 text-emerald-600' : 'bg-red-500/15 text-red-500' }} text-[10px] font-black">
                        {{ $rule['current'] === null ? '•' : ($rule['passed'] ? '✓' : '✗') }}
                    </span>
                    <span class="flex-1 font-bold {{ $rule['passed'] ? 'text-slate-600 dark:text-slate-300' : 'text-slate-700 dark:text-slate-200' }}">
                        {{ $rule['label'] }}
                    </span>
                    @if($rule['current'] !== null && !$rule['passed'] && $rule['required'] > 1)
                        <span class="shrink-0 font-black {{ $rule['passed'] ? 'text-emerald-500' : 'text-amber-500' }} tabular-nums">
                            {{ number_format($rule['current']) }} / {{ number_format($rule['required']) }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>

        @if(auth()->check() && !$eligibility['eligible'])
            <div class="mt-3 border-t border-amber-200/40 pt-3 dark:border-amber-500/15">
                <p class="text-[11px] leading-5 font-bold text-amber-600 dark:text-amber-400">
                    Complete the requirements above to activate your chance of winning.
                    <a href="{{ route('account.loyalty') }}#ways-to-earn" class="underline hover:text-amber-700 dark:hover:text-amber-300">How to earn points ←</a>
                    <span class="mx-1">·</span>
                    <a href="{{ route('account.affiliate') }}" class="underline hover:text-amber-700 dark:hover:text-amber-300">Invitation link ←</a>
                </p>
            </div>
        @endif
    </div>
@endif
