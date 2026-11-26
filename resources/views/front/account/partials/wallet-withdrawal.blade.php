@php
    $withdrawalEnabled = (bool) get_setting('financial', 'withdrawal_enabled', false);
    $availableWithdrawable = (int) $wallet->available_withdrawable_balance;
    $minimumWithdrawal = (int) config('financial.wallet.withdrawal.minimum_amount', 50000);
    $maximumWithdrawal = (int) config('financial.wallet.withdrawal.maximum_amount', 20000000);
@endphp

<x-front.cart id="wallet-withdrawal" class="scroll-mt-36 !p-4 sm:!p-5">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-black text-slate-900 sm:text-base dark:text-white">Withdraw funds</h2>
            <p class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                Requested funds are reserved in your wallet until admin review.
            </p>
        </div>
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">
            @include('icons.wallet-03')
        </div>
    </div>

    @if(session('wallet_withdrawal_success'))
        <div role="status" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold leading-6 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
            {{ session('wallet_withdrawal_success') }}
        </div>
    @endif

    @if($errors->walletWithdrawal->any())
        <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-6 text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">
            <ul class="list-disc pr-4">
                @foreach($errors->walletWithdrawal->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-xl bg-slate-50 px-4 py-3 text-xs dark:bg-slate-950">
        <div class="text-slate-500 dark:text-slate-400">
            Freely withdrawable:
            <strong class="mr-1 text-slate-800 dark:text-slate-100">{{ number_format($availableWithdrawable) }} Toman</strong>
        </div>
        @if($wallet->held_withdrawable_balance > 0)
            <div class="text-slate-500 dark:text-slate-400">
                Reserved:
                <strong class="mr-1 text-slate-800 dark:text-slate-100">{{ number_format($wallet->held_withdrawable_balance) }} Toman</strong>
            </div>
        @endif
    </div>

    @if(!$withdrawalEnabled)
        <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-6 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-200">
            Withdrawal requests are currently disabled. Previous requests are available below.
        </p>
    @elseif(!$wallet->allowsUserTransactions())
        <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-6 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-200">
            Your wallet is not enabled for withdrawals.
        </p>
    @else
        <form method="post" action="{{ route('account.wallet.withdrawals.store') }}" class="mt-4 space-y-3" novalidate>
            @csrf
            <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">

            <div class="grid gap-3 sm:grid-cols-2">
                <div x-data="{
                    toEnglishDigits(value) {
                        return String(value)
                            .replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
                            .replace(/[٠-٩]/g, digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));
                    },
                    formatAmount(value) {
                        const rawValue = this.toEnglishDigits(value)
                            .replace(/[^\d]/g, '')
                            .replace(/^0+(?=\d)/, '');

                        return rawValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    }
                }">
                    <label for="withdrawal-amount" class="text-xs font-bold text-slate-700 dark:text-slate-300">Withdrawal amount (toman)</label>
                    <input id="withdrawal-amount" name="amount" type="text" inputmode="numeric" autocomplete="off" value="{{ old('amount') }}" dir="ltr"
                           x-init="$el.value = formatAmount($el.value)"
                           x-on:input="$event.target.value = formatAmount($event.target.value)"
                           class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-left text-sm font-bold text-slate-900 outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:bg-slate-950 dark:focus:ring-brand-500/15">
                </div>
                <div>
                    <label for="withdrawal-holder" class="text-xs font-bold text-slate-700 dark:text-slate-300">Account holder name</label>
                    <input id="withdrawal-holder" name="account_holder" type="text" value="{{ old('account_holder', $user->full_name) }}"
                           class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:bg-slate-950 dark:focus:ring-brand-500/15">
                </div>
            </div>

            <div>
                <label for="withdrawal-iban" class="text-xs font-bold text-slate-700 dark:text-slate-300">IBAN</label>
                <div class="relative mt-2" dir="ltr"
                     x-data="{ normalizeIban(value) {
                         return String(value)
                             .replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
                             .replace(/[٠-٩]/g, digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit))
                             .replace(/^\s*IR/i, '')
                             .replace(/[^0-9]/g, '')
                             .slice(0, 24);
                     } }">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-black text-slate-500 dark:text-slate-400" aria-hidden="true">IR</span>
                    <input id="withdrawal-iban" name="iban" type="text" inputmode="numeric" value="{{ preg_replace('/^IR/i', '', old('iban', '')) }}" placeholder="24 digits of IBAN" autocomplete="off" dir="ltr" aria-describedby="withdrawal-iban-prefix"
                           x-on:input="$event.target.value = normalizeIban($event.target.value)"
                           class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 pl-12 text-left text-sm font-bold tracking-wide text-slate-900 outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:bg-slate-950 dark:focus:ring-brand-500/15">
                </div>
                <span id="withdrawal-iban-prefix" class="sr-only">Prefix IR is automatically added to the IBAN.</span>
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
                <label for="withdrawal-card-number" class="text-xs font-bold text-slate-700 dark:text-slate-300">Card number</label>
                <input id="withdrawal-card-number" name="card_number" type="text" inputmode="numeric" autocomplete="off" value="{{ old('card_number') }}" placeholder="0000 0000 0000 0000" dir="ltr"
                       x-init="$el.value = formatCardNumber($el.value)"
                       x-on:input="$event.target.value = formatCardNumber($event.target.value)"
                       class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-left text-sm font-bold tracking-wide text-slate-900 outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:bg-slate-950 dark:focus:ring-brand-500/15">
            </div>

            <div>
                <label for="withdrawal-note" class="text-xs font-bold text-slate-700 dark:text-slate-300">Note to administrator <span class="font-normal text-slate-400">(Optional)</span></label>
                <textarea id="withdrawal-note" name="user_note" rows="2"
                          class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:bg-slate-950 dark:focus:ring-brand-500/15">{{ old('user_note') }}</textarea>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                <p class="text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                    Minimum {{ number_format($minimumWithdrawal) }} and maximum {{ number_format($maximumWithdrawal) }} toman; the amount is unavailable until resolved.
                </p>
                <button type="submit"
                        class="h-11 cursor-pointer rounded-xl bg-brand-600 px-5 text-xs font-black text-white transition hover:bg-brand-700">
                    Submit withdrawal request
                </button>
            </div>
        </form>
    @endif
