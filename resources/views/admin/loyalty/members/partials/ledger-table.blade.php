<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="130px">نوع</x-admin.list.th>
            <x-admin.list.th width="145px">علت</x-admin.list.th>

            <x-admin.list.th
                width="135px"
                label="مقدار"
                sortable
                sortKey="amount"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />

            <x-admin.list.th width="180px">مانده حساب</x-admin.list.th>
            <x-admin.list.th width="250px">توضیحات / مرجع</x-admin.list.th>
            <x-admin.list.th width="150px">ثبت کننده</x-admin.list.th>

            <x-admin.list.th
                width="150px"
                label="زمان رخداد"
                sortable
                sortKey="occurred_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($entries as $entry)
            @php
                $isCredit = $entry->direction->isCredit();
                $reasonLabel = \App\Domain\Loyalty\Enums\EarningRuleTypeEnum::tryFrom($entry->reason)?->label()
                    ?? match($entry->reason) {
                        'manual_adjustment' => 'اصلاح دستی',
                        'reversal' => 'برگشت تراکنش',
                        default => $entry->reason,
                    };
                $occurredAt = $entry->occurred_at ?? $entry->created_at;
            @endphp

            <x-admin.list.tr>
                <x-admin.list.td>
                    <div class="space-y-2">
                        <x-admin.badge :color="$isCredit ? 'green' : 'red'" size="md">
                            {{ $entry->type->label() }}
                        </x-admin.badge>
                        <div class="text-[11px] font-bold {{ $isCredit ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $entry->direction->label() }}
                        </div>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="text-xs font-bold text-slate-700">{{ $reasonLabel }}</div>
                    @if($entry->earningRule)
                        <div class="mt-1 text-[11px] text-slate-400">{{ $entry->earningRule->title }}</div>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="text-base font-black {{ $isCredit ? 'text-emerald-700' : 'text-red-600' }}" dir="ltr">
                        {{ $isCredit ? '+' : '-' }}{{ number_format((int)$entry->amount) }}
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="flex items-center gap-2 text-xs" dir="ltr">
                        <span class="font-bold text-slate-500">{{ number_format((int)$entry->balance_before) }}</span>
                        <span class="text-slate-300">→</span>
                        <span class="font-black {{ (int)$entry->balance_after < 0 ? 'text-red-600' : 'text-slate-900' }}">
                            {{ number_format((int)$entry->balance_after) }}
                        </span>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="max-w-[240px] text-xs leading-6 text-slate-600">
                        @if($entry->description)
                            <div>{{ $entry->description }}</div>
                        @endif

                        @if($entry->reference_type && $entry->reference_id)
                            <div class="mt-1 text-[11px] text-slate-400" dir="ltr">
                                {{ class_basename($entry->reference_type) }} #{{ $entry->reference_id }}
                            </div>
                        @endif

                        @if($entry->reversal_of_id)
                            <div class="mt-1 text-[11px] text-amber-600">
                                برگشت از تراکنش #{{ $entry->reversal_of_id }}
                            </div>
                        @endif
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    @if($entry->actor)
                        <div class="text-xs font-bold text-slate-700">{{ $entry->actor->full_name }}</div>
                        <div class="mt-1 text-[11px] text-slate-400">#{{ $entry->actor->id }}</div>
                    @else
                        <x-admin.badge color="slate" size="sm">سیستم</x-admin.badge>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td muted>
                    <div class="text-xs leading-6">
                        <div>{{ jalali_date($occurredAt, 'Y/m/d') }}</div>
                        <div>{{ jalali_date($occurredAt, 'H:i') }}</div>
                    </div>
                    <div class="mt-1 max-w-[135px] truncate text-[10px] text-slate-300" dir="ltr" title="{{ $entry->public_token }}">
                        {{ $entry->public_token }}
                    </div>
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty
                title="تراکنش امتیازی وجود ندارد"
                description="این کاربر هنوز تراکنش امتیازی ندارد یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
