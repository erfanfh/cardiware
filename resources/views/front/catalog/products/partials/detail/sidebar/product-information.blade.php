<x-front.cart class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
    <div class="mb-4 text-sm font-extrabold text-slate-900 dark:text-white">باکس اطلاعات محصول</div>

    <div class="space-y-3 [&_svg]:h-4 [&_svg]:w-4">
        <div
            class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
            <div class="flex min-w-0 items-center gap-1.5 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                @include('icons.view')
                <span>تعداد بازدید</span>
            </div>

            <div class="shrink-0 text-sm font-bold text-slate-900 dark:text-white">
                {{ number_format(page_views($product)) }}
            </div>
        </div>

        @if($product->displaySetting?->show_sales_count)
            <div
                class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div
                    class="flex min-w-0 items-center gap-1.5 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                    @include('icons.shopping-basket-03')
                    <span>تعداد فروش</span>
                </div>

                <div class="shrink-0 text-sm font-bold text-slate-900 dark:text-white">
                    {{ $soldQuantity ? number_format($soldQuantity) : 'جدید!' }}
                </div>
            </div>
        @endif

        @foreach($product->informationEntries as $info)
            <div
                class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div
                    class="flex min-w-0 items-center gap-1.5 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                    @if($info->field->icon)
                        {!! $info->field->icon->svg !!}
                    @else
                        @include('icons.menu-01')
                    @endif
                    <span class="line-clamp-1">{{ $info->field->title }}</span>
                </div>

                <div class="shrink-0 text-left text-sm font-bold text-slate-900 dark:text-white">
                    {{ $info->value }}
                </div>
            </div>
        @endforeach
    </div>
</x-front.cart>
