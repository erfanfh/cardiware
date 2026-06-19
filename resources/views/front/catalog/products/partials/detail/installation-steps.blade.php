@if($product->installationSteps->isNotEmpty())
    <x-front.cart id="installation-steps">
        <x-front.cart-title
            title="راهنمای نصب"
            subtitle="راهنمای ساده و مرحله‌ای برای نصب محصول"
            iconName="settings-02"
        />

        <div class="mt-4 grid gap-3 sm:mt-5 sm:gap-4">
            @foreach($product->installationSteps as $i => $step)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:rounded-3xl sm:p-5">
                    <div class="flex gap-3 sm:gap-4">
                        <div
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-2xl bg-brand-500 text-sm font-black text-white sm:h-10 sm:w-10">
                            {{ $i + 1 }}
                        </div>

                        <div class="min-w-0">
                            <div class="text-sm font-extrabold leading-7 text-slate-900">
                                {{ $step->title }}
                            </div>

                            <div class="mt-2 text-sm leading-7 text-slate-600">
                                {!! nl2br(htmlspecialchars(e($step->value))) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-front.cart>
@endif
