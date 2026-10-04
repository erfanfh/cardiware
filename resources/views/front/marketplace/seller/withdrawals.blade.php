@extends('front.marketplace.seller.layout')

@section('seller-title', 'درخواست وجه')
@section('seller-heading', 'درخواست وجه فروشگاه')
@section('seller-description', 'ماندهٔ حاصل از کمیسیون فروش‌های پرداخت‌شده و درخواست‌های وجه فروشگاه را پیگیری کنید.')

@section('seller-content')
    @if(session('success'))
        <div
            class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'درآمد ثبت‌شدهٔ فروشگاه', 'value' => $balance['earned'], 'tone' => 'text-brand-600 dark:text-brand-300'],
            ['label' => 'درخواست‌های تأییدشده', 'value' => $balance['approved'], 'tone' => 'text-slate-800 dark:text-slate-100'],
            ['label' => 'در انتظار بررسی', 'value' => $balance['pending'], 'tone' => 'text-amber-600 dark:text-amber-300'],
            ['label' => 'قابل درخواست', 'value' => $balance['available'], 'tone' => 'text-emerald-600 dark:text-emerald-400'],
        ] as $card)
            <div
                class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $card['label'] }}</div>
                <div
                    class="mt-3 break-all text-xl font-black sm:text-2xl {{ $card['tone'] }}">{{ number_format($card['value']) }}
                    تومان
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        <section
            class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-black">درخواست جدید</h2>
            <p class="mt-2 text-xs leading-7 text-slate-500 dark:text-slate-400">حداقل {{ number_format($minimum) }} و
                حداکثر {{ number_format($maximum) }} تومان در هر درخواست. درخواست‌های در انتظار از مبلغ قابل درخواست
                کنار گذاشته می‌شوند.</p>

            @if($errors->any())
                <div
                    class="mt-4 rounded-xl bg-rose-50 px-4 py-3 text-xs font-bold leading-7 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('seller.stores.withdrawals.store', $store) }}" method="post" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="request_key" value="{{ $requestKey }}">
                <div x-data="{
                    formatAmount(value) {
                        const digits = String(value)
                            .replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
                            .replace(/[٠-٩]/g, digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit))
                            .replace(/[^0-9]/g, '')
                            .replace(/^0+(?=\d)/, '');

                        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    }
                }">
                    <label for="withdrawal-amount" class="mb-2 block text-xs font-black">مبلغ درخواست (تومان)</label>
                    <input id="withdrawal-amount" name="amount" value="{{ old('amount') }}" type="text"
                           inputmode="numeric" autocomplete="off" dir="ltr" required
                           x-init="$el.value = formatAmount($el.value)"
                           x-on:input="$event.target.value = formatAmount($event.target.value)"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800"
                           placeholder="مثلاً {{ number_format($minimum) }}">
                </div>
                <div>
                    <label for="withdrawal-holder" class="mb-2 block text-xs font-black">نام صاحب حساب</label>
                    <input id="withdrawal-holder" name="account_holder" value="{{ old('account_holder') }}"
                           maxlength="150" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label for="withdrawal-iban" class="mb-2 block text-xs font-black">شماره شبا</label>
                    <div class="relative" dir="ltr" x-data="{
                        normalizeIban(value) {
                            return String(value)
                                .replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
                                .replace(/[٠-٩]/g, digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit))
                                .replace(/^\s*IR/i, '')
                                .replace(/[^0-9]/g, '')
                                .slice(0, 24);
                        }
                    }">
                        <span
                            class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-black text-slate-500 dark:text-slate-400"
                            aria-hidden="true">IR</span>
                        <input id="withdrawal-iban" name="iban"
                               value="{{ preg_replace('/^IR/i', '', old('iban', '')) }}" type="text" inputmode="numeric"
                               autocomplete="off" dir="ltr" maxlength="26" required
                               aria-describedby="seller-withdrawal-iban-prefix"
                               x-init="$el.value = normalizeIban($el.value)"
                               x-on:input="$event.target.value = normalizeIban($event.target.value)"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pl-12 text-left text-sm outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800"
                               placeholder="۲۴ رقم شماره شبا">
                    </div>
                    <span id="seller-withdrawal-iban-prefix" class="sr-only">پیشوند IR به صورت خودکار به شماره شبا اضافه می‌شود.</span>
                </div>
                <div x-data="{
                    formatCardNumber(value) {
                        const digits = String(value)
                            .replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
                            .replace(/[٠-٩]/g, digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit))
                            .replace(/[^0-9]/g, '')
                            .slice(0, 16);

                        return digits.replace(/(.{4})(?=.)/g, '$1 ');
                    }
                }">
                    <label for="withdrawal-card" class="mb-2 block text-xs font-black">شماره کارت</label>
                    <input id="withdrawal-card" name="card_number" value="{{ old('card_number') }}" type="text"
                           inputmode="numeric" autocomplete="off" dir="ltr" maxlength="19" required
                           x-init="$el.value = formatCardNumber($el.value)"
                           x-on:input="$event.target.value = formatCardNumber($event.target.value)"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800"
                           placeholder="0000 0000 0000 0000">
                </div>
                <div>
                    <label for="withdrawal-note" class="mb-2 block text-xs font-black">توضیح (اختیاری)</label>
                    <textarea id="withdrawal-note" name="seller_note" rows="3" maxlength="1000"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800">{{ old('seller_note') }}</textarea>
                </div>
                <button type="submit"
                        @disabled($balance['available'] < $minimum) class="cursor-pointer rounded-xl bg-brand-600 px-5 py-3 text-sm font-black text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                    ثبت درخواست وجه
                </button>
                @if($balance['available'] < $minimum)
                    <p class="text-xs text-amber-700 dark:text-amber-300">ماندهٔ قابل درخواست هنوز به حداقل تعیین‌شده
                        نرسیده است.</p>
                @endif
            </form>
        </section>

        <section
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 p-5 sm:px-7 dark:border-slate-800"><h2 class="text-lg font-black">
                    تاریخچهٔ درخواست‌ها</h2></div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($withdrawals as $withdrawal)
                    <article class="p-5 sm:px-7">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <strong class="text-sm">{{ number_format($withdrawal->amount) }} تومان</strong>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold {{ match($withdrawal->status->value) { 'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300', 'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300', default => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' } }}">{{ $withdrawal->status->label() }}</span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                            <span>{{ jalali_date($withdrawal->created_at, 'Y/m/d H:i') }}</span>
                            <span>ثبت‌کننده: {{ $withdrawal->requestedBy?->display_name ?? 'کاربر حذف‌شده' }}</span>
                            <span dir="ltr">{{ $withdrawal->maskedIban() }}</span>
                        </div>
                        @if(filled($withdrawal->payment_reference))
                            <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-xl bg-emerald-50 p-3 text-xs leading-6 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-200">
                                <span class="font-bold">کد رهگیری:</span>
                                <span dir="ltr" class="break-all font-black">{{ $withdrawal->payment_reference }}</span>
                            </div>
                        @endif
                        @if(filled($withdrawal->admin_note))
                            <p class="mt-3 whitespace-pre-line break-words rounded-xl p-3 text-xs leading-6 {{ $withdrawal->status->value === 'rejected' ? 'bg-rose-50 text-rose-800 dark:bg-rose-500/10 dark:text-rose-200' : 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">
                                {{ $withdrawal->status->value === 'rejected' ? 'دلیل رد:' : 'توضیح ادمین:' }} {{ $withdrawal->admin_note }}</p>
                        @endif
                    </article>
                @empty
                    <p class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">هنوز درخواست وجهی ثبت نشده
                        است.</p>
                @endforelse
            </div>
            @if($withdrawals->hasPages())
                <div class="border-t border-slate-100 p-5 dark:border-slate-800">{{ $withdrawals->links() }}</div>
            @endif
        </section>
    </div>
@endsection
