@extends('admin.layout')

@section('title', 'بررسی درخواست برداشت')

@section('content')
    <x-admin.page>
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-orange-100 bg-gradient-to-l from-white to-orange-50/60 p-6 text-slate-900 shadow-sm">
            <div>
                <div class="text-xs font-bold text-orange-600">درخواست برداشت #{{ $withdrawal->id }}</div>
                <h1 class="mt-2 text-xl font-black">{{ number_format($withdrawal->amount) }} تومان</h1>
                <p class="mt-2 text-xs text-slate-600">{{ $withdrawal->status->label() }} · ثبت در {{ jalali_date($withdrawal->created_at, 'Y/m/d H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('wallet.adjust')
                    @if($withdrawal->user)
                        <x-admin.form.button tag="a" href="{{ route('admin.users.wallet.show', $withdrawal->user) }}" variant="secondary">کیف پول کاربر</x-admin.form.button>
                    @endif
                @endcan
                <x-admin.form.button tag="a" href="{{ route('admin.wallet-withdrawals.index') }}" variant="secondary">بازگشت به فهرست</x-admin.form.button>
            </div>
        </div>

        <x-admin.form.validation-errors class="mb-5"/>

        <div class="grid gap-5 xl:grid-cols-2">
            <x-admin.metabox title="اطلاعات درخواست" :collapsible="false">
                <dl class="space-y-4 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">کاربر</dt><dd class="font-bold">{{ $withdrawal->user?->full_name }} #{{ $withdrawal->user_id }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">صاحب حساب</dt><dd class="font-bold">{{ $withdrawal->account_holder }}</dd></div>
                    <div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">شماره شبا</dt><dd class="font-bold" dir="ltr">{{ $withdrawal->iban }}</dd></div>
                    <div class="flex flex-wrap justify-between gap-3"><dt class="text-slate-500">شماره کارت</dt><dd class="font-bold" @if($withdrawal->card_number) dir="ltr" @endif>{{ $withdrawal->card_number ? implode(' ', str_split($withdrawal->card_number, 4)) : 'برای این درخواست ثبت نشده است' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">رزرو کیف پول</dt><dd class="font-bold">{{ $withdrawal->hold?->status->label() ?? 'نامشخص' }}</dd></div>
                    @if($withdrawal->user_note)<div><dt class="text-slate-500">توضیح کاربر</dt><dd class="mt-1 rounded-xl bg-slate-50 p-3 leading-7">{{ $withdrawal->user_note }}</dd></div>@endif
                </dl>
            </x-admin.metabox>

            <x-admin.metabox title="روند رسیدگی" :collapsible="false">
                <div class="space-y-3 text-sm leading-7">
                    <div class="rounded-xl bg-slate-50 p-3">ثبت درخواست: {{ jalali_date($withdrawal->created_at, 'Y/m/d H:i') }}</div>
                    @if($withdrawal->approved_at)<div class="rounded-xl bg-amber-50 p-3">تأیید: {{ jalali_date($withdrawal->approved_at, 'Y/m/d H:i') }} توسط {{ $withdrawal->approvedBy?->full_name }}</div>@endif
                    @if($withdrawal->paid_at)<div class="rounded-xl bg-emerald-50 p-3">واریز: {{ jalali_date($withdrawal->paid_at, 'Y/m/d H:i') }} توسط {{ $withdrawal->paidBy?->full_name }}<br>شناسه پیگیری: <span dir="ltr">{{ $withdrawal->payment_reference }}</span><br>{{ $withdrawal->admin_note }}</div>@endif
                    @if($withdrawal->rejected_at)<div class="rounded-xl bg-red-50 p-3">رد: {{ jalali_date($withdrawal->rejected_at, 'Y/m/d H:i') }} توسط {{ $withdrawal->rejectedBy?->full_name }}<br>{{ $withdrawal->admin_note }}</div>@endif
                </div>
            </x-admin.metabox>
        </div>

        @if($withdrawal->status->value === 'pending')
            <div class="mt-5">
                <x-admin.metabox title="تأیید درخواست" :collapsible="false">
                    <p class="mb-4 text-xs leading-7 text-slate-500">تأیید فقط وضعیت درخواست را تغییر می‌دهد. مبلغ تا ثبت واریز، رزرو می‌ماند.</p>
                    <x-admin.form action="{{ route('admin.wallet-withdrawals.approve', $withdrawal) }}" method="post" class="js-confirm" data-confirm-title="تأیید درخواست" data-confirm-text="درخواست تأیید شود؟ مبلغ همچنان رزرو خواهد ماند.">
                        <x-admin.form.button type="submit" variant="solid">تأیید درخواست</x-admin.form.button>
                    </x-admin.form>
                </x-admin.metabox>
            </div>
        @endif

        @if($withdrawal->status->value === 'approved')
            <div class="mt-5">
                <x-admin.metabox title="ثبت واریز انجام‌شده" :collapsible="false">
                    <p class="mb-4 text-xs leading-7 text-slate-500">پس از واریز واقعی به حساب ثبت‌شده، شناسه پیگیری و توضیح را ثبت کنید. این عملیات مبلغ رزروشده را از کیف پول کسر می‌کند.</p>
                    <x-admin.form action="{{ route('admin.wallet-withdrawals.pay', $withdrawal) }}" method="post" class="space-y-4 js-confirm" data-confirm-title="ثبت واریز" data-confirm-text="تأیید می‌کنید که مبلغ واقعاً واریز شده است؟">
                        <x-admin.field required label="شناسه پیگیری واریز"><x-admin.form.input name="payment_reference" :value="old('payment_reference')" maxlength="160" dir="ltr"/></x-admin.field>
                        <x-admin.field required label="توضیحات واریز برای کاربر"><x-admin.form.textarea name="admin_note" :value="old('admin_note')" rows="3"/></x-admin.field>
                        <x-admin.form.button type="submit" variant="solid">ثبت واریز و نهایی‌سازی برداشت</x-admin.form.button>
                    </x-admin.form>
                </x-admin.metabox>
            </div>
        @endif

        @if(in_array($withdrawal->status->value, ['pending', 'approved']))
            <div class="mt-5">
                <x-admin.metabox title="رد درخواست" :collapsible="false">
                    <p class="mb-4 text-xs leading-7 text-slate-500">با رد درخواست، مبلغ رزروشده فوراً در کیف پول کاربر آزاد می‌شود.</p>
                    <x-admin.form action="{{ route('admin.wallet-withdrawals.reject', $withdrawal) }}" method="post" class="space-y-4 js-confirm" data-confirm-title="رد درخواست" data-confirm-text="درخواست رد و مبلغ رزروشده آزاد شود؟">
                        <x-admin.field required label="دلیل رد برای کاربر"><x-admin.form.textarea name="reason" :value="old('reason')" rows="3"/></x-admin.field>
                        <x-admin.form.button type="submit" variant="danger">رد و آزادسازی موجودی</x-admin.form.button>
                    </x-admin.form>
                </x-admin.metabox>
            </div>
        @endif
    </x-admin.page>
@endsection
