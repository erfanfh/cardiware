@extends('admin.layout')

@section('title', 'لیست خریداران')

@section('content')
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700 [&_svg]:h-5 [&_svg]:w-5">
                        @include('icons.user-multiple-02')
                    </div>
                    <div>
                        <h1 class="text-base font-black text-slate-900">لیست خریداران</h1>
                        <p class="mt-1 text-sm leading-7 text-slate-500">
                            خریدهای پرداخت‌شده به تفکیک خریدار و محصول؛ مبالغ و اطلاعات مالی در این صفحه نمایش داده نمی‌شود.
                        </p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.buyers.index') }}" class="grid grid-cols-1 gap-4 p-5 md:grid-cols-12 md:items-end">
                <x-admin.field label="خریدار یا شماره سفارش" class="md:col-span-4">
                    <x-admin.form.input name="q" :value="$filters['q'] ?? ''" placeholder="نام، موبایل، ایمیل یا شماره سفارش" />
                </x-admin.field>
                <x-admin.field label="محصول" class="md:col-span-3">
                    <x-admin.form.input name="product" :value="$filters['product'] ?? ''" placeholder="عنوان محصول یا ماد" />
                </x-admin.field>
                <x-admin.field label="از تاریخ (میلادی)" class="md:col-span-2">
                    <x-admin.form.input type="date" name="from" :value="$filters['from'] ?? ''" class="direction-ltr" />
                </x-admin.field>
                <x-admin.field label="تا تاریخ (میلادی)" class="md:col-span-2">
                    <x-admin.form.input type="date" name="to" :value="$filters['to'] ?? ''" class="direction-ltr" />
                </x-admin.field>
                <div class="md:col-span-1">
                    <x-admin.form.button type="submit" variant="dark" block>جستجو</x-admin.form.button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if($items->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-slate-500">خریدی با این مشخصات پیدا نشد.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-right text-sm">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">خریدار</th>
                            <th scope="col" class="px-4 py-3">موبایل</th>
                            <th scope="col" class="px-4 py-3">محصول / آیتم</th>
                            <th scope="col" class="px-4 py-3">تعداد</th>
                            <th scope="col" class="px-4 py-3">سفارش</th>
                            <th scope="col" class="px-4 py-3">تاریخ پرداخت</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach($items as $item)
                            @php($buyer = $item->order?->user)
                            <tr>
                                <td class="px-5 py-3 font-bold text-slate-900">
                                    {{ $buyer ? ($buyer->display_name ?: trim($buyer->first_name.' '.$buyer->last_name) ?: 'کاربر #'.$buyer->id) : 'کاربر حذف‌شده' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600" dir="ltr">{{ $buyer?->mobile ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800">{{ $item->buyer_item_title }}</div>
                                    @if($item->product && $item->product->title !== $item->buyer_item_title)
                                        <div class="mt-0.5 text-xs text-slate-500">{{ $item->product->title }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ number_format((int)$item->quantity) }}</td>
                                <td class="px-4 py-3 font-bold" dir="ltr">#{{ $item->order?->order_number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $item->order?->paid_at ? jalali_date($item->order->paid_at, 'Y/m/d H:i') : '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if($items->hasPages())
                    <div class="border-t border-slate-100 p-5">{{ $items->links() }}</div>
                @endif
            @endif
        </section>
    </div>
@endsection
