<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox
                    :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($orderIds) . ')'"
                    :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($orderIds) . ')'"
                />
            </x-admin.list.th>

            <x-admin.list.th width="140px">شماره سفارش</x-admin.list.th>

            <x-admin.list.th>کاربر</x-admin.list.th>

            <x-admin.list.th width="140px">وضعیت</x-admin.list.th>

            <x-admin.list.th width="130px">مبلغ کل</x-admin.list.th>

            <x-admin.list.th width="120px">تعداد آیتم</x-admin.list.th>

            <x-admin.list.th width="140px">تاریخ</x-admin.list.th>
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($orders as $order)
            @php
                $statusColor = match($order->status) {
                    \App\Domain\Commerce\Enums\OrderStatusEnum::PENDING_PAYMENT => 'yellow',
                    \App\Domain\Commerce\Enums\OrderStatusEnum::PAID => 'green',
                    \App\Domain\Commerce\Enums\OrderStatusEnum::FAILED => 'red',
                    \App\Domain\Commerce\Enums\OrderStatusEnum::CANCELLED => 'orange',
                    default => 'slate',
                };
            @endphp

            <x-admin.list.tr
                ::class="{
                    'bg-slate-50/90': isSelected({{ $order->id }})
                }"
            >

                {{-- checkbox --}}
                <x-admin.list.td align="center">
                    <x-admin.list.bulk-checkbox
                        name="ids[]"
                        :value="$order->id"
                        x-bind:checked="isSelected({{ $order->id }})"
                        x-on:change="toggleSelection({{ $order->id }})"
                    />
                </x-admin.list.td>

                {{-- order number --}}
                <x-admin.list.td>
                    <a
                        href="{{ route('admin.orders.edit', $order) }}"
                        class="font-bold text-sm text-slate-800 hover:text-brand-600 whitespace-nowrap"
                    >
                        {{ $order->order_number }}
                    </a>
                </x-admin.list.td>

                {{-- user --}}
                <x-admin.list.td>
                    <a href="{{ route('admin.orders.edit', $order) }}">
                        @if($order->user)
                            <div class="text-sm font-medium text-slate-800">
                                {{ $order->user->first_name . ' ' . $order->user->last_name }}
                            </div>

                            <div class="text-xs text-slate-400">
                                {{ ($order->user->email ? $order->user->email . ' | ' : '') . $order->user->mobile }}
                            </div>
                        @else
                            <div class="text-xs italic text-slate-600">
                                (کاربر حذف شده)
                            </div>
                        @endif
                    </a>
                </x-admin.list.td>

                {{-- status --}}
                <x-admin.list.td>
                    <a href="{{ route('admin.orders.edit', $order) }}">
                        <x-admin.badge :color="$statusColor" size="md">
                            {{ $order->status?->label() ?? 'نامشخص' }}
                        </x-admin.badge>
                    </a>
                </x-admin.list.td>

                {{-- total --}}
                <x-admin.list.td>
                    <a href="{{ route('admin.orders.edit', $order) }}">
                        <div class="font-bold text-slate-800 whitespace-nowrap">
                            {{ number_format($order->total_amount) }}
                            <span class="text-[0.7rem] font-normal">تومان</span>
                        </div>

                        @if($order->discount_amount > 0)
                            <div class="text-xs text-red-500">
                                - {{ number_format($order->discount_amount) }}
                            </div>
                        @endif
                        @if((int) $order->tax_amount > 0)
                            <div class="mt-1 text-[11px] font-bold text-amber-700">
                                + {{ number_format($order->tax_amount) }} تومان مالیات
                            </div>
                        @endif
                    </a>
                </x-admin.list.td>

                {{-- items count --}}
                <x-admin.list.td>
                    <a href="{{ route('admin.orders.edit', $order) }}">
                        <x-admin.badge size="md" color="slate">
                            {{ $order->items_count ?? $order->items->count() }}
                        </x-admin.badge>
                    </a>
                </x-admin.list.td>

                {{-- date --}}
                <x-admin.list.td muted>
                    <div class="text-xs">
                        <div>{{ jalali_date($order->created_at, 'Y/m/d') }}</div>
                        <div>{{ jalali_date($order->created_at, 'H:i') }}</div>
                    </div>
                </x-admin.list.td>

            </x-admin.list.tr>

        @empty
            <x-admin.list.empty
                title="سفارشی وجود ندارد"
                description="هنوز هیچ سفارشی ثبت نشده یا فیلترها نتیجه‌ای نداشتند."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
