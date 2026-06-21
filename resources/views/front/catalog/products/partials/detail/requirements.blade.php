@if($product->requirements->isNotEmpty())
    <div
        class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900 p-4 sm:mt-6 sm:rounded-3xl sm:p-5">
        <x-front.cart-title
            title="سیستم موردنیاز"
            subtitle="پیشنهاد شده برای اجرای روان محصول"
            iconName="computer-check"
            class="[&_h2]:text-sm [&_span]:border [&_span]:border-slate-200 [&_span]:bg-white dark:[&_span]:bg-slate-800 dark:[&_span]:border-slate-700 [&_svg]:text-blue-500"
        />

        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach($product->requirements as $requirement)
                <div
                    class="flex items-center justify-between gap-3
                           rounded-lg border border-slate-200 bg-white px-3 py-2
                           text-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 shrink-0">
                        {{ $requirement->title }}
                    </div>

                    <div
                        class="min-w-0 truncate text-[0.8rem] font-semibold text-slate-900 dark:text-slate-100 text-left">
                        {{ $requirement->value }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
