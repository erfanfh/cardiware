<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="90px" label="شناسه" sortable sortKey="id" :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
            <x-admin.list.th width="240px">کاربر</x-admin.list.th>
            <x-admin.list.th width="155px">سفارش</x-admin.list.th>
            <x-admin.list.th width="145px" label="شروع اشتراک" sortable sortKey="starts_at" :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
            <x-admin.list.th width="145px" label="پایان اشتراک" sortable sortKey="expires_at" :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
            <x-admin.list.th width="100px" label="مدت" sortable sortKey="duration_days" :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
            <x-admin.list.th width="120px">وضعیت</x-admin.list.th>
            <x-admin.list.th width="145px" label="زمان خرید" sortable sortKey="created_at" :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($purchases as $purchase)
            @php
                $user = $purchase->user;
                $order = $purchase->order;
                $userName = $user ? ($user->display_name ?: trim($user->first_name.' '.$user->last_name)) : null;
                $status = $purchase->revoked_at ? 'revoked' : ($purchase->starts_at->isFuture() ? 'scheduled' : ($purchase->expires_at->isPast() ? 'expired' : 'active'));
                $statusLabels = ['active' => 'فعال', 'scheduled' => 'در انتظار شروع', 'expired' => 'پایان‌یافته', 'revoked' => 'لغوشده'];
                $statusColors = ['active' => 'green', 'scheduled' => 'blue', 'expired' => 'slate', 'revoked' => 'red'];
            @endphp
            <x-admin.list.tr>
                <x-admin.list.td muted><span class="text-xs font-bold">#{{ $purchase->id }}</span></x-admin.list.td>

                <x-admin.list.td>
                    @if($user)
                        <div class="min-w-[200px]">
                            @if(!$user->trashed() && auth()->user()?->can('user.edit'))
                                <a href="{{ route('admin.users.edit', $user) }}" class="block truncate text-sm font-extrabold text-slate-800 transition hover:text-brand-600">{{ $userName ?: $user->username }}</a>
                            @else
                                <div class="truncate text-sm font-extrabold text-slate-800">{{ $userName ?: $user->username }}</div>
                            @endif
                            <div class="mt-1 flex flex-wrap gap-x-2 text-[11px] text-slate-500">
                                <span>#{{ $user->id }}</span>
                                @if($user->mobile)<span dir="ltr">{{ $user->mobile }}</span>@endif
                                @if($user->trashed())<span class="font-bold text-rose-500">حذف‌شده</span>@endif
                            </div>
                            @if($user->email)<div class="mt-1 truncate text-[11px] text-slate-400" dir="ltr">{{ $user->email }}</div>@endif
                        </div>
                    @else
                        <span class="text-xs text-slate-500">کاربر در دسترس نیست</span>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td>
                    @if($order)
                        @can('order.edit')
                            <a href="{{ route('admin.orders.edit', $order) }}" class="whitespace-nowrap text-sm font-extrabold text-brand-700 transition hover:text-brand-800" dir="ltr">{{ $order->order_number }}</a>
                        @else
                            <span class="whitespace-nowrap text-sm font-bold text-slate-700" dir="ltr">{{ $order->order_number }}</span>
                        @endcan
                    @else
                        <span class="text-xs text-slate-400">سفارش در دسترس نیست</span>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="whitespace-nowrap text-xs font-bold text-slate-700">{{ jalali_date($purchase->starts_at, 'j F Y') }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">ساعت {{ jalali_date($purchase->starts_at, 'H:i') }}</div>
                </x-admin.list.td>
                <x-admin.list.td>
                    <div class="whitespace-nowrap text-xs font-bold text-slate-700">{{ jalali_date($purchase->expires_at, 'j F Y') }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">ساعت {{ jalali_date($purchase->expires_at, 'H:i') }}</div>
                </x-admin.list.td>
                <x-admin.list.td>{{ number_format($purchase->duration_days) }} روز</x-admin.list.td>
                <x-admin.list.td><x-admin.badge :color="$statusColors[$status]" size="md">{{ $statusLabels[$status] }}</x-admin.badge></x-admin.list.td>
                <x-admin.list.td muted>
                    <div class="whitespace-nowrap text-xs font-bold text-slate-600">{{ jalali_date($purchase->created_at, 'j F Y') }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">ساعت {{ jalali_date($purchase->created_at, 'H:i') }}</div>
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty title="خریدی برای این پلن پیدا نشد" description="هنوز خریدی ثبت نشده یا فیلترهای فعلی نتیجه‌ای ندارند."/>
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
