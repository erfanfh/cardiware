@props(['campaign'])

@php
    use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;

    $capacityPercent = $campaign->ticket_limit
        ? min(100, round(((int)$campaign->active_tickets_count / max(1, (int)$campaign->ticket_limit) * 100), 1))
        : null;

    $countdownTarget = match ($campaign->status) {
        DrawCampaignStatusEnum::SCHEDULED => $campaign->starts_at,
        DrawCampaignStatusEnum::OPEN => $campaign->closes_at,
        DrawCampaignStatusEnum::CLOSED, DrawCampaignStatusEnum::DRAWING => $campaign->draw_at,
        default => null,
    };

    $countdownLabel = match ($campaign->status) {
        DrawCampaignStatusEnum::SCHEDULED => 'تا شروع',
        DrawCampaignStatusEnum::OPEN => 'تا پایان دریافت بلیط',
        DrawCampaignStatusEnum::CLOSED, DrawCampaignStatusEnum::DRAWING => 'تا زمان قرعه کشی',
        default => null,
    };
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:border-brand-500/30">
    <a href="{{ route('raffles.show', $campaign->slug) }}" class="relative block aspect-[16/10] overflow-hidden bg-slate-900">
        @if($campaign->coverMediaFile)
            <x-front.responsive-image
                :media="$campaign->coverMediaFile"
                :fallback="$campaign->coverMediaFile?->url"
                :alt="$campaign->title"
                :title="$campaign->title"
                variant="medium"
                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                wrapper-class="h-full w-full"
                :skeleton="true"
                loading="lazy"
                decoding="async"
                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 420px"
            />
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-brand-950 to-orange-950"></div>
            <div class="absolute -left-12 -top-12 h-44 w-44 rounded-full bg-brand-500/25 blur-3xl"></div>
            <div class="absolute -bottom-12 right-8 h-40 w-40 rounded-full bg-orange-500/20 blur-3xl"></div>
            <div class="absolute inset-0 flex items-center justify-center text-white/80 [&_svg]:h-16 [&_svg]:w-16">
                @include('icons.award-03')
            </div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/15 to-transparent"></div>

        <div class="absolute right-3 top-3">
            <x-front.raffle.status-badge :status="$campaign->status"/>
        </div>

        <div class="absolute inset-x-0 bottom-0 p-4 text-white">
            <div class="flex items-end justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-white/60">{{ number_format((int)($campaign->prize_units_count ?? $campaign->prizes->sum('quantity'))) }} جایزه</div>
                    <h2 class="mt-1 line-clamp-2 text-base font-black leading-7 sm:text-lg">{{ $campaign->title }}</h2>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/10 backdrop-blur transition group-hover:bg-white/20 [&_svg]:h-5 [&_svg]:w-5">
                    @include('icons.arrow-left-02')
                </span>
            </div>
        </div>
    </a>

    <div class="flex flex-1 flex-col p-4 sm:p-5">
        @if($campaign->short_description)
            <p class="line-clamp-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                {{ $campaign->short_description }}
            </p>
        @endif

        <div class="mt-4 grid grid-cols-2 gap-2">
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/50">
                <div class="text-[10px] font-bold text-slate-400">بلیط فعال</div>
                <div class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ number_format($campaign->active_tickets_count) }}</div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/50">
                <div class="text-[10px] font-bold text-slate-400">شرکت کننده</div>
                <div class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ number_format($campaign->participants_count) }}</div>
            </div>
        </div>

        @if($capacityPercent !== null)
            <div class="mt-4">
                <div class="mb-2 flex items-center justify-between gap-3 text-[10px] font-bold text-slate-400">
                    <span>ظرفیت بلیط</span>
                    <span>{{ number_format($capacityPercent, 0) }}٪ تکمیل</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-l from-brand-500 to-orange-400" style="width: {{ $capacityPercent }}%"></div>
                </div>
            </div>
        @endif

        @if($countdownTarget)
            <div class="mt-4">
                <x-front.raffle.countdown :target="$countdownTarget" :label="$countdownLabel" compact/>
            </div>
        @elseif($campaign->status === DrawCampaignStatusEnum::COMPLETED)
            <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-3 text-xs font-extrabold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                نتیجه این قرعه کشی نهایی شده است.
            </div>
        @endif

        <a href="{{ route('raffles.show', $campaign->slug) }}" class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 text-sm font-extrabold text-white transition hover:bg-brand-600 dark:bg-slate-800 dark:hover:bg-brand-500">
            مشاهده جزئیات قرعه کشی
            <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
        </a>
    </div>
</article>
