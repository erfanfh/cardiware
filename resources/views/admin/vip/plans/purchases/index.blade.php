@extends('admin.layout')

@section('title', 'خریدهای پلن VIP «'.$plan->title.'»')

@section('content')
    <div class="space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg font-black text-slate-900">خریدهای پلن {{ $plan->title }}</h1>
                        <x-admin.badge color="dark" size="md" dir="ltr">{{ $plan->code }}</x-admin.badge>
                    </div>
                    <p class="mt-2 text-xs leading-6 text-slate-500">هر خرید یک ردیف مستقل دارد؛ خرید دوبارهٔ یک کاربر نیز جداگانه نمایش داده می‌شود.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-xl bg-brand-50 px-4 py-2 text-sm font-black text-brand-700">
                        {{ number_format($totalPurchases) }} خرید ثبت‌شده
                    </div>
                    <x-admin.form.button tag="a" href="{{ route('admin.vip-plans.index') }}" variant="secondary">بازگشت به پلن‌ها</x-admin.form.button>
                </div>
            </div>
        </section>

        @include('admin.vip.plans.purchases.partials.filters-panel')

        <div class="space-y-4">
            @include('admin.vip.plans.purchases.partials.list-table')
            <x-admin.list.pagination :model="$purchases"/>
        </div>
    </div>
@endsection
