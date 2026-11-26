@extends('front.master')

@php
    $isSuccess = $resultState === 'success';
    $isProcessing = $resultState === 'processing';
    $isPending = $resultState === 'pending';
    $isFailed = $resultState === 'failed';

    $money = fn (int $amount): string =>
        number_format($amount) . ' Toman';

    $receiptCode = $payment->gateway_transaction_id
        ?? $payment->gateway_reference
        ?? $payment->tracking_code;

    $happenedAt = match (true) {
        $isSuccess =>
            $topUp->completed_at
                ?? $payment->paid_at
                ?? $payment->updated_at,

        $isProcessing =>
            $payment->paid_at
                ?? $payment->updated_at,

        $isFailed =>
            $payment->failed_at
                ?? $payment->updated_at,

        default =>
            $payment->updated_at,
    };

    $mainTitle = match ($resultState) {
        'success' =>
            'Wallet topped up successfully',

        'processing' =>
            'Payment confirmed; top-up is being completed',

        'pending' =>
            'Payment under review',

        default =>
            'Wallet top-up failed',
    };

    $mainDescription = match ($resultState) {
        'success' =>
            'The paid amount has been added to your wallet and is available for purchases.',

        'processing' =>
            'Your payment was confirmed by the gateway, but the final balance update is not complete. The transaction is saved; do not pay again.',

        'pending' =>
            'The final payment result is not known yet. Check your wallet status again shortly.',

        default =>
            'The gateway did not confirm payment and no funds were added to your wallet.',
    };

    $statusLabel = match ($resultState) {
        'success' => 'Successful top-up',
        'processing' => 'In progress',
        'pending' => 'Under review',
        default => 'Failed',
    };

    $statusBadgeClasses = match ($resultState) {
        'success' =>
            'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300',

        'processing',
        'pending' =>
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300',

        default =>
            'border-red-200 bg-red-50 text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300',
    };

    $statusDotClasses = match ($resultState) {
        'success' => 'bg-emerald-500 dark:bg-emerald-400',

        'processing',
        'pending' => 'bg-amber-500 dark:bg-amber-400',

        default => 'bg-red-500 dark:bg-red-400',
    };

    $iconBoxClasses = match ($resultState) {
        'success' =>
            'border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300',

        'processing',
        'pending' =>
            'border-amber-200 bg-amber-50 text-amber-600 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300',

        default =>
            'border-red-200 bg-red-50 text-red-600 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300',
    };

    $dateTitle = match ($resultState) {
        'success' => 'Top-up date',
        'processing' => 'Payment date',
        'pending' => 'Request date',
        default => 'Attempt date',
    };
@endphp

@section('head')
    <title>
        {{ $mainTitle . ' | ' . get_setting('general', 'title') }}
    </title>
@endsection

