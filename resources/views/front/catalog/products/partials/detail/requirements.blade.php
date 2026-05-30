@if($product->requirements->isNotEmpty())
    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5">
        <x-front.cart-title
            title="سیستم موردنیاز"
            subtitle="پیشنهاد شده برای اجرای روان محصول"
            iconName="computer-check"
            class="[&_h2]:text-sm [&_span]:bg-white [&_span]:border [&_span]:border-slate-200 [&_svg]:text-blue-500"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            @foreach($product->requirements as $requirement)
                <div class="rounded-2xl bg-white border border-slate-200 p-4">
                    <div class="text-xs font-extrabold text-slate-500">{{ $requirement->title }}</div>
                    <div class="mt-2 font-bold text-slate-900">{{ $requirement->value }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endif
