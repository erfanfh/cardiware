@extends('admin.layout')
@section('title', 'شرکت کنندگان ' . $drawCampaign->title)
@section('content')
    @include('admin.raffle.campaigns.partials.nav')
    <div class="mb-4 flex justify-end"><x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.exports.participants', $drawCampaign) }}" variant="secondary">خروجی CSV شرکت‌کنندگان</x-admin.form.button></div>
    <x-admin.form method="get" action="{{ route('admin.draw-campaigns.participants.index',$drawCampaign) }}">
        <x-admin.filters.panel :open="$hasActiveFilters"><x-admin.filters.header title="فیلتر شرکت کنندگان"/><x-admin.filters.body><x-admin.filters.grid>
            <x-admin.filters.item label="شناسه کاربر"><x-admin.form.input name="user_id" type="number" :value="request('user_id')"/></x-admin.filters.item>
            <x-admin.filters.item label="جستجوی کاربر"><x-admin.form.input name="query" :value="request('query')" placeholder="نام، موبایل یا ایمیل"/></x-admin.filters.item>
            <x-admin.filters.item label="بلیط فعال"><div class="flex gap-2"><x-admin.form.input name="active_tickets_from" type="number" min="0" :value="request('active_tickets_from')" placeholder="از"/><x-admin.form.input name="active_tickets_to" type="number" min="0" :value="request('active_tickets_to')" placeholder="تا"/></div></x-admin.filters.item>
        </x-admin.filters.grid><x-admin.filters.actions><x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>@if($hasActiveFilters)<x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.participants.index',$drawCampaign) }}" variant="secondary">حذف فیلتر</x-admin.form.button>@endif</x-admin.filters.actions></x-admin.filters.body></x-admin.filters.panel>
    </x-admin.form>
    <x-admin.list.table>
        <x-admin.list.thead><tr><x-admin.list.th width="300px">کاربر</x-admin.list.th><x-admin.list.th width="150px" label="بلیط فعال" sortable sortKey="active_tickets_count" :current-sort="request('sort')" :current-direction="request('direction','desc')"/><x-admin.list.th width="150px" label="کل بلیط" sortable sortKey="total_tickets_count" :current-sort="request('sort')" :current-direction="request('direction','desc')"/><x-admin.list.th width="140px">باطل شده</x-admin.list.th><x-admin.list.th width="140px">دفعات صدور</x-admin.list.th><x-admin.list.th width="170px" label="آخرین بلیط" sortable sortKey="latest_ticket_at" :current-sort="request('sort')" :current-direction="request('direction','desc')"/></tr></x-admin.list.thead>
        <x-admin.list.tbody>
            @forelse($participants as $participant)
                <x-admin.list.tr><x-admin.list.td><div class="text-sm font-extrabold text-slate-800">{{ $participant->user?->full_name }}</div><div class="mt-1 text-[11px] text-slate-400">{{ $participant->user?->mobile }} · #{{ $participant->user_id }}</div><x-admin.list.row-actions><x-admin.list.action tag="a" href="{{ route('admin.draw-campaigns.tickets.index',[$drawCampaign,'user_id'=>$participant->user_id]) }}" variant="primary">مشاهده بلیط ها</x-admin.list.action></x-admin.list.row-actions></x-admin.list.td><x-admin.list.td><span class="text-lg font-black text-brand-700">{{ number_format($participant->active_tickets_count) }}</span></x-admin.list.td><x-admin.list.td><span class="font-black text-slate-800">{{ number_format($participant->total_tickets_count) }}</span></x-admin.list.td><x-admin.list.td><span class="font-bold text-rose-600">{{ number_format($participant->invalidated_tickets_count) }}</span></x-admin.list.td><x-admin.list.td>{{ number_format($participant->grants_count) }}</x-admin.list.td><x-admin.list.td muted>{{ jalali_date($participant->latest_ticket_at,'Y/m/d H:i') }}</x-admin.list.td></x-admin.list.tr>
            @empty <x-admin.list.empty title="شرکت کننده ای وجود ندارد" description="هنوز کاربری در این قرعه کشی بلیط فعال یا تاریخی ندارد."/> @endforelse
        </x-admin.list.tbody>
    </x-admin.list.table>
    <div class="mt-4"><x-admin.list.pagination :model="$participants"/></div>
@endsection