@section('content')
    <section
        class="mx-auto flex max-w-6xl items-center justify-center
               px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8"
    >
        <div
            class="w-full overflow-hidden rounded-2xl border border-slate-200
                   bg-white shadow-soft2
                   dark:border-slate-700 dark:bg-slate-900"
        >
            {{-- Header --}}
            <div
                class="flex flex-col gap-3 border-b border-slate-200
                       px-4 py-4
                       sm:flex-row sm:items-center sm:justify-between sm:px-7
                       dark:border-slate-700"
            >
                <div>
                    <h1 class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">
                        Wallet top-up receipt
                    </h1>

                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        Payment details and wallet top-up status
                    </p>
                </div>

                <span
                    class="inline-flex w-fit items-center gap-2 rounded-2xl
                           border px-3 py-2 text-[11px] font-extrabold
                           {{ $statusBadgeClasses }}"
                >
                    <span class="h-2 w-2 rounded-full {{ $statusDotClasses }}"></span>

                    {{ $statusLabel }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12">
                {{-- Main content --}}
                <div class="min-w-0 p-4 sm:p-7 lg:col-span-8 lg:p-9">
                    <div
                        class="flex flex-col items-center gap-4 text-center
                               sm:flex-row sm:items-center sm:gap-5 sm:text-start"
                    >
                        <div
                            class="grid h-16 w-16 shrink-0 place-items-center
                                   rounded-3xl border sm:h-20 sm:w-20
                                   {{ $iconBoxClasses }}"
                        >
                            @if($isSuccess)
                                <svg
                                    class="h-9 w-9 sm:h-11 sm:w-11"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <path
                                        d="M20 6 9 17l-5-5"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            @elseif($isProcessing || $isPending)
                                <svg
                                    class="h-9 w-9 sm:h-11 sm:w-11"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <path
                                        d="M12 6v6l4 2"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="8.5"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    />
                                </svg>
                            @else
                                <svg
                                    class="h-9 w-9 sm:h-11 sm:w-11"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <path
                                        d="M18 6 6 18M6 6l12 12"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h2
                                class="text-xl font-black leading-8 text-slate-900
                                       sm:text-3xl sm:leading-tight
                                       dark:text-white"
                            >
                                {{ $mainTitle }}
                            </h2>

                            <p
                                class="mx-auto mt-3 max-w-xl text-sm leading-7
                                       text-slate-600 sm:mx-0
                                       dark:text-slate-300"
                            >
                                {{ $mainDescription }}
                            </p>
                        </div>
                    </div>

                    {{-- Success amount --}}
                    @if($isSuccess)
                        <div
                            class="mt-6 rounded-3xl border border-emerald-200
                                   bg-emerald-50 p-5
                                   dark:border-emerald-900/40
                                   dark:bg-emerald-900/20"
                        >
                            <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">
                                Amount added to wallet
                            </p>

                            <div class="mt-2 flex items-end gap-2">
                                <strong
                                    class="text-2xl font-black text-emerald-900
                                           sm:text-3xl dark:text-emerald-200"
                                    dir="ltr"
                                >
                                    {{ number_format((int) $topUp->amount) }}
                                </strong>

                                <span class="pb-1 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                                    Toman
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Payment is paid but credit completion failed --}}
                    @if($isProcessing)
                        <div
                            class="mt-6 rounded-2xl border border-amber-200
                                   bg-amber-50 p-4
                                   dark:border-amber-900/40
                                   dark:bg-amber-900/20"
                        >
                            <h3 class="text-sm font-extrabold text-amber-900 dark:text-amber-300">
                                Do not pay again
                            </h3>

                            <p class="mt-1 text-xs leading-6 text-amber-800 dark:text-amber-400">
                                Your bank payment is confirmed. The transaction has been recorded for review and completion of
                                the balance update; no further payment is needed.
                            </p>
                        </div>
                    @endif

                    {{-- Failed --}}
                    @if($isFailed)
                        <div
                            class="mt-6 rounded-2xl border border-red-200
                                   bg-red-50 p-4
                                   dark:border-red-900/40
                                   dark:bg-red-900/20"
                        >
                            <h3 class="text-sm font-extrabold text-red-900 dark:text-red-300">
                                Possible error reason
                            </h3>

                            <p class="mt-1 text-xs leading-6 text-red-800 dark:text-red-400">
                                {{ $payment->gateway_message
                                    ?: 'The bank did not confirm the payment, the gateway connection was lost, or the user canceled the transaction.' }}
                            </p>
                        </div>
                    @endif

                    {{-- Wallet balance --}}
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <article
                            class="rounded-2xl border border-slate-200
                                   bg-slate-50 p-4
                                   dark:border-slate-700 dark:bg-slate-800/50"
                        >
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                Current wallet balance
                            </p>

                            <p
                                class="mt-2 text-lg font-black text-slate-900 dark:text-white"
                                dir="ltr"
                            >
                                {{ number_format((int) $wallet->balance) }}

                                <span class="text-xs text-slate-400">
                                    Toman
                                </span>
                            </p>
                        </article>

                        <article
                            class="rounded-2xl border border-slate-200
                                   bg-slate-50 p-4
                                   dark:border-slate-700 dark:bg-slate-800/50"
                        >
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                Available balance
                            </p>

                            <p
                                class="mt-2 text-lg font-black text-slate-900 dark:text-white"
                                dir="ltr"
                            >
                                {{ number_format((int) $wallet->available_balance) }}

                                <span class="text-xs text-slate-400">
                                    Toman
                                </span>
                            </p>
                        </article>
                    </div>

                    {{-- Actions --}}
                    <div
                        class="mt-6 grid grid-cols-1 gap-3
                               sm:flex sm:flex-row sm:flex-wrap"
                    >
                        <a
                            href="{{ route('account.wallet') }}"
                            class="inline-flex h-11 items-center justify-center gap-2
                                   rounded-2xl bg-brand-500 px-5 text-sm font-extrabold
                                   text-white shadow-soft transition
                                   hover:bg-brand-600 active:bg-brand-700"
                        >
                            View wallet
                        </a>

                        @if($isFailed)
                            <a
                                href="{{ route('account.wallet') }}"
                                class="inline-flex h-11 items-center justify-center gap-2
                                       rounded-2xl border border-slate-200 bg-white
                                       px-5 text-sm font-extrabold text-brand-700
                                       transition hover:border-brand-200 hover:bg-brand-50
                                       dark:border-slate-700 dark:bg-slate-900
                                       dark:text-brand-300 dark:hover:bg-slate-800"
                            >
                                Try again
                                <span>←</span>
                            </a>
                        @else
                            <a
                                href="{{ route('products.list') }}"
                                class="inline-flex h-11 items-center justify-center gap-2
                                       rounded-2xl border border-slate-200 bg-white
                                       px-5 text-sm font-extrabold text-slate-700
                                       transition hover:bg-slate-50
                                       dark:border-slate-700 dark:bg-slate-900
                                       dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                Back to store
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Receipt details --}}
                <aside
                    class="border-t border-slate-200 bg-slate-50 p-4
                           sm:p-5 lg:col-span-4 lg:border-r lg:border-t-0
                           dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="rounded-2xl border border-slate-200
                               bg-white p-4 sm:rounded-3xl
                               dark:border-slate-700 dark:bg-slate-900"
                    >
                        <div>
                            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                Payment amount
                            </div>

                            <div class="mt-2 text-lg font-black text-slate-900 sm:text-xl dark:text-white">
                                {{ $money((int) $payment->amount) }}
                            </div>
                        </div>

                        <div
                            class="mt-4 space-y-3 border-t border-slate-100 pt-4
                                   dark:border-slate-700"
                        >
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">
                                    Top-up request code
                                </span>

                                <span
                                    class="break-all text-end font-extrabold
                                           text-slate-900 dark:text-white"
                                >
                                    {{ $topUp->public_token }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">
                                    Receipt code
                                </span>

                                <span
                                    class="break-all text-end font-extrabold
                                           text-slate-900 dark:text-white"
                                >
                                    {{ $receiptCode }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">
                                    Gateway
                                </span>

                                <span class="font-extrabold text-slate-900 dark:text-white">
                                    {{ $payment->gateway->label() }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">
                                    Payment status
                                </span>

                                <span class="font-extrabold">
                                    {{ $payment->status->label() }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">
                                    {{ $dateTitle }}
                                </span>

                                <span class="text-end font-extrabold text-slate-900 dark:text-white">
                                    {{ $happenedAt ? (($happenedAt) ? \Illuminate\Support\Carbon::parse($happenedAt)->format('Y/m/d H:i') : '') : '—' }}
                                </span>
                            </div>
                        </div>

                        @if($isSuccess)
                            <div
                                class="mt-4 rounded-2xl border border-emerald-200
                                       bg-emerald-50 p-3
                                       dark:border-emerald-900/40
                                       dark:bg-emerald-900/20"
                            >
                                <h3 class="text-xs font-extrabold text-emerald-900 dark:text-emerald-300">
                                    Top-up confirmed and recorded
                                </h3>

                                <p class="mt-1 text-xs leading-6 text-emerald-800 dark:text-emerald-400">
                                    The balance change is recorded in your wallet ledger.
                                </p>
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
