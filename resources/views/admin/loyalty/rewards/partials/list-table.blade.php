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
            <x-admin.list.th width="250px">جزئیات جایزه</x-admin.list.th>
            <x-admin.list.th width="130px" label="هزینه" sortable sortKey="points_cost" :current-sort="request('sort')" :current-direction="request('direction', 'asc')"/>
            <x-admin.list.th width="180px">محدودیت دریافت</x-admin.list.th>
            <x-admin.list.th width="180px">بازه اجرا</x-admin.list.th>
            <x-admin.list.th width="120px">وضعیت</x-admin.list.th>
            <x-admin.list.th width="120px" label="دریافت شده" sortable sortKey="redeemed_count" :current-sort="request('sort')" :current-direction="request('direction', 'asc')"/>
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($rewardOffers as $rewardOffer)
            @php
                $config = $rewardOffer->config ?? [];
                $couponType = $config['coupon_type'] ?? 'fixed_amount';
                $now = now();

                if (!$rewardOffer->is_active) {
                    $statusLabel = 'غیرفعال'; $statusColor = 'slate';
                } elseif ($rewardOffer->starts_at && $rewardOffer->starts_at->gt($now)) {
                    $statusLabel = 'زمان‌بندی شده'; $statusColor = 'blue';
                } elseif ($rewardOffer->ends_at && $rewardOffer->ends_at->lt($now)) {
                    $statusLabel = 'پایان یافته'; $statusColor = 'red';
                } elseif ($rewardOffer->hasReachedGlobalLimit()) {
                    $statusLabel = 'تکمیل ظرفیت'; $statusColor = 'yellow';
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
                        <a href="{{ route('admin.reward-offers.edit', $rewardOffer) }}" class="block truncate text-sm font-bold leading-6 text-slate-800 transition hover:text-brand-600">
                            {{ $rewardOffer->title }}
                        </a>
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
                                <x-admin.form action="{{ route('admin.reward-offers.destroy', $rewardOffer) }}" method="delete" class="js-confirm" data-confirm-title="حذف پاداش" data-confirm-text="آیا از حذف پاداش «{{ $rewardOffer->title }}» مطمئن هستید؟ سوابق دریافت کاربران حفظ می‌شود.">
                                    <x-admin.list.action tag="button" type="submit" variant="danger">حذف</x-admin.list.action>
                                </x-admin.form>
                            @endcan
                        </x-admin.list.row-actions>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td><x-admin.badge color="brand" size="md">{{ $rewardOffer->type->label() }}</x-admin.badge></x-admin.list.td>

                <x-admin.list.td>
                    <div class="min-w-[220px] space-y-1 text-xs leading-6 text-slate-600">
                        <div class="font-bold text-slate-800">
                            @if($couponType === 'percentage')
                                {{ number_format((int)($config['coupon_value'] ?? 0)) }}٪ تخفیف
                            @else
                                {{ number_format((int)($config['coupon_value'] ?? 0)) }} تومان تخفیف
                            @endif
                        </div>
                        @if($couponType === 'percentage' && !empty($config['max_discount_amount']))
                            <div>سقف تخفیف: {{ number_format((int)$config['max_discount_amount']) }} تومان</div>
                        @endif
                        @if((int)($config['minimum_order_amount'] ?? 0) > 0)
                            <div>حداقل خرید: {{ number_format((int)$config['minimum_order_amount']) }} تومان</div>
                        @endif
                        <div>اعتبار: {{ !empty($config['expires_after_days']) ? number_format((int)$config['expires_after_days']) . ' روز' : 'بدون انقضا' }}</div>
                    </div>
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

                <x-admin.list.td muted>
                    <div class="space-y-1 text-xs leading-6">
                        <div>{{ $rewardOffer->starts_at ? 'شروع: ' . jalali_date($rewardOffer->starts_at, 'Y/m/d H:i') : 'شروع: نامحدود' }}</div>
                        <div>{{ $rewardOffer->ends_at ? 'پایان: ' . jalali_date($rewardOffer->ends_at, 'Y/m/d H:i') : 'پایان: نامحدود' }}</div>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td><x-admin.badge :color="$statusColor" size="md">{{ $statusLabel }}</x-admin.badge></x-admin.list.td>

                <x-admin.list.td>
                    <div class="text-sm font-black text-slate-800">{{ number_format((int)$rewardOffer->redeemed_count) }}</div>
                    @if($rewardOffer->remainingRedemptions() !== null)
                        <div class="mt-1 text-[11px] text-slate-400">{{ number_format($rewardOffer->remainingRedemptions()) }} باقی‌مانده</div>
                    @endif
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty
                title="پاداشی برای نمایش وجود ندارد"
                description="هنوز پاداشی برای خرج کردن امتیاز کاربران تعریف نشده است."
                action-text="افزودن پاداش جدید"
                :action-href="route('admin.reward-offers.create')"
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