</x-front.cart>

<x-front.cart id="wallet-withdrawal-history" class="scroll-mt-36 !p-4 sm:!p-5">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-black text-slate-900 sm:text-base dark:text-white">Withdrawal history</h2>
            <p class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">Status and deposit details of your requests</p>
        </div>
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ number_format($withdrawals->total()) }} Request</span>
    </div>

    <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($withdrawals as $withdrawal)
            @php($unreadNotificationId = $withdrawalNotificationIds[$withdrawal->id] ?? null)
            <div id="wallet-withdrawal-{{ $withdrawal->id }}" class="scroll-mt-36 py-3 first:pt-0 last:pb-0 {{ $unreadNotificationId ? 'rounded-xl bg-brand-50/60 px-3 dark:bg-brand-500/10' : '' }}">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <strong class="text-sm font-black text-slate-900 dark:text-white">{{ number_format($withdrawal->amount) }} Toman</strong>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ (($withdrawal->created_at) ? \Illuminate\Support\Carbon::parse($withdrawal->created_at)->format('Y/m/d H:i') : '') }} · <span dir="ltr">{{ $withdrawal->maskedIban() }}</span></p>
                        @if($withdrawal->card_number)
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Card number: <span dir="ltr">{{ $withdrawal->maskedCardNumber() }}</span></p>
                        @endif
                    </div>
                    <span @class([
                        'rounded-full px-2.5 py-1 text-[11px] font-bold',
                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300' => $withdrawal->status->value === 'paid',
                        'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300' => $withdrawal->status->value === 'rejected',
                        'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300' => in_array($withdrawal->status->value, ['pending', 'approved']),
                    ])>{{ $withdrawal->status->label() }}</span>
                </div>
                @if($withdrawal->admin_note)
                    <p class="mt-2 text-xs leading-6 text-slate-600 dark:text-slate-300">{{ $withdrawal->admin_note }}</p>
                @endif
                @if($withdrawal->payment_reference)
                    <p class="mt-1 text-[11px] font-semibold text-slate-500 dark:text-slate-400">Deposit tracking ID: <span dir="ltr">{{ $withdrawal->payment_reference }}</span></p>
                @endif
                @if($unreadNotificationId)
                    <form method="post" action="{{ route('account.notifications.read', $unreadNotificationId) }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="return_to" value="wallet">
                        <input type="hidden" name="withdrawals_page" value="{{ $withdrawals->currentPage() }}">
                        <button type="submit" class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-bold text-white">New result · Mark as read</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="py-5 text-center text-xs text-slate-500 dark:text-slate-400">No withdrawal requests yet.</p>
        @endforelse
    </div>

    @if($withdrawals->hasPages())
        <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">{{ $withdrawals->links() }}</div>
    @endif
</x-front.cart>
