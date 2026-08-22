<x-admin.metabox title="روش‌های دریافت بلیط" :collapsible="true">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="text-sm font-black text-slate-800">پاداش‌های امتیازی متصل به این کمپین</div>
            <p class="mt-1 text-xs leading-6 text-slate-500">کاربر از صفحه قرعه‌کشی امتیاز خرج می‌کند و صدور بلیط با کنترل ظرفیت و سقف هر کاربر انجام می‌شود.</p>
        </div>
        @can('loyalty-reward.create')
            <x-admin.form.button tag="a" href="{{ route('admin.reward-offers.create', ['type' => 'raffle_ticket', 'target_id' => $drawCampaign->id]) }}" variant="solid">ساخت روش دریافت با امتیاز</x-admin.form.button>
        @endcan
    </div>

    <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
        @forelse($raffleRewardOffers as $offer)
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-black text-slate-800">{{ $offer->title }}</div>
                        <div class="mt-1 text-[11px] text-slate-500">{{ number_format($offer->points_cost) }} امتیاز · {{ number_format((int) data_get($offer->config, 'ticket_quantity', 1)) }} بلیط</div>
                    </div>
                    <x-admin.badge :color="$offer->is_active ? 'green' : 'slate'" size="md">{{ $offer->is_active ? 'فعال' : 'غیرفعال' }}</x-admin.badge>
                </div>
                <div class="mt-3 flex items-center justify-between text-[10px] text-slate-400">
                    <span>{{ number_format($offer->redemptions_count) }} بار دریافت</span>
                    @can('loyalty-reward.edit')
                        <a href="{{ route('admin.reward-offers.edit', $offer) }}" class="font-extrabold text-brand-600">ویرایش پاداش</a>
                    @endcan
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-5 text-center text-xs leading-7 text-slate-500 lg:col-span-2">هنوز روش دریافت امتیازی برای این کمپین تعریف نشده است. صدور دستی بلیط همچنان از بخش بلیط‌ها در دسترس است.</div>
        @endforelse
    </div>
</x-admin.metabox>
