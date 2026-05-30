@if($product->installationSteps->isNotEmpty())
    <x-front.cart id="installation-steps">
        <x-front.cart-title
            title="راهنمای نصب"
            subtitle="راهنمای ساده و مرحله‌ای برای نصب محصول"
            iconName="settings-02"
        />

        <div class="mt-5 grid gap-4">
            @foreach($product->installationSteps as $i => $step)
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                    <div class="flex gap-4">
                        <div
                            class="h-10 w-10 shrink-0 rounded-2xl bg-brand-500 text-white font-black grid place-items-center">
                            {{ $i + 1 }}
                        </div>
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">
                                {{ $step->title }}
                            </div>
                            <div class="mt-2 text-sm text-slate-600 leading-7">
                                {!! nl2br(htmlspecialchars(e($step->value))) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-front.cart>
@endif
