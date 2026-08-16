<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox
                    :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($earningRuleIds) . ')'"
                    :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($earningRuleIds) . ')'"
                />
            </x-admin.list.th>

            <x-admin.list.th width="290px">قانون</x-admin.list.th>
            <x-admin.list.th width="150px">نوع</x-admin.list.th>
            <x-admin.list.th width="260px">تنظیمات</x-admin.list.th>

            <x-admin.list.th
                width="110px"
                label="اولویت"
                sortable
                sortKey="priority"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />

            <x-admin.list.th width="190px">بازه اجرا</x-admin.list.th>
            <x-admin.list.th width="130px">وضعیت</x-admin.list.th>
            <x-admin.list.th width="120px">دفعات امتیازدهی</x-admin.list.th>

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
        @if($earningRules->isNotEmpty())
            @foreach($earningRules as $earningRule)
                @php
                    $config = $earningRule->config ?? [];
                    $typeValue = $earningRule->type?->value ?? $earningRule->type;
                    $now = now();

                    if (!$earningRule->is_active) {
                        $effectiveStatus = 'inactive';
                        $effectiveStatusLabel = 'غیرفعال';
                        $effectiveStatusColor = 'slate';
                    } elseif ($earningRule->starts_at && $earningRule->starts_at->greaterThan($now)) {
                        $effectiveStatus = 'scheduled';
                        $effectiveStatusLabel = 'زمان‌بندی شده';
                        $effectiveStatusColor = 'blue';
                    } elseif ($earningRule->ends_at && $earningRule->ends_at->lessThan($now)) {
                        $effectiveStatus = 'expired';
                        $effectiveStatusLabel = 'پایان یافته';
                        $effectiveStatusColor = 'red';
                    } else {
                        $effectiveStatus = 'active';
                        $effectiveStatusLabel = 'در حال اجرا';
                        $effectiveStatusColor = 'green';
                    }

                    $typeColor = match($typeValue) {
                        'purchase' => 'green',
                        'comment' => 'blue',
                        'daily_visit' => 'yellow',
                        'membership_anniversary' => 'brand',
                        default => 'slate',
                    };
                @endphp

                <x-admin.list.tr
                    ::class="{
                        'bg-slate-50/90': isSelected({{ $earningRule->id }})
                    }"
                >
                    <x-admin.list.td align="center">
                        <x-admin.list.bulk-checkbox
                            name="ids[]"
                            :value="$earningRule->id"
                            x-bind:checked="isSelected({{ $earningRule->id }})"
                            x-on:change="toggleSelection({{ $earningRule->id }})"
                        />
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[230px] max-w-[290px]">
                            <a
                                href="{{ route('admin.earning-rules.edit', $earningRule) }}"
                                class="block truncate text-sm font-bold leading-6 text-slate-800 transition hover:text-brand-600"
                                title="{{ $earningRule->title }}"
                            >
                                {{ $earningRule->title }}
                            </a>

                            <div class="mt-2 flex items-center gap-2">
                                <x-admin.badge
                                    color="dark"
                                    size="sm"
                                    class="max-w-[180px] truncate font-bold tracking-wider"
                                    dir="ltr"
                                    title="{{ $earningRule->code }}"
                                >
                                    {{ $earningRule->code }}
                                </x-admin.badge>

                                <span class="text-[11px] text-slate-400">#{{ $earningRule->id }}</span>
                            </div>

                            <x-admin.list.row-actions>
                                <x-admin.list.action
                                    tag="a"
                                    href="{{ route('admin.earning-rules.edit', $earningRule) }}"
                                    variant="primary"
                                >
                                    ویرایش
                                </x-admin.list.action>

                                <span class="text-slate-300">|</span>

                                <x-admin.form
                                    action="{{ route('admin.earning-rules.destroy', $earningRule) }}"
                                    method="delete"
                                    class="js-confirm"
                                    data-confirm-title="حذف قانون امتیاز"
                                    data-confirm-text="آیا از حذف قانون «{{ $earningRule->title }}» مطمئن هستید؟ تاریخچه امتیازات قبلی حفظ می‌شود."
                                >
                                    <x-admin.list.action tag="button" type="submit" variant="danger">
                                        حذف
                                    </x-admin.list.action>
                                </x-admin.form>
                            </x-admin.list.row-actions>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge :color="$typeColor" size="md" class="whitespace-nowrap">
                            {{ $earningRule->type?->label() ?? 'نامشخص' }}
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[220px] space-y-1 text-xs leading-6 text-slate-600">
                            @if($typeValue === 'purchase')
                                <div>
                                    هر
                                    <span class="font-bold text-slate-800">{{ number_format((int)($config['amount_step'] ?? 0)) }}</span>
                                    تومان
                                    <span class="mx-1 text-slate-300">←</span>
                                    <span class="font-bold text-emerald-700">{{ number_format((int)($config['points'] ?? 0)) }} امتیاز</span>
                                </div>

                                @if((int)($config['minimum_order_amount'] ?? 0) > 0)
                                    <div>حداقل سفارش: {{ number_format((int)$config['minimum_order_amount']) }} تومان</div>
                                @endif

                                @if(!empty($config['max_points_per_order']))
                                    <div>سقف هر سفارش: {{ number_format((int)$config['max_points_per_order']) }} امتیاز</div>
                                @endif
                            @elseif($typeValue === 'comment')
                                <div class="font-bold text-slate-800">
                                    {{ number_format((int)($config['points'] ?? 0)) }} امتیاز برای هر دیدگاه تاییدشده
                                </div>
                                <div>حداکثر روزانه: {{ number_format((int)($config['daily_limit'] ?? 0)) }} دیدگاه</div>
                                <div>
                                    پاسخ‌ها:
                                    {{ !empty($config['include_replies']) ? 'محاسبه می‌شوند' : 'محاسبه نمی‌شوند' }}
                                </div>
                            @elseif($typeValue === 'membership_anniversary')
                                <div class="font-bold text-slate-800">
                                    {{ number_format((int)($config['points'] ?? 0)) }} امتیاز
                                </div>
                                <div>
                                    از سالگرد {{ number_format((int)($config['minimum_membership_years'] ?? 1)) }} سالگی عضویت
                                </div>
                            @else
                                <div class="font-bold text-slate-800">
                                    {{ number_format((int)($config['points'] ?? 0)) }} امتیاز
                                </div>
                            @endif
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge color="slate" size="md">
                            {{ number_format((int)$earningRule->priority) }}
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td muted>
                        <div class="space-y-1 text-xs leading-6">
                            @if($earningRule->starts_at)
                                <div class="whitespace-nowrap">شروع: {{ jalali_date($earningRule->starts_at, 'Y/m/d H:i') }}</div>
                            @else
                                <div class="whitespace-nowrap text-slate-400">شروع: نامحدود</div>
                            @endif

                            @if($earningRule->ends_at)
                                <div class="whitespace-nowrap">پایان: {{ jalali_date($earningRule->ends_at, 'Y/m/d H:i') }}</div>
                            @else
                                <div class="whitespace-nowrap text-slate-400">پایان: نامحدود</div>
                            @endif
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="space-y-2">
                            <x-admin.badge :color="$effectiveStatusColor" size="md" class="whitespace-nowrap">
                                {{ $effectiveStatusLabel }}
                            </x-admin.badge>

                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="text-sm font-bold text-slate-800">
                            {{ number_format((int)($earningRule->entries_count ?? 0)) }}
                        </div>
                        <div class="mt-1 text-[11px] text-slate-400">ثبت امتیاز</div>
                    </x-admin.list.td>

                    <x-admin.list.td muted>
                        <div class="text-xs leading-6">
                            <div>{{ jalali_date($earningRule->created_at, 'Y/m/d') }}</div>
                            <div>{{ jalali_date($earningRule->created_at, 'H:i') }}</div>
                        </div>
                    </x-admin.list.td>
                </x-admin.list.tr>
            @endforeach
        @else
            <x-admin.list.empty
                title="قانون امتیازی برای نمایش وجود ندارد"
                description="هنوز هیچ قانون کسب امتیازی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                action-text="افزودن قانون جدید"
                :action-href="route('admin.earning-rules.create')"
            />
        @endif
    </x-admin.list.tbody>
</x-admin.list.table>
