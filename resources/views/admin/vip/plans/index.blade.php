@extends('admin.layout')

@section('title', 'پلن‌های VIP')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-800">پلن‌های VIP</h1>
                <p class="mt-1 text-sm text-slate-500">مدت، قیمت و وضعیت پلن‌های اشتراک را مدیریت کنید.</p>
            </div>
            @can('vip-plan.create')
                <x-admin.form.button tag="a" href="{{ route('admin.vip-plans.create') }}">ایجاد پلن جدید</x-admin.form.button>
            @endcan
        </div>

        <x-admin.list.table>
            <x-admin.list.thead>
                <tr>
                    <x-admin.list.th>پلن</x-admin.list.th>
                    <x-admin.list.th>مدت اعتبار</x-admin.list.th>
                    <x-admin.list.th>قیمت</x-admin.list.th>
                    <x-admin.list.th>وضعیت</x-admin.list.th>
                    <x-admin.list.th>ترتیب</x-admin.list.th>
                    <x-admin.list.th>عملیات</x-admin.list.th>
                </tr>
            </x-admin.list.thead>
            <x-admin.list.tbody>
                @forelse($plans as $plan)
                    <x-admin.list.tr>
                        <x-admin.list.td>
                            <div class="font-bold text-slate-800">{{ $plan->title }}</div>
                            <div class="mt-1 text-xs text-slate-500" dir="ltr">{{ $plan->code }}</div>
                        </x-admin.list.td>
                        <x-admin.list.td>{{ number_format($plan->duration_days) }} روز</x-admin.list.td>
                        <x-admin.list.td>{{ number_format($plan->price_toman) }} تومان</x-admin.list.td>
                        <x-admin.list.td>
                            <x-admin.badge :color="$plan->is_active ? 'green' : 'slate'">
                                {{ $plan->is_active ? 'فعال' : 'غیرفعال' }}
                            </x-admin.badge>
                        </x-admin.list.td>
                        <x-admin.list.td>{{ $plan->sort_order }}</x-admin.list.td>
                        <x-admin.list.td>
                            <div class="flex items-center gap-3">
                                @can('vip-plan.edit')
                                    <a class="text-sm font-semibold text-brand-600 hover:underline" href="{{ route('admin.vip-plans.edit', $plan) }}">ویرایش</a>
                                @endcan
                                @can('vip-plan.delete')
                                    <x-admin.form action="{{ route('admin.vip-plans.destroy', $plan) }}" method="delete"
                                                  data-confirm-title="حذف پلن VIP"
                                                  data-confirm-text="پلن «{{ $plan->title }}» حذف شود؟">
                                        <button type="submit" class="text-sm font-semibold text-rose-600 hover:underline">حذف</button>
                                    </x-admin.form>
                                @endcan
                            </div>
                        </x-admin.list.td>
                    </x-admin.list.tr>
                @empty
                    <x-admin.list.tr>
                        <x-admin.list.td colspan="6" class="py-10 text-center text-slate-500">هنوز پلن VIP تعریف نشده است.</x-admin.list.td>
                    </x-admin.list.tr>
                @endforelse
            </x-admin.list.tbody>
        </x-admin.list.table>

        <x-admin.list.pagination :model="$plans"/>
    </div>
@endsection
