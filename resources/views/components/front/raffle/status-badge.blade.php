@props(['status'])

@php
    use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;

    [$label, $classes, $dot] = match ($status) {
        DrawCampaignStatusEnum::OPEN => [
            'Ongoing',
            'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300',
            'bg-emerald-500 dark:bg-emerald-300',
        ],
        DrawCampaignStatusEnum::SCHEDULED => [
            'Coming soon',
            'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300',
            'bg-sky-500 dark:bg-sky-300',
        ],
        DrawCampaignStatusEnum::CLOSED => [
            'Ticket registration ended',
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300',
            'bg-amber-500 dark:bg-amber-300',
        ],
        DrawCampaignStatusEnum::DRAWING => [
            'Drawing raffle',
            'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-300',
            'bg-violet-500 dark:bg-violet-300 animate-pulse',
        ],
        DrawCampaignStatusEnum::COMPLETED => [
            'Raffle completed',
            'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300',
            'bg-slate-500 dark:bg-slate-400',
        ],
        default => [
            $status->label(),
            'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300',
            'bg-slate-400',
        ],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-2 rounded-xl border px-2.5 py-1.5 text-[11px] font-extrabold {$classes}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>
