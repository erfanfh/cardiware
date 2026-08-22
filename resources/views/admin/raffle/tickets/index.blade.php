@extends('admin.layout')
@section('title', 'بلیط های ' . $drawCampaign->title)
@section('content')
    @include('admin.raffle.campaigns.partials.nav')
    <div class="mb-4 flex justify-end"><x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.exports.tickets', $drawCampaign) }}" variant="secondary">خروجی CSV بلیط‌ها</x-admin.form.button></div>
    @can('raffle-ticket.issue')
        <x-admin.metabox title="صدور دستی بلیط">
            @if($drawCampaign->acceptsTicketsAt())
                <x-admin.form method="post" action="{{ route('admin.draw-campaigns.tickets.issue',$drawCampaign) }}">
                    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string)\Illuminate\Support\Str::ulid()) }}">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                        <div class="lg:col-span-2"><x-admin.field required label="کاربر"><x-admin.form.async-select name="user_id" :value="old('user_id')" :search-url="route('admin.draw-campaigns.user-search')" placeholder="نام، موبایل، ایمیل یا شناسه کاربر..."/></x-admin.field></div>
                        <x-admin.field required label="تعداد بلیط"><x-admin.form.input name="quantity" type="number" min="1" max="1000" :value="old('quantity',1)"/></x-admin.field>
                        <div class="lg:col-span-1"></div>
                        <div class="lg:col-span-4"><x-admin.field required label="دلیل صدور"><x-admin.form.textarea name="reason" rows="2" :value="old('reason')" placeholder="مثلا هدیه کمپین یا جبران مشکل سفارش"/></x-admin.field></div>
                    </div>
                    <div class="mt-3 flex justify-end"><x-admin.form.button type="submit" variant="solid">صدور بلیط</x-admin.form.button></div>
                </x-admin.form>
            @else
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-7 text-amber-800">صدور بلیط فقط زمانی ممکن است که قرعه کشی در وضعیت «در حال برگزاری» و داخل بازه زمانی دریافت بلیط باشد.</div>
            @endif
        </x-admin.metabox>
    @endcan

    @include('admin.raffle.tickets.partials.filters')
    <x-admin.list.table>
        <x-admin.list.thead><tr><x-admin.list.th width="175px">کد</x-admin.list.th><x-admin.list.th width="260px">کاربر</x-admin.list.th><x-admin.list.th width="120px">وضعیت</x-admin.list.th><x-admin.list.th width="140px">سری صدور</x-admin.list.th><x-admin.list.th width="170px">ثبت کننده</x-admin.list.th><x-admin.list.th width="160px" label="زمان صدور" sortable sortKey="issued_at" :current-sort="request('sort')" :current-direction="request('direction','desc')"/><x-admin.list.th width="220px">ابطال</x-admin.list.th></tr></x-admin.list.thead>
        <x-admin.list.tbody>
            @forelse($tickets as $ticket)
                <x-admin.list.tr>
                    <x-admin.list.td><div class="text-xs font-black text-slate-800" dir="ltr">{{ $ticket->code }}</div><div class="mt-1 text-[10px] text-slate-400">#{{ $ticket->id }}</div></x-admin.list.td>
                    <x-admin.list.td><div class="text-xs font-bold text-slate-800">{{ $ticket->user?->full_name }}</div><div class="mt-1 text-[11px] text-slate-400">{{ $ticket->user?->mobile }} · #{{ $ticket->user_id }}</div></x-admin.list.td>
                    <x-admin.list.td><x-admin.badge :color="$ticket->status->value === 'active' ? 'green':'red'" size="md">{{ $ticket->status->label() }}</x-admin.badge></x-admin.list.td>
                    <x-admin.list.td><a href="{{ route('admin.draw-campaigns.tickets.index',[$drawCampaign,'grant_id'=>$ticket->ticket_grant_id]) }}" class="text-xs font-extrabold text-brand-600">#{{ $ticket->ticket_grant_id }}</a></x-admin.list.td>
                    <x-admin.list.td muted>{{ $ticket->grant?->issuer?->full_name ?? 'سیستم' }}</x-admin.list.td>
                    <x-admin.list.td muted>{{ jalali_date($ticket->issued_at,'Y/m/d H:i') }}</x-admin.list.td>
                    <x-admin.list.td>
                        @can('raffle-ticket.invalidate')
                            @if($ticket->status->value === 'active' && $drawCampaign->status->allowsTicketInvalidation())
                                @if(data_get($ticket->grant?->metadata, 'invalidation_mode') === 'grant')
                                    <x-admin.form method="post" action="{{ route('admin.draw-campaigns.ticket-grants.invalidate',[$drawCampaign,$ticket->grant]) }}" class="flex items-center gap-2 js-confirm" data-confirm-title="ابطال کل سری بلیط" data-confirm-text="تمام بلیط های فعال سری #{{ $ticket->ticket_grant_id }} باطل شوند؟ اگر این سری از پاداش امتیازی آمده باشد، فرآیند بازگشت امتیاز فعال می شود.">
                                        <x-admin.form.input name="reason" placeholder="دلیل ابطال سری" class="min-w-[120px]"/>
                                        <x-admin.form.button type="submit" variant="danger">ابطال کل سری</x-admin.form.button>
                                    </x-admin.form>
                                    <div class="mt-1 text-[10px] text-amber-600">این سری به صورت یکپارچه مدیریت می شود.</div>
                                @else
                                    <x-admin.form method="post" action="{{ route('admin.draw-campaigns.tickets.invalidate',[$drawCampaign,$ticket]) }}" class="flex items-center gap-2 js-confirm" data-confirm-title="ابطال بلیط" data-confirm-text="بلیط {{ $ticket->code }} باطل شود؟"><x-admin.form.input name="reason" placeholder="دلیل ابطال" class="min-w-[120px]"/><x-admin.form.button type="submit" variant="danger">ابطال</x-admin.form.button></x-admin.form>
                                @endif
                            @elseif($ticket->invalidated_at)
                                <div class="text-[11px] leading-5 text-slate-500">{{ $ticket->invalidation_reason }}<br>{{ jalali_date($ticket->invalidated_at,'Y/m/d H:i') }}</div>
                            @else <span class="text-xs text-slate-400">غیرقابل ابطال</span> @endif
                        @endcan
                    </x-admin.list.td>
                </x-admin.list.tr>
            @empty <x-admin.list.empty title="بلیطی وجود ندارد" description="هنوز بلیطی برای این قرعه کشی صادر نشده است."/> @endforelse
        </x-admin.list.tbody>
    </x-admin.list.table>
    <div class="mt-4"><x-admin.list.pagination :model="$tickets"/></div>
@endsection
