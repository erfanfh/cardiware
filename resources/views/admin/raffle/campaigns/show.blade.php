@extends('admin.layout')
@section('title', $drawCampaign->title)
@section('content')
    @include('admin.raffle.campaigns.partials.nav')
    @php($statusColor = match($drawCampaign->status) { \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::DRAFT=>'slate', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::SCHEDULED=>'blue', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::OPEN=>'green', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::CLOSED=>'yellow', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::DRAWING=>'brand', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::COMPLETED=>'emerald', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::CANCELLED=>'red' })
    <x-admin.page columns="2">
        <x-slot:sidebar>
            @include('admin.raffle.campaigns.partials.status-actions')
            <x-admin.metabox title="اطلاعات زمانی">
                <div class="space-y-3 text-xs leading-6 text-slate-600">
                    <div><span class="font-bold">شروع:</span> {{ $drawCampaign->starts_at ? jalali_date($drawCampaign->starts_at,'Y/m/d H:i') : 'بدون محدودیت' }}</div>
                    <div><span class="font-bold">پایان:</span> {{ $drawCampaign->closes_at ? jalali_date($drawCampaign->closes_at,'Y/m/d H:i') : 'بدون محدودیت' }}</div>
                    <div><span class="font-bold">Draw:</span> {{ $drawCampaign->draw_at ? jalali_date($drawCampaign->draw_at,'Y/m/d H:i') : 'تعیین نشده' }}</div>
                </div>
            </x-admin.metabox>
        </x-slot:sidebar>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <div class="flex items-center gap-4 min-w-0">
                    <x-admin.list.thumbnail :src="$drawCampaign->coverMediaFile?->url" :alt="$drawCampaign->title" size="lg"/>
                    <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h1 class="text-lg font-black text-slate-900">{{ $drawCampaign->title }}</h1><x-admin.badge :color="$statusColor" size="md">{{ $drawCampaign->status->label() }}</x-admin.badge></div><div class="mt-2 text-xs text-slate-400" dir="ltr">{{ $drawCampaign->slug }} · {{ $drawCampaign->public_token }}</div></div>
                </div>
                <div class="flex gap-2">@can('raffle-campaign.edit')<x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.edit',$drawCampaign) }}" variant="secondary">ویرایش</x-admin.form.button>@endcan</div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            @foreach([
                ['label'=>'بلیط صادر شده','value'=>$drawCampaign->issued_tickets_count,'class'=>'text-slate-900'],
                ['label'=>'بلیط فعال','value'=>$drawCampaign->active_tickets_count,'class'=>'text-brand-700'],
                ['label'=>'شرکت کننده','value'=>$drawCampaign->participants_count,'class'=>'text-emerald-700'],
                ['label'=>'تعداد جایزه','value'=>$drawCampaign->prizes->sum('quantity'),'class'=>'text-slate-900'],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs font-bold text-slate-500">{{ $card['label'] }}</div><div class="mt-2 text-2xl font-black {{ $card['class'] }}">{{ number_format($card['value']) }}</div></div>
            @endforeach
        </div>

        <x-admin.metabox title="جوایز">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                @forelse($drawCampaign->prizes as $prize)
                    <div class="flex gap-3 rounded-2xl border border-slate-200 p-3"><x-admin.list.thumbnail :src="$prize->mediaFile?->url" :alt="$prize->title" size="md-square"/><div><div class="text-sm font-extrabold text-slate-800">{{ $prize->title }}</div><div class="mt-1 text-xs text-slate-500">{{ number_format($prize->quantity) }} جایزه · {{ number_format($prize->effectiveReserveWinnersCount()) }} ذخیره</div>@if($prize->estimated_value)<div class="mt-1 text-[11px] text-slate-400">ارزش تقریبی: {{ number_format($prize->estimated_value) }} تومان</div>@endif</div></div>
                @empty <div class="text-sm text-slate-500">جایزه ای ثبت نشده است.</div> @endforelse
            </div>
        </x-admin.metabox>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <x-admin.metabox title="آخرین بلیط ها">
                <div class="space-y-2">@forelse($recentTickets as $ticket)<div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2"><div><div class="text-xs font-bold text-slate-700" dir="ltr">{{ $ticket->code }}</div><div class="mt-1 text-[11px] text-slate-400">{{ $ticket->user?->full_name }}</div></div><x-admin.badge :color="$ticket->status->value === 'active' ? 'green':'red'" size="sm">{{ $ticket->status->label() }}</x-admin.badge></div>@empty<div class="py-6 text-center text-xs text-slate-400">بلیطی وجود ندارد.</div>@endforelse</div>
                <div class="mt-3"><a class="text-xs font-extrabold text-brand-600" href="{{ route('admin.draw-campaigns.tickets.index',$drawCampaign) }}">مشاهده همه بلیط ها ←</a></div>
            </x-admin.metabox>
            <x-admin.metabox title="آخرین صدورها">
                <div class="space-y-2">@forelse($recentGrants as $grant)<div class="rounded-xl bg-slate-50 px-3 py-2"><div class="flex justify-between gap-3"><span class="text-xs font-bold text-slate-700">{{ $grant->user?->full_name }}</span><span class="text-xs font-black text-brand-700">{{ number_format($grant->quantity) }} بلیط</span></div><div class="mt-1 text-[11px] text-slate-400">ثبت کننده: {{ $grant->issuer?->full_name ?? 'سیستم' }} · {{ jalali_date($grant->issued_at,'Y/m/d H:i') }}</div></div>@empty<div class="py-6 text-center text-xs text-slate-400">صدوری وجود ندارد.</div>@endforelse</div>
            </x-admin.metabox>
        </div>
    </x-admin.page>
@endsection
