<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox
                    :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($couponIds) . ')'"
                    :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($couponIds) . ')'"
                />
            </x-admin.list.th>

            <x-admin.list.th width="300px">کدتخفیف</x-admin.list.th>

            <x-admin.list.th width="160px">نوع و مقدار</x-admin.list.th>

            <x-admin.list.th width="160px">محدوده اعمال</x-admin.list.th>

            <x-admin.list.th width="150px">کاربران مجاز</x-admin.list.th>

            <x-admin.list.th width="180px">مصرف</x-admin.list.th>

            <x-admin.list.th width="150px">حداقل سفارش</x-admin.list.th>

            <x-admin.list.th width="180px">بازه زمانی</x-admin.list.th>

            <x-admin.list.th width="140px">وضعیت</x-admin.list.th>

            <x-admin.list.th
                width="130px"
                label="زمان ایجاد"
                sortable
                sortKey="created_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @if($coupons->isNotEmpty())
            @foreach($coupons as $coupon)
                @php
                    $typeValue = $coupon->type?->value ?? $coupon->type;
                    $statusValue = $coupon->status?->value ?? $coupon->status;

                    $isPercentType = in_array($typeValue, [
                        'percent',
                        'percentage',
                    ], true);

                    $discountValue = $isPercentType
                        ? rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') . '٪'
                        : number_format((int) $coupon->value) . ' تومان';

                    $statusColor = match($statusValue) {
                        'active' => 'green',
                        'inactive' => 'slate',
                        'expired' => 'red',
                        'scheduled' => 'blue',
                        'used_up' => 'yellow',
                        default => 'slate',
                    };

                    $typeColor = $isPercentType ? 'blue' : 'green';

                    $usedCount = (int) ($coupon->used_count ?? 0);
                    $activeReservationsCount = (int) ($coupon->active_reservations_count ?? 0);
                    $usageLimit = $coupon->usage_limit ? (int) $coupon->usage_limit : null;
                    $reservedAndUsedCount = $usedCount + $activeReservationsCount;
                    $remainingCount = $usageLimit
                        ? max($usageLimit - $reservedAndUsedCount, 0)
                        : null;
                @endphp

                <x-admin.list.tr
                    ::class="{
                        'bg-slate-50/90': isSelected({{ $coupon->id }})
                    }"
                >
                    <x-admin.list.td align="center">
                        <x-admin.list.bulk-checkbox
                            name="ids[]"
                            :value="$coupon->id"
                            x-bind:checked="isSelected({{ $coupon->id }})"
                            x-on:change="toggleSelection({{ $coupon->id }})"
                        />
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[230px] max-w-[300px]">
                            <div class="flex items-center gap-2">
                                <x-admin.badge
                                    color="dark"
                                    size="md"
                                    class="max-w-[170px] truncate font-bold tracking-wider"
                                    dir="ltr"
                                    title="{{ $coupon->code }}"
                                >
                                    {{ $coupon->code }}
                                </x-admin.badge>

                                <button
                                    type="button"
                                    x-data="{ copied: false }"
                                    x-on:click="
                                        navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($coupon->code) }});
                                        copied = true;
                                        setTimeout(() => copied = false, 1200);
                                    "
                                    class="cursor-pointer inline-flex h-6 items-center rounded-full border border-slate-200 bg-white px-2 text-[11px] font-medium text-slate-500 transition hover:border-brand-200 hover:text-brand-600"
                                >
                                    <span x-show="!copied">کپی</span>
                                    <span x-show="copied" x-cloak>کپی شد</span>
                                </button>
                            </div>

                            <a
                                href="{{ route('admin.coupons.edit', $coupon) }}"
                                class="mt-2 block truncate leading-6 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                                title="{{ $coupon->title ?: 'بدون عنوان' }}"
                            >
                                {{ $coupon->title ?: 'بدون عنوان' }}
                            </a>

                            @if($coupon->description)
                                <p
                                    class="mt-1 line-clamp-1 text-xs leading-6 text-slate-500"
                                    title="{{ $coupon->description }}"
                                >
                                    {{ $coupon->description }}
                                </p>
                            @endif

                            @if($coupon->is_floating_box_enabled)
                                <div class="mt-2">
                                    <x-admin.badge color="yellow" size="sm" class="whitespace-nowrap">
                                        باکس پایین سایت فعال است
                                    </x-admin.badge>
                                </div>
                            @endif

                            <x-admin.list.row-actions>
                                <x-admin.list.action
                                    tag="a"
                                    href="{{ route('admin.coupons.edit', $coupon) }}"
                                    variant="primary"
                                >
                                    ویرایش
                                </x-admin.list.action>

                                <span class="text-slate-300">|</span>

                                <x-admin.form
                                    action="{{ route('admin.coupons.destroy', $coupon) }}"
                                    method="delete"
                                    class="js-confirm"
                                    data-confirm-title="حذف کدتخفیف"
                                    data-confirm-text="آیا از حذف کدتخفیف «{{ $coupon->code }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست."
                                >
                                    <x-admin.list.action
                                        tag="button"
                                        type="submit"
                                        variant="danger"
                                    >
                                        حذف
                                    </x-admin.list.action>
                                </x-admin.form>
                            </x-admin.list.row-actions>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="space-y-2">
                            <x-admin.badge
                                :color="$typeColor"
                                size="md"
                                class="whitespace-nowrap"
                            >
                                {{ $coupon->type?->label() ?? 'نامشخص' }}
                            </x-admin.badge>

                            <div class="text-xs leading-6 text-slate-600">
                                <div class="whitespace-nowrap font-bold text-slate-700">
                                    {{ $discountValue }}
                                </div>

                                @if($coupon->max_discount_amount)
                                    <div class="whitespace-nowrap">
                                        سقف: {{ number_format((int) $coupon->max_discount_amount) }} تومان
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="space-y-2">
                            <x-admin.badge
                                color="slate"
                                size="md"
                                class="whitespace-nowrap"
                            >
                                {{ $coupon->scope?->label() ?? 'نامشخص' }}
                            </x-admin.badge>

                            @if(($coupon->targets_count ?? 0) > 0)
                                <div class="text-xs text-slate-500">
                                    {{ number_format($coupon->targets_count) }} هدف انتخاب شده
                                </div>
                            @else
                                <div class="text-xs text-slate-400">
                                    بدون هدف اختصاصی
                                </div>
                            @endif

                            @if($coupon->applies_to_addons)
                                <x-admin.badge
                                    color="blue"
                                    size="sm"
                                    class="whitespace-nowrap"
                                >
                                    شامل خدمات افزوده
                                </x-admin.badge>
                            @endif
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        @if($coupon->applies_to_all_users)
                            <x-admin.badge
                                color="green"
                                size="md"
                                class="whitespace-nowrap"
                            >
                                همه کاربران
                            </x-admin.badge>
                        @else
                            <div class="space-y-1">
                                <x-admin.badge
                                    color="yellow"
                                    size="md"
                                    class="whitespace-nowrap"
                                >
                                    کاربران خاص
                                </x-admin.badge>

                                <div class="text-xs text-slate-500">
                                    {{ number_format($coupon->users_count ?? 0) }} کاربر
                                </div>
                            </div>
                        @endif
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="space-y-1 text-xs leading-6 text-slate-600">
                            <div class="whitespace-nowrap">
                                مصرف شده:
                                <span class="font-bold text-slate-800">
                                    {{ number_format($usedCount) }}
                                </span>
                            </div>

                            @if($activeReservationsCount > 0)
                                <div class="whitespace-nowrap">
                                    رزرو فعال:
                                    <span class="font-bold text-amber-700">
                                        {{ number_format($activeReservationsCount) }}
                                    </span>
                                </div>
                            @else
                                <div class="whitespace-nowrap text-slate-400">
                                    رزرو فعال: 0
                                </div>
                            @endif

                            @if($usageLimit)
                                <div class="whitespace-nowrap">
                                    درگیر شده:
                                    <span class="font-bold text-slate-800">
                                        {{ number_format($reservedAndUsedCount) }}
                                    </span>
                                </div>

                                <div class="whitespace-nowrap">
                                    سقف کل:
                                    <span class="font-bold text-slate-800">
                                        {{ number_format($usageLimit) }}
                                    </span>
                                </div>

                                <div class="whitespace-nowrap">
                                    قابل استفاده:
                                    <span
                                        class="font-bold {{ $remainingCount > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ number_format($remainingCount) }}
                                    </span>
                                </div>
                            @else
                                <div class="text-slate-400">
                                    بدون محدودیت کل
                                </div>
                            @endif

                            @if($coupon->usage_limit_per_user)
                                <div class="whitespace-nowrap">
                                    هر کاربر:
                                    <span class="font-bold text-slate-800">
                                        {{ number_format((int) $coupon->usage_limit_per_user) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        @if($coupon->minimum_order_amount)
                            <x-admin.badge
                                color="slate"
                                size="md"
                                class="whitespace-nowrap"
                            >
                                {{ number_format((int) $coupon->minimum_order_amount) }} تومان
                            </x-admin.badge>
                        @else
                            <span class="text-xs text-slate-400">بدون حداقل سفارش</span>
                        @endif
                    </x-admin.list.td>

                    <x-admin.list.td muted>
                        <div class="space-y-1 text-xs leading-6">
                            @if($coupon->starts_at)
                                <div class="whitespace-nowrap">
                                    شروع: {{ jalali_date($coupon->starts_at, 'Y/m/d H:i') }}
                                </div>
                            @else
                                <div class="whitespace-nowrap text-slate-400">
                                    شروع: نامحدود
                                </div>
                            @endif

                            @if($coupon->expires_at)
                                <div class="whitespace-nowrap">
                                    پایان: {{ jalali_date($coupon->expires_at, 'Y/m/d H:i') }}
                                </div>
                            @else
                                <div class="whitespace-nowrap text-slate-400">
                                    پایان: نامحدود
                                </div>
                            @endif
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge
                            :color="$statusColor"
                            size="md"
                            class="whitespace-nowrap"
                        >
                            {{ $coupon->status?->label() ?? 'نامشخص' }}
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td muted>
                        <div class="text-xs leading-6">
                            <div>{{ jalali_date($coupon->created_at, 'Y/m/d') }}</div>
                            <div>{{ jalali_date($coupon->created_at, 'H:i') }}</div>
                        </div>
                    </x-admin.list.td>
                </x-admin.list.tr>
            @endforeach
        @else
            <x-admin.list.empty
                title="کدتخفیفی برای نمایش وجود ندارد"
                description="هنوز هیچ کدتخفیفی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                action-text="افزودن کدتخفیف جدید"
                :action-href="route('admin.coupons.create')"
            />
        @endif
    </x-admin.list.tbody>
</x-admin.list.table>
