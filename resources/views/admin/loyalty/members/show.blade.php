@extends('admin.layout')

@section('title', 'جزئیات امتیاز ' . $user->full_name)

@section('content')
    @php
        $balance = (int)($account?->balance ?? 0);
        $spendable = max(0, $balance);
        $lifetimeEarned = (int)($account?->lifetime_earned ?? 0);
        $lifetimeSpent = (int)($account?->lifetime_spent ?? 0);
        $adjustmentKey = 'loyalty:admin-adjustment:' . (string) \Illuminate\Support\Str::ulid();
    @endphp

    <div class="space-y-5">
        <div
            class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:flex-row lg:items-center">
            <div class="flex min-w-0 items-center gap-4">
                <x-admin.list.thumbnail :src="$user->avatar?->url" :alt="$user->full_name" size="sm-square"/>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="truncate text-lg font-black text-slate-900">{{ $user->full_name }}</h1>
                        <x-admin.badge color="slate" size="sm">#{{ $user->id }}</x-admin.badge>
                    </div>

                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                        @if($user->username)
                            <span dir="ltr">{{ '@' . $user->username }}</span>
                        @endif
                        @if($user->mobile)
                            <span>{{ $user->mobile }}</span>
                        @endif
                        @if($user->email)
                            <span dir="ltr">{{ $user->email }}</span>
                        @endif
                        <span>{{ $user->role?->title ?? 'بدون نقش' }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @can('user.edit')
                    <x-admin.form.button tag="a" href="{{ route('admin.users.edit', $user) }}" variant="secondary">
                        ویرایش کاربر
                    </x-admin.form.button>
                @endcan
                <x-admin.form.button tag="a" href="{{ route('admin.loyalty-members.index') }}" variant="secondary">
                    بازگشت به کاربران باشگاه
                </x-admin.form.button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">موجودی فعلی</div>
                <div
                    class="mt-2 text-2xl font-black {{ $balance < 0 ? 'text-red-600' : 'text-slate-900' }}">{{ number_format($balance) }}</div>
                <div class="mt-1 text-[11px] text-slate-400">امتیاز</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">قابل مصرف</div>
                <div class="mt-2 text-2xl font-black text-brand-700">{{ number_format($spendable) }}</div>
                <div class="mt-1 text-[11px] text-slate-400">امتیاز</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">کل کسب شده</div>
                <div class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($lifetimeEarned) }}</div>
                <div class="mt-1 text-[11px] text-slate-400">امتیاز</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">کل مصرف شده</div>
                <div class="mt-2 text-2xl font-black text-slate-700">{{ number_format($lifetimeSpent) }}</div>
                <div class="mt-1 text-[11px] text-slate-400">امتیاز</div>
            </div>
        </div>

        @can('loyalty-account.adjust')
            <x-admin.metabox title="افزایش یا کاهش دستی امتیاز">
                <x-admin.form
                    method="post"
                    action="{{ route('admin.loyalty-members.adjust', $user) }}"
                    class="space-y-4"
                >
                    <input type="hidden" name="idempotency_key" value="{{ $adjustmentKey }}">

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <x-admin.field required label="نوع تغییر">
                            <x-admin.form.select
                                name="direction"
                                :options="[
                                    'credit' => 'افزایش موجودی',
                                    'debit' => 'کاهش موجودی',
                                ]"
                                :value="old('direction', 'credit')"
                            />
                        </x-admin.field>

                        <x-admin.field required label="مقدار امتیاز">
                            <x-admin.form.input
                                name="amount"
                                type="number"
                                min="1"
                                dir="ltr"
                                :value="old('amount')"
                            />
                        </x-admin.field>

                        <div class="lg:col-span-1"></div>

                        <div class="lg:col-span-3">
                            <x-admin.field required label="دلیل تغییر">
                                <x-admin.form.textarea
                                    name="note"
                                    rows="3"
                                    :value="old('note')"
                                />
                            </x-admin.field>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-7 text-amber-800">
                        اصلاح دستی فقط موجودی فعلی را تغییر می‌دهد و شاخص‌های «کل کسب شده» و «کل مصرف شده» را دستکاری
                        نمی‌کند. کاهش دستی می‌تواند در صورت نیاز موجودی کاربر را منفی کند و بدهی امتیاز ایجاد کند.
                    </div>

                    <div class="flex justify-end mt-3">
                        <x-admin.form.button type="submit" variant="solid">ثبت تغییر امتیاز</x-admin.form.button>
                    </div>
                </x-admin.form>
            </x-admin.metabox>
        @endcan

        @include('admin.loyalty.members.partials.ledger-filters')

        <x-admin.metabox title="تاریخچه امتیاز">
            @include('admin.loyalty.members.partials.ledger-table')

            <div class="mt-4">
                <x-admin.list.pagination :model="$entries"/>
            </div>
        </x-admin.metabox>
    </div>
@endsection
