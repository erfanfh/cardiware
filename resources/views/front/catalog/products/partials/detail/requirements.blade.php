@if($product->requirements->isNotEmpty())
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:mt-6 sm:rounded-3xl sm:p-5">
        <x-front.cart-title
            title="سیستم موردنیاز"
            subtitle="پیشنهاد شده برای اجرای روان محصول"
            iconName="computer-check"
            class="[&_h2]:text-sm [&_span]:border [&_span]:border-slate-200 [&_span]:bg-white [&_svg]:text-blue-500"
        />

        <div class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 sm:gap-4">
            @foreach($product->requirements as $requirement)
                <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 sm:p-4">
                    <div class="text-xs font-extrabold leading-5 text-slate-500">
                        {{ $requirement->title }}
                    </div>

                    <div class="mt-2 break-words text-sm font-bold leading-7 text-slate-900">
                        {{ $requirement->value }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
