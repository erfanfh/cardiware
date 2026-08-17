@extends('admin.layout')
@section('title', 'قرعه کشی ها')
@section('content')
    @include('admin.raffle.campaigns.partials.filters-panel')
    <x-admin.list.table>
        <x-admin.list.thead><tr>
            <x-admin.list.th width="310px">قرعه کشی</x-admin.list.th>
            <x-admin.list.th width="130px">وضعیت</x-admin.list.th>
            <x-admin.list.th width="125px">جوایز</x-admin.list.th>
            <x-admin.list.th width="140px" label="بلیط فعال" sortable sortKey="active_tickets_count" :current-sort="request('sort')" :current-direction="request('direction','desc')"/>
            <x-admin.list.th width="140px" label="شرکت کننده" sortable sortKey="participants_count" :current-sort="request('sort')" :current-direction="request('direction','desc')"/>
            <x-admin.list.th width="190px">بازه شرکت</x-admin.list.th>
            <x-admin.list.th width="140px" label="ایجاد" sortable sortKey="created_at" :current-sort="request('sort')" :current-direction="request('direction','desc')"/>
        </tr></x-admin.list.thead>
        <x-admin.list.tbody>
            @forelse($campaigns as $campaign)
                @php($statusColor = match($campaign->status) { \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::DRAFT=>'slate', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::SCHEDULED=>'blue', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::OPEN=>'green', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::CLOSED=>'yellow', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::DRAWING=>'brand', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::COMPLETED=>'emerald', \App\Domain\Raffle\Enums\DrawCampaignStatusEnum::CANCELLED=>'red' })
                <x-admin.list.tr>
                    <x-admin.list.td><div class="flex items-center gap-3"><x-admin.list.thumbnail :src="$campaign->coverMediaFile?->url" :alt="$campaign->title" size="md-square"/><div class="min-w-0"><a href="{{ route('admin.draw-campaigns.show',$campaign) }}" class="block truncate text-sm font-extrabold text-slate-800 hover:text-brand-600">{{ $campaign->title }}</a><div class="mt-1 text-[11px] text-slate-400" dir="ltr">{{ $campaign->slug }} · #{{ $campaign->id }}</div><x-admin.list.row-actions><x-admin.list.action tag="a" href="{{ route('admin.draw-campaigns.show',$campaign) }}" variant="primary">مدیریت</x-admin.list.action>@can('raffle-campaign.edit')<span class="text-slate-300">|</span><x-admin.list.action tag="a" href="{{ route('admin.draw-campaigns.edit',$campaign) }}">ویرایش</x-admin.list.action>@endcan</x-admin.list.row-actions></div></div></x-admin.list.td>
                    <x-admin.list.td><x-admin.badge :color="$statusColor" size="md">{{ $campaign->status->label() }}</x-admin.badge></x-admin.list.td>
                    <x-admin.list.td><span class="font-black text-slate-800">{{ number_format($campaign->prizes_count) }}</span></x-admin.list.td>
                    <x-admin.list.td><span class="font-black text-brand-700">{{ number_format($campaign->active_tickets_count) }}</span></x-admin.list.td>
                    <x-admin.list.td><span class="font-black text-slate-800">{{ number_format($campaign->participants_count) }}</span></x-admin.list.td>
                    <x-admin.list.td muted><div class="text-xs leading-6"><div>{{ $campaign->starts_at ? jalali_date($campaign->starts_at,'Y/m/d H:i') : 'بدون شروع' }}</div><div>{{ $campaign->closes_at ? jalali_date($campaign->closes_at,'Y/m/d H:i') : 'بدون پایان' }}</div></div></x-admin.list.td>
                    <x-admin.list.td muted>{{ jalali_date($campaign->created_at,'Y/m/d H:i') }}</x-admin.list.td>
                </x-admin.list.tr>
            @empty
                <x-admin.list.empty title="قرعه کشی ای وجود ندارد" description="اولین قرعه کشی را ایجاد کنید." action-text="ایجاد قرعه کشی" :action-href="route('admin.draw-campaigns.create')"/>
            @endforelse
        </x-admin.list.tbody>
    </x-admin.list.table>
    <div class="mt-4"><x-admin.list.pagination :model="$campaigns"/></div>
@endsection
