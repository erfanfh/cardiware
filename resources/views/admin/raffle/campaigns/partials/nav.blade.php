@php($items = [
    ['label'=>'داشبورد','route'=>'admin.draw-campaigns.show','gate'=>'raffle-campaign.show'],
    ['label'=>'بلیط ها','route'=>'admin.draw-campaigns.tickets.index','gate'=>'raffle-ticket.list'],
    ['label'=>'شرکت کنندگان','route'=>'admin.draw-campaigns.participants.index','gate'=>'raffle-participant.list'],
    ['label'=>'اجرای قرعه کشی','route'=>'admin.draw-campaigns.draws.index','gate'=>'raffle-draw.list'],
])
<div class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
    @foreach($items as $item)
        @can($item['gate'])
            @php($active = request()->routeIs($item['route']))
            <a href="{{ route($item['route'], $drawCampaign) }}" class="inline-flex h-9 items-center rounded-xl px-3 text-xs font-extrabold transition {{ $active ? 'bg-brand-500 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $item['label'] }}</a>
        @endcan
    @endforeach
</div>
