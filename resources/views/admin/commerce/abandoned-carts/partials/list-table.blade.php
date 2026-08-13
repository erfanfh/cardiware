<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="90px">شناسه</x-admin.list.th>
            <x-admin.list.th>کاربر</x-admin.list.th>
            <x-admin.list.th>آیتم های سبد</x-admin.list.th>
            <x-admin.list.th width="145px">مبلغ کل</x-admin.list.th>
            <x-admin.list.th width="145px">آخرین تغییر</x-admin.list.th>
            <x-admin.list.th width="155px">وضعیت پاکسازی</x-admin.list.th>
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($carts as $cart)
            @php
                $isGuest = $cart->user_id === null;

                $readyForCleanup = $isGuest
                    ? $cart->updated_at->lte($guestCleanupCutoff)
                    : $cart->updated_at->lte($userCleanupCutoff);

                $cleanupAt = $isGuest
                    ? $cart->updated_at->copy()->addDays($guestRetentionDays)
                    : $cart->updated_at->copy()->addMonthsNoOverflow($userRetentionMonths);

                $rootItems = $cart->rootItems;
            @endphp

            <x-admin.list.tr>
                <x-admin.list.td>
                    <div class="font-black text-slate-800">#{{ $cart->id }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">
                        {{ $isGuest ? 'مهمان' : 'عضو' }}
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    @if($cart->user)
                        <div class="text-sm font-bold text-slate-800">
                            {{ trim($cart->user->first_name . ' ' . $cart->user->last_name) ?: ($cart->user->display_name ?: 'کاربر سایت') }}
                        </div>
                        <div class="mt-1 text-xs leading-6 text-slate-400">
                            @if($cart->user->mobile)
                                <span>{{ $cart->user->mobile }}</span>
                            @endif
                            @if($cart->user->mobile && $cart->user->email)
                                <span class="px-1">|</span>
                            @endif
                            @if($cart->user->email)
                                <span>{{ $cart->user->email }}</span>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <x-admin.badge color="slate" size="md">کاربر مهمان</x-admin.badge>
                        </div>

                        @if($cart->session_id)
                            <div
                                class="mt-1 max-w-[240px] truncate text-[11px] text-slate-400"
                                title="{{ $cart->session_id }}"
                                dir="ltr"
                            >
                                {{ $cart->session_id }}
                            </div>
                        @endif
                    @endif
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="space-y-1.5">
                        @foreach($rootItems->take(3) as $item)
                            @php
                                $itemTitle = data_get($item->snapshot, 'product_title')
                                    ?? data_get($item->snapshot, 'title')
                                    ?? 'آیتم سبد خرید';
                                $variantTitle = data_get($item->snapshot, 'product_sale_item_title');
                            @endphp

                            <div class="flex items-center gap-2 text-xs leading-6 text-slate-700">
                                <span class="min-w-0 truncate font-bold" title="{{ $itemTitle }}">
                                    {{ $itemTitle }}
                                </span>

                                @if($variantTitle && $variantTitle !== $itemTitle)
                                    <span class="shrink-0 text-[11px] text-slate-400">({{ $variantTitle }})</span>
                                @endif

                                <span class="shrink-0 rounded-lg bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500">
                                    × {{ $item->quantity }}
                                </span>
                            </div>
                        @endforeach

                        @if($rootItems->count() > 3)
                            <div class="text-[11px] font-bold text-slate-400">
                                + {{ $rootItems->count() - 3 }} آیتم دیگر
                            </div>
                        @endif

                        <div class="pt-0.5 text-[11px] text-slate-400">
                            مجموع رکوردها: {{ $cart->items_count }}
                        </div>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="whitespace-nowrap font-black text-slate-800">
                        {{ number_format($cart->total_amount) }}
                        <span class="text-[11px] font-normal text-slate-500">تومان</span>
                    </div>

                    @if($cart->discount_amount > 0)
                        <div class="mt-1 text-[11px] text-rose-500">
                            {{ number_format($cart->discount_amount) }} تومان تخفیف
                        </div>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td muted>
                    <div class="text-xs leading-6">
                        <div>{{ jalali_date($cart->updated_at, 'Y/m/d') }}</div>
                        <div>{{ jalali_date($cart->updated_at, 'H:i') }}</div>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    @if($readyForCleanup)
                        <x-admin.badge color="red" size="md">آماده پاکسازی</x-admin.badge>
                    @else
                        <x-admin.badge color="green" size="md">در حال نگهداری</x-admin.badge>
                        <div class="mt-1 text-[10px] leading-5 text-slate-400">
                            حذف پس از {{ jalali_date($cleanupAt, 'Y/m/d') }}
                        </div>
                    @endif

                    <div class="mt-1 text-[10px] text-slate-400">
                        بازه: {{ $isGuest ? $guestRetentionDays . ' روز' : $userRetentionMonths . ' ماه' }}
                    </div>
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty
                title="سبد خریدی وجود ندارد"
                description="در حال حاضر هیچ سبد خرید ACTIVE دارای آیتم که هنوز وارد مرحله Checkout نشده باشد وجود ندارد."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
