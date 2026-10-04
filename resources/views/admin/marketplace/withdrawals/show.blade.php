@extends('admin.layout')

@section('title', 'بررسی درخواست وجه فروشگاه')

@section('content')
    <x-admin.page>
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-orange-100 bg-gradient-to-l from-white to-orange-50/60 p-6 text-slate-900 shadow-sm">
            <div><div class="text-xs font-bold text-orange-600">درخواست وجه #{{ $withdrawal->id }}</div><h1 class="mt-2 text-xl font-black">{{ $withdrawal->store?->title }} · {{ number_format($withdrawal->amount) }} تومان</h1><p class="mt-2 text-xs text-slate-600">{{ $withdrawal->status->label() }} · {{ jalali_date($withdrawal->created_at, 'Y/m/d H:i') }}</p></div>
            <x-admin.form.button tag="a" href="{{ route('admin.store-withdrawals.index') }}" variant="secondary">بازگشت به فهرست</x-admin.form.button>
        </div>

        @if(session('success'))<div class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-5 rounded-xl bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ session('error') }}</div>@endif
        <x-admin.form.validation-errors class="mb-5"/>

        <div class="grid gap-5 xl:grid-cols-2">
            <x-admin.metabox title="اطلاعات درخواست" :collapsible="false">
                <dl class="space-y-4 text-sm">
                    <div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">ثبت‌کننده</dt><dd class="font-bold">{{ $withdrawal->requestedBy?->display_name }} #{{ $withdrawal->requested_by_user_id }}</dd></div>
                    <div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">صاحب حساب</dt><dd class="font-bold">{{ $withdrawal->account_holder }}</dd></div>
                    <div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">شماره شبا</dt><dd class="font-bold" dir="ltr">{{ $withdrawal->iban }}</dd></div>
                    <div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">شماره کارت</dt><dd class="font-bold" dir="ltr">{{ implode(' ', str_split($withdrawal->card_number, 4)) }}</dd></div>
                    @if($withdrawal->seller_note)<div><dt class="text-slate-500">توضیح فروشنده</dt><dd class="mt-1 rounded-xl bg-slate-50 p-3 leading-7">{{ $withdrawal->seller_note }}</dd></div>@endif
                    @if($withdrawal->payment_reference)<div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">شناسه پیگیری</dt><dd dir="ltr">{{ $withdrawal->payment_reference }}</dd></div>@endif
                    @if($withdrawal->approved_at)<div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">تأیید</dt><dd>{{ jalali_date($withdrawal->approved_at, 'Y/m/d H:i') }} · {{ $withdrawal->approvedBy?->display_name }}</dd></div>@endif
                    @if($withdrawal->rejected_at)<div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">رد</dt><dd>{{ jalali_date($withdrawal->rejected_at, 'Y/m/d H:i') }} · {{ $withdrawal->rejectedBy?->display_name }}</dd></div>@endif
                    @if($withdrawal->admin_note)<div><dt class="text-slate-500">توضیح ادمین</dt><dd class="mt-1 rounded-xl bg-slate-50 p-3 leading-7">{{ $withdrawal->admin_note }}</dd></div>@endif
                </dl>
            </x-admin.metabox>

            <x-admin.metabox title="ماندهٔ فروشگاه" :collapsible="false">
                <dl class="space-y-4 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">درآمد فروش پرداخت‌شده</dt><dd class="font-black">{{ number_format($balance['earned']) }} تومان</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">درخواست‌های تأییدشده</dt><dd class="font-black">{{ number_format($balance['approved']) }} تومان</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">درخواست‌های در انتظار</dt><dd class="font-black">{{ number_format($balance['pending']) }} تومان</dd></div>
                    <div class="flex justify-between gap-3 border-t border-slate-100 pt-4"><dt class="text-slate-500">قابل درخواست</dt><dd class="font-black text-emerald-700">{{ number_format($balance['available']) }} تومان</dd></div>
                </dl>
                <p class="mt-5 text-xs leading-6 text-slate-500">فقط سهم کمیسیون فروشگاه از سفارش‌های پرداخت‌شده محاسبه می‌شود؛ سفارش‌های مستردشده در مانده نیستند.</p>
            </x-admin.metabox>
        </div>

        @if($withdrawal->status->value === 'pending')
            <div class="mt-5 grid gap-5 xl:grid-cols-2">
                <x-admin.metabox title="تأیید درخواست" :collapsible="false">
                    <p class="mb-4 text-xs leading-7 text-slate-500">تأیید، این مبلغ را از ماندهٔ فروشگاه کسر می‌کند. انتقال بانکی باید جداگانه انجام شود.</p>
                    <x-admin.form action="{{ route('admin.store-withdrawals.approve', $withdrawal) }}" method="post" class="space-y-4 js-confirm" data-confirm-title="تأیید درخواست وجه" data-confirm-text="درخواست تأیید و مبلغ از ماندهٔ فروشگاه کسر شود؟">
                        <x-admin.field label="شناسه پیگیری انتقال (اختیاری)"><x-admin.form.input name="payment_reference" :value="old('payment_reference')" maxlength="160" dir="ltr"/></x-admin.field>
                        <x-admin.field label="توضیح ادمین (اختیاری)"><x-admin.form.textarea name="admin_note" :value="old('admin_note')" rows="3"/></x-admin.field>
                        <x-admin.form.button type="submit" variant="solid">تأیید و کسر از مانده</x-admin.form.button>
                    </x-admin.form>
                </x-admin.metabox>
                <x-admin.metabox title="رد درخواست" :collapsible="false">
                    <p class="mb-4 text-xs leading-7 text-slate-500">با رد درخواست، مبلغ کنارگذاشته‌شده دوباره قابل درخواست می‌شود.</p>
                    <x-admin.form action="{{ route('admin.store-withdrawals.reject', $withdrawal) }}" method="post" class="space-y-4 js-confirm" data-confirm-title="رد درخواست وجه" data-confirm-text="این درخواست رد شود؟">
                        <x-admin.field required label="دلیل رد برای فروشنده"><x-admin.form.textarea name="reason" :value="old('reason')" rows="3"/></x-admin.field>
                        <x-admin.form.button type="submit" variant="danger">رد درخواست</x-admin.form.button>
                    </x-admin.form>
                </x-admin.metabox>
            </div>
        @endif
    </x-admin.page>
@endsection
