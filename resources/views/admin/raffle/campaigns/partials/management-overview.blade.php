@php
    use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;

    $statusColor = match($drawCampaign->status) {
        DrawCampaignStatusEnum::DRAFT => 'slate',
        DrawCampaignStatusEnum::SCHEDULED => 'blue',
        DrawCampaignStatusEnum::OPEN => 'green',
        DrawCampaignStatusEnum::CLOSED => 'yellow',
        DrawCampaignStatusEnum::DRAWING => 'brand',
        DrawCampaignStatusEnum::COMPLETED => 'emerald',
        DrawCampaignStatusEnum::CANCELLED => 'red',
    };
    $isPubliclyVisible = !in_array($drawCampaign->status, [
        DrawCampaignStatusEnum::DRAFT,
        DrawCampaignStatusEnum::CANCELLED,
    ], true);
@endphp

<div class="my-5 grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <div class="flex min-w-0 items-center gap-4">
                    <x-admin.list.thumbnail :src="$drawCampaign->coverMediaFile?->url" :alt="$drawCampaign->title" size="lg"/>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="truncate text-lg font-black text-slate-900">{{ $drawCampaign->title }}</h1>
                            <x-admin.badge :color="$statusColor" size="md">{{ $drawCampaign->status->label() }}</x-admin.badge>
                        </div>
                        <div class="mt-2 truncate text-xs text-slate-400" dir="ltr">{{ $drawCampaign->slug }}</div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-admin.form.button tag="a" href="{{ route('admin.settings.raffle') }}" variant="secondary">
                        تنظیمات و پیامک
                    </x-admin.form.button>
                    @if($isPubliclyVisible)
                        <x-admin.form.button tag="a" href="{{ route('raffles.show', $drawCampaign->slug) }}" target="_blank" variant="secondary">
                            مشاهده در سایت
                        </x-admin.form.button>
                    @else
                        <x-admin.form.button variant="secondary" disabled>
                            هنوز عمومی نیست
                        </x-admin.form.button>
                    @endif
                    <x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.index') }}" variant="secondary">
                        همه قرعه کشی ها
                    </x-admin.form.button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach([
                ['label' => 'بلیط صادر شده', 'value' => $drawCampaign->issued_tickets_count, 'class' => 'text-slate-900'],
                ['label' => 'بلیط فعال', 'value' => $drawCampaign->active_tickets_count, 'class' => 'text-brand-700'],
                ['label' => 'شرکت کننده', 'value' => $drawCampaign->participants_count, 'class' => 'text-emerald-700'],
                ['label' => 'تعداد جایزه', 'value' => $drawCampaign->prizes->sum('quantity'), 'class' => 'text-slate-900'],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-bold text-slate-500">{{ $card['label'] }}</div>
                    <div class="mt-2 text-2xl font-black {{ $card['class'] }}">{{ number_format($card['value']) }}</div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-500 shadow-sm">
            <span><strong class="text-slate-700">شروع:</strong> {{ $drawCampaign->starts_at ? jalali_date($drawCampaign->starts_at, 'Y/m/d H:i') : 'بدون محدودیت' }}</span>
            <span><strong class="text-slate-700">پایان:</strong> {{ $drawCampaign->closes_at ? jalali_date($drawCampaign->closes_at, 'Y/m/d H:i') : 'بدون محدودیت' }}</span>
            <span><strong class="text-slate-700">زمان قرعه کشی:</strong> {{ $drawCampaign->draw_at ? jalali_date($drawCampaign->draw_at, 'Y/m/d H:i') : 'تعیین نشده' }}</span>
        </div>
    </div>

    <div>
        @include('admin.raffle.campaigns.partials.status-actions')
    </div>
</div>
