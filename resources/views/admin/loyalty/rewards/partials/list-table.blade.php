@php
    use App\Domain\Loyalty\Enums\RewardTypeEnum;
    use App\Domain\Promotion\Enums\CouponTypeEnum;
    use App\Domain\Raffle\Models\DrawCampaign;
@endphp

<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox
                    :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($rewardOfferIds) . ')'"
                    :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($rewardOfferIds) . ')'"
                />
            </x-admin.list.th>
            <x-admin.list.th width="300px">پاداش</x-admin.list.th>
            <x-admin.list.th width="150px">نوع</x-admin.list.th>
            <x-admin.list.th width="280px">جزئیات</x-admin.list.th>
            <x-admin.list.th width="120px">هزینه</x-admin.list.th>
            <x-admin.list.th width="165px">محدودیت دریافت</x-admin.list.th>
            <x-admin.list.th width="150px">وضعیت</x-admin.list.th>
            <x-admin.list.th width="110px">دریافت موفق</x-admin.list.th>
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($rewardOffers as $rewardOffer)
            @php
                $isRaffle = $rewardOffer->type === RewardTypeEnum::RAFFLE_TICKET;
                $campaign = $isRaffle && $rewardOffer->target instanceof DrawCampaign ? $rewardOffer->target : null;
                $cfg = $rewardOffer->config ?? [];
                $now = now();

                if (!$rewardOffer->is_active) {
                    $statusLabel = 'غیرفعال'; $statusColor = 'slate';
                } elseif ($rewardOffer->starts_at && $rewardOffer->starts_at->gt($now)) {
                    $statusLabel = 'زمان بندی شده'; $statusColor = 'blue';
                } elseif ($rewardOffer->ends_at && $rewardOffer->ends_at->lt($now)) {
                    $statusLabel = 'پایان یافته'; $statusColor = 'red';
                } elseif ($rewardOffer->hasReachedGlobalLimit()) {
                    $statusLabel = 'تکمیل ظرفیت'; $statusColor = 'yellow';
                } elseif($isRaffle && !$campaign?->acceptsTicketsAt()) {
                    $statusLabel = $campaign ? $campaign->status->label() : 'هدف نامعتبر'; $statusColor = 'yellow';
                } else {
                    $statusLabel = 'قابل دریافت'; $statusColor = 'green';
                }
            @endphp

            <x-admin.list.tr ::class="{'bg-slate-50/90': isSelected({{ $rewardOffer->id }})}">
                <x-admin.list.td align="center">
                    <x-admin.list.bulk-checkbox name="ids[]" :value="$rewardOffer->id" x-bind:checked="isSelected({{ $rewardOffer->id }})" x-on:change="toggleSelection({{ $rewardOffer->id }})"/>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="min-w-[240px] max-w-[300px]">
                        <a href="{{ route('admin.reward-offers.edit', $rewardOffer) }}" class="block truncate text-sm font-bold leading-6 text-slate-800 transition hover:text-brand-600">{{ $rewardOffer->title }}</a>
                        <div class="mt-2 flex items-center gap-2">
                            <x-admin.badge color="dark" size="sm" dir="ltr">{{ $rewardOffer->code }}</x-admin.badge>
                            <span class="text-[11px] text-slate-400">#{{ $rewardOffer->id }}</span>
                        </div>
                        <x-admin.list.row-actions>
                            @can('loyalty-reward.edit')
                                <x-admin.list.action tag="a" href="{{ route('admin.reward-offers.edit', $rewardOffer) }}" variant="primary">ویرایش</x-admin.list.action>
                            @endcan
                            @can('loyalty-reward.delete')
                                @can('loyalty-reward.edit')<span class="text-slate-300">|</span>@endcan
                                <x-admin.form action="{{ route('admin.reward-offers.destroy', $rewardOffer) }}" method="delete" class="js-confirm" data-confirm-title="حذف پاداش" data-confirm-text="سوابق دریافت قبلی حفظ می شوند. آیا مطمئن هستید؟">
                                    <x-admin.list.action tag="button" type="submit" variant="danger">حذف</x-admin.list.action>
                                </x-admin.form>
                            @endcan
                        </x-admin.list.row-actions>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <x-admin.badge :color="$isRaffle ? 'yellow' : 'brand'" size="md">{{ $rewardOffer->type->label() }}</x-admin.badge>
                </x-admin.list.td>

                <x-admin.list.td>
                    @if($isRaffle)
                        <div class="min-w-[240px] space-y-1 text-xs leading-6 text-slate-600">
                            <div class="font-black text-slate-800">{{ number_format((int)($cfg['ticket_quantity'] ?? 0)) }} بلیط</div>
                            <div>{{ $campaign?->title ?? 'قرعه کشی حذف یا نامعتبر' }}</div>
                            @if($campaign)
                                <div class="text-[11px] text-slate-400">وضعیت Campaign: {{ $campaign->status->label() }}</div>
                            @endif
                        </div>
                    @else
                        <div class="min-w-[240px] space-y-1 text-xs leading-6 text-slate-600">
                            <div class="font-black text-slate-800">
                                @if(($cfg['coupon_type'] ?? null) === CouponTypeEnum::PERCENTAGE->value)
                                    {{ number_format((int)($cfg['coupon_value'] ?? 0)) }}٪ تخفیف
                                @else
                                    {{ number_format((int)($cfg['coupon_value'] ?? 0)) }} تومان تخفیف
                                @endif
                            </div>
                            @if((int)($cfg['minimum_order_amount'] ?? 0) > 0)
                                <div>حداقل خرید: {{ number_format((int)$cfg['minimum_order_amount']) }} تومان</div>
                            @endif
                        </div>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="text-lg font-black text-brand-700">{{ number_format($rewardOffer->points_cost) }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">امتیاز</div>
                </x-admin.list.td>

                <x-admin.list.td muted>
                    <div class="space-y-1 text-xs leading-6">
                        <div>کل: {{ $rewardOffer->redemption_limit ? number_format($rewardOffer->redemption_limit) : 'نامحدود' }}</div>
                        <div>هر کاربر: {{ $rewardOffer->redemption_limit_per_user ? number_format($rewardOffer->redemption_limit_per_user) : 'نامحدود' }}</div>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td><x-admin.badge :color="$statusColor" size="md">{{ $statusLabel }}</x-admin.badge></x-admin.list.td>

                <x-admin.list.td>
                    <div class="text-sm font-black text-slate-800">{{ number_format((int)$rewardOffer->redeemed_count) }}</div>
                    @if($rewardOffer->remainingRedemptions() !== null)
                        <div class="mt-1 text-[11px] text-slate-400">{{ number_format($rewardOffer->remainingRedemptions()) }} باقی مانده</div>
                    @endif
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty title="پاداشی برای نمایش وجود ندارد" description="هنوز پاداشی برای خرج کردن امتیاز کاربران تعریف نشده است." action-text="افزودن پاداش جدید" :action-href="route('admin.reward-offers.create')"/>
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
