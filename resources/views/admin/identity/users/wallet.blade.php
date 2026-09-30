@extends('admin.layout')

@section('title', 'مدیریت کیف پول ' . $user->full_name)

@section('content')
    @php
        $balance = (int) ($wallet?->balance ?? 0);
        $withdrawable = (int) ($wallet?->withdrawable_balance ?? 0);
        $held = (int) ($wallet?->held_balance ?? 0);
        $adjustmentKey = old('idempotency_key', (string) \Illuminate\Support\Str::uuid());
    @endphp

    <x-admin.page>
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-900">کیف پول {{ $user->full_name }} <span class="text-sm text-slate-500">#{{ $user->id }}</span></h1>
                <p class="mt-1 text-sm text-slate-500">وضعیت: {{ $wallet?->status->label() ?? 'فعال' }}</p>
            </div>
            @can('user.edit')
                <x-admin.form.button tag="a" href="{{ route('admin.users.edit', $user) }}" variant="secondary">بازگشت به کاربر</x-admin.form.button>
            @endcan
        </div>

        <x-admin.form.validation-errors class="mb-6"/>

        <x-admin.metabox title="موجودی کیف پول">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>کل موجودی: <strong>{{ number_format($balance) }} تومان</strong></div>
                <div>قابل برداشت: <strong>{{ number_format($withdrawable) }} تومان</strong></div>
                <div>رزروشده: <strong>{{ number_format($held) }} تومان</strong></div>
            </div>
            <p class="mt-3 text-sm text-slate-500">موجودی آزاد برای کاهش: {{ number_format($balance - $held) }} تومان</p>
        </x-admin.metabox>

        <div class="mt-5">
            <x-admin.metabox title="افزایش یا کاهش موجودی">
                <x-admin.form method="post" action="{{ route('admin.users.wallet.adjust', $user) }}" class="space-y-4 js-confirm" data-confirm-title="تأیید تغییر موجودی" data-confirm-text="آیا از ثبت این تغییر در کیف پول کاربر مطمئن هستید؟">
                    <input type="hidden" name="idempotency_key" value="{{ $adjustmentKey }}">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <x-admin.field required label="نوع تغییر">
                            <x-admin.form.select name="direction" :options="['credit' => 'افزایش', 'debit' => 'کاهش']" :value="old('direction', 'credit')"/>
                        </x-admin.field>
                        <x-admin.field required label="مبلغ (تومان)">
                            <x-admin.form.input name="amount" type="number" min="1" max="1000000000" dir="ltr" :value="old('amount')"/>
                        </x-admin.field>
                        <x-admin.field required label="مبلغ افزایشی قابل برداشت باشد؟">
                            <x-admin.form.select name="withdrawable" :options="['0' => 'خیر', '1' => 'بله']" :value="old('withdrawable', '0')"/>
                        </x-admin.field>
                    </div>

                    <x-admin.field required label="دلیل تغییر">
                        <x-admin.form.textarea name="note" rows="3" :value="old('note')"/>
                    </x-admin.field>

                    <p class="text-sm text-slate-500">در کاهش، ابتدا اعتبار غیرقابل برداشتِ آزاد مصرف می‌شود. مبلغ رزروشده کاهش نمی‌یابد. گزینه قابل برداشت فقط برای افزایش کاربرد دارد.</p>
                    <div class="flex justify-end">
                        <x-admin.form.button type="submit" variant="solid">ثبت تغییر موجودی</x-admin.form.button>
                    </div>
                </x-admin.form>
            </x-admin.metabox>
        </div>

        <div class="mt-5">
            <x-admin.metabox title="۱۰ تراکنش اخیر کیف پول">
                @forelse($transactions as $transaction)
                    <div class="flex flex-wrap justify-between gap-3 border-b border-slate-100 py-3 text-sm last:border-b-0">
                        <div>
                            <strong>{{ $transaction->type->label() }}</strong>
                            <span class="mr-2 text-slate-500">{{ $transaction->description }}</span>
                            <div class="text-xs text-slate-500">{{ $transaction->actor?->full_name ?? 'سیستم' }} · {{ jalali_date($transaction->created_at, 'Y/m/d H:i') }}</div>
                        </div>
                        <div class="text-left">
                            <div>{{ $transaction->direction->isCredit() ? '+' : '-' }}{{ number_format($transaction->amount) }} تومان</div>
                            <div class="text-xs text-slate-500">موجودی پس از تراکنش: {{ number_format($transaction->balance_after) }} تومان</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">تراکنشی ثبت نشده است.</p>
                @endforelse
            </x-admin.metabox>
        </div>
    </x-admin.page>
@endsection
