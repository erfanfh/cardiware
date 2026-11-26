@php
    use Illuminate\Support\Str;

    $conversion = $club['pointConversion'];
    $config = $conversion['config'];
    $initialPoints = (int) old('points', $config->minimumPoints);
@endphp

@if($conversion['isEnabled'])
    <section
        id="convert-points"
        class="scroll-mt-24 overflow-hidden rounded-3xl border border-emerald-200 bg-white shadow-sm dark:border-emerald-900/50 dark:bg-slate-900"
        x-data="{
            points: @js($initialPoints),
            unit: @js($config->pointsPerUnit),
            walletPerUnit: @js($config->walletAmountPerUnit),
            minimum: @js($config->minimumPoints),
            maximum: @js($conversion['maximumConvertiblePoints']),
            confirming: false,
            submitting: false,
            format(value) {
                return new Intl.NumberFormat('en-US').format(Number(value) || 0);
            },
            walletAmount() {
                const value = Number(this.points) || 0;
                return Math.floor(value / this.unit) * this.walletPerUnit;
            },
            isValid() {
                const value = Number(this.points) || 0;
                return value >= this.minimum
                    && value <= this.maximum
                    && value % this.unit === 0;
            },
            choose(value) {
                this.points = value;
                this.confirming = false;
            }
        }"
    >
        <div class="relative grid gap-0 lg:grid-cols-12">
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 p-5 text-white sm:p-6 lg:col-span-5 lg:p-7">
                <div class="pointer-events-none absolute -left-16 -top-16 h-44 w-44 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -right-12 h-48 w-48 rounded-full bg-amber-300/15 blur-3xl"></div>

                <div class="relative">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 [&_svg]:h-6 [&_svg]:w-6">
                        @include('icons.wallet-03')
                    </div>

                    <h2 class="mt-4 text-lg font-black leading-8 sm:text-xl">Convert your points to shopping credit</h2>
                    <p class="mt-2 text-xs font-medium leading-6 text-emerald-50/90">
                        Credit is instantly added to your wallet and can be used for purchases Cardiware with it.
                    </p>

                    <div class="mt-5 rounded-2xl border border-white/15 bg-black/10 p-4 backdrop-blur-sm">
                        <div class="text-[10px] font-bold text-emerald-100">Conversion rate</div>
                        <div class="mt-1.5 text-sm font-black">
                            {{ number_format($config->pointsPerUnit) }} points
                            <span class="mx-1 text-emerald-200">=</span>
                            {{ number_format($config->walletAmountPerUnit) }} Toman
                        </div>
                    </div>

                    <div class="mt-3 flex items-start gap-2 text-[10px] font-medium leading-5 text-emerald-100/80">
                        <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-300"></span>
                        Converted credit cannot be withdrawn and is usable for website payments.
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-6 lg:col-span-7 lg:p-7">
                @if(session('point_conversion_success'))
                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs font-bold leading-6 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
                        Great! {{ number_format((int) session('point_conversion_success.points')) }} points converted to
                        {{ number_format((int) session('point_conversion_success.wallet_amount')) }} toman in wallet credit.
                    </div>
                @elseif(session('point_conversion_error'))
                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-bold leading-6 text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-200">
                        {{ session('point_conversion_error') }}
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400">Available points</div>
                        <div class="mt-1 text-xl font-black text-slate-900 dark:text-white">
                            {{ number_format($club['spendablePointBalance']) }}
                            <span class="text-xs text-slate-400">points</span>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-3 py-2 text-left dark:bg-slate-950/70" dir="ltr">
                        <div class="text-[10px] font-bold text-slate-400">Wallet balance</div>
                        <div class="mt-1 text-xs font-black text-slate-800 dark:text-slate-200">
                            {{ number_format($conversion['walletAvailableBalance']) }} Toman
                        </div>
                    </div>
                </div>

                @if($conversion['canConvert'])
                    <form
                        method="post"
                        action="{{ route('account.loyalty.convert-to-wallet') }}"
                        class="mt-5"
                        x-on:submit="submitting = true"
                    >
                        @csrf
                        <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">

                        <label for="conversion-points" class="text-xs font-extrabold text-slate-700 dark:text-slate-300">
                            How many points to convert?
                        </label>
                        <div class="mt-2 flex items-stretch gap-2">
                            <div class="relative min-w-0 flex-1">
                                <input
                                    id="conversion-points"
                                    name="points"
                                    type="number"
                                    inputmode="numeric"
                                    x-model.number="points"
                                    x-on:input="confirming = false"
                                    min="{{ $config->minimumPoints }}"
                                    max="{{ $conversion['maximumConvertiblePoints'] }}"
                                    step="{{ $config->pointsPerUnit }}"
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 pl-16 text-sm font-black text-slate-900 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-emerald-600 dark:focus:bg-slate-950 dark:focus:ring-emerald-900/30"
                                >
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[10px] font-bold text-slate-400">points</span>
                            </div>
                            <button
                                type="button"
                                x-on:click="choose(maximum)"
                                class="rounded-2xl border border-slate-200 bg-white px-3 text-xs font-extrabold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                            >
                                Maximum
                            </button>
                        </div>

                        @error('points')
                            <p class="mt-2 text-[10px] font-bold leading-5 text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror

                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach(array_unique([$config->minimumPoints, $config->minimumPoints * 2, $conversion['maximumConvertiblePoints']]) as $preset)
                                @if($preset >= $config->minimumPoints && $preset <= $conversion['maximumConvertiblePoints'])
                                    <button
                                        type="button"
                                        x-on:click="choose({{ $preset }})"
                                        class="rounded-xl bg-slate-100 px-2.5 py-1.5 text-[10px] font-bold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-emerald-950/30 dark:hover:text-emerald-300"
                                    >
                                        {{ number_format($preset) }} points
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Credit received</span>
                                <strong class="text-lg font-black text-emerald-700 dark:text-emerald-300">
                                    <span x-text="format(walletAmount())"></span>
                                    <span class="text-xs">Toman</span>
                                </strong>
                            </div>
                            <p x-show="!isValid()" class="mt-2 text-[10px] font-bold leading-5 text-rose-600 dark:text-rose-300">
                                Amount must be between {{ number_format($config->minimumPoints) }} and {{ number_format($conversion['maximumConvertiblePoints']) }} and a multiple of {{ number_format($config->pointsPerUnit) }} be.
                            </p>
                        </div>

                        @if($config->dailyPointsLimit > 0)
                            <p class="mt-3 text-[10px] leading-5 text-slate-400">
                                Remaining daily capacity: {{ number_format($conversion['remainingDailyPoints']) }} points
                            </p>
                        @endif

                        <button
                            x-show="!confirming"
                            type="button"
                            x-on:click="if (isValid()) confirming = true"
                            x-bind:disabled="!isValid()"
                            class="mt-4 inline-flex h-12 w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 text-xs font-black text-white shadow-lg shadow-emerald-600/15 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none dark:disabled:bg-slate-800 dark:disabled:text-slate-500"
                        >
                            Continue and review
                        </button>

                        <div
                            x-show="confirming"
                            x-cloak
                            class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
                        >
                            <p class="text-xs font-bold leading-6 text-amber-900 dark:text-amber-200">
                                Upon final confirmation, <strong x-text="format(points)"></strong> points will be deducted and
                                <strong x-text="format(walletAmount())"></strong> toman added to your wallet. This cannot be undone.
                            </p>
                            <div class="mt-3 flex gap-2">
                                <button
                                    type="submit"
                                    x-bind:disabled="submitting"
                                    class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-emerald-600 px-3 text-xs font-black text-white transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60"
                                >
                                    <span x-show="!submitting">Confirm and convert</span>
                                    <span x-show="submitting" x-cloak>Submitting...</span>
                                </button>
                                <button
                                    type="button"
                                    x-on:click="confirming = false"
                                    x-bind:disabled="submitting"
                                    class="h-10 rounded-xl border border-amber-200 bg-white px-3 text-xs font-bold text-amber-800 dark:border-amber-800 dark:bg-slate-900 dark:text-amber-200"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-xs font-bold leading-6 text-slate-600 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
                        @if($conversion['walletIsBlocked'])
                            Your wallet must be active before converting points.
                        @elseif($conversion['remainingDailyPoints'] < $config->minimumPoints)
                            You have reached your daily conversion limit; try again tomorrow.
                        @else
                            For the first conversion you need at least {{ number_format($config->minimumPoints) }} available points.
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
