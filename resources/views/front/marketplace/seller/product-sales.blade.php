@extends('front.marketplace.seller.layout')

@section('seller-title', 'جزئیات فروش')
@section('seller-heading', 'جزئیات فروش ' . $product->title)
@section('seller-description', 'گزارش فروش نسخه‌های محصول و افزونه‌ها بر اساس دسترسی فروشگاه')

@section('seller-content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('seller.stores.products', $store) }}" class="text-xs font-bold text-brand-600 dark:text-brand-300">← بازگشت به محصولات</a>
        <div class="flex flex-wrap items-center gap-2">
            <span class="ml-1 text-xs font-bold text-slate-500 dark:text-slate-400">بازهٔ گزارش:</span>
            @foreach(['all' => 'از ابتدا', '7' => '۷ روز', '30' => '۳۰ روز', '90' => '۹۰ روز'] as $period => $label)
                <a href="{{ route('seller.stores.products.sales', [$store, $product->id, 'period' => $period]) }}" @if($report['period'] === (string) $period) aria-current="page" @endif class="rounded-xl px-3 py-2 text-xs font-bold {{ $report['period'] === (string) $period ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['visible' => $store->sellerCan('view_revenue'), 'label' => 'درآمد فروشگاه', 'value' => number_format((int) $report['summary']['seller_amount']).' تومان', 'detail' => 'سهم ثبت‌شده در زمان پرداخت', 'tone' => 'text-emerald-600 dark:text-emerald-400'],
            ['visible' => $store->sellerCan('view_revenue'), 'label' => 'فروش خالص', 'value' => number_format((int) $report['summary']['net_amount']).' تومان', 'detail' => 'محصول و افزونه پس از تخفیف', 'tone' => 'text-brand-600 dark:text-brand-300'],
            ['visible' => true, 'label' => 'سفارش‌های پرداخت‌شده', 'value' => number_format($report['summary']['orders_count']), 'detail' => 'تعداد سفارش یکتا', 'tone' => 'text-sky-600 dark:text-sky-400'],
            ['visible' => $store->sellerCan('view_revenue') && $store->sellerCan('view_commission'), 'label' => 'سهم پلتفرم', 'value' => number_format((int) $report['summary']['platform_amount']).' تومان', 'detail' => 'بر اساس ردیف‌های سفارش', 'tone' => 'text-violet-600 dark:text-violet-400'],
        ] as $card)
            @continue(! $card['visible'])
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $card['label'] }}</div>
                <div class="mt-4 break-all text-xl font-black sm:text-2xl {{ $card['tone'] }}">{{ $card['value'] }}</div>
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $card['detail'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-4 md:grid-cols-2">
        @foreach([
            ['label' => 'فروش محصول', 'units' => $report['summary']['product_units'], 'net' => $report['summary']['product_net_amount'], 'seller' => $report['summary']['product_seller_amount'], 'color' => 'bg-brand-500'],
            ['label' => 'فروش افزونه‌ها', 'units' => $report['summary']['addon_units'], 'net' => $report['summary']['addon_net_amount'], 'seller' => $report['summary']['addon_seller_amount'], 'color' => 'bg-emerald-500'],
        ] as $typeCard)
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3"><h2 class="text-base font-black">{{ $typeCard['label'] }}</h2><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ number_format($typeCard['units']) }} عدد</span></div>
                @if($store->sellerCan('view_revenue'))
                <div class="mt-5 flex flex-wrap justify-between gap-4 text-sm"><div class="min-w-0"><div class="text-xs text-slate-500 dark:text-slate-400">مبلغ فروش</div><strong class="mt-1 block break-all">{{ number_format($typeCard['net']) }} تومان</strong></div><div class="min-w-0"><div class="text-xs text-slate-500 dark:text-slate-400">درآمد فروشگاه</div><strong class="mt-1 block break-all text-emerald-600 dark:text-emerald-400">{{ number_format($typeCard['seller']) }} تومان</strong></div></div>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full {{ $typeCard['color'] }}" style="width: {{ $report['summary']['net_amount'] > 0 ? round(100 * $typeCard['net'] / $report['summary']['net_amount']) : 0 }}%"></div></div>
                <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">سهم از مبلغ فروش خالص این محصول</p>
                @endif
            </div>
        @endforeach
    </div>

    @foreach([
        ['title' => 'جزئیات نسخه‌های محصول', 'items' => $report['sale_items'], 'empty' => 'هنوز فروشی برای نسخه‌های این محصول ثبت نشده است.'],
        ['title' => 'جزئیات افزونه‌ها', 'items' => $report['addons'], 'empty' => 'هنوز فروشی برای افزونه‌های این محصول ثبت نشده است.'],
        ['title' => 'سایر آیتم‌ها', 'items' => $report['other_items'], 'empty' => null],
    ] as $section)
        @if($section['empty'] !== null || $section['items']->isNotEmpty())
            <section class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 p-5 sm:px-7 dark:border-slate-800"><h2 class="text-lg font-black">{{ $section['title'] }}</h2></div>
                @if($section['items']->isEmpty())
                    <p class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ $section['empty'] }}</p>
                @else
                    <div class="overflow-x-auto" tabindex="0" role="region" aria-label="{{ $section['title'] }}؛ جدول قابل پیمایش افقی">
                        <table class="w-full {{ $store->sellerCan('view_revenue') ? 'min-w-[850px]' : 'min-w-[480px]' }} text-right text-sm">
                            <thead class="bg-slate-50 text-xs font-bold text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
                                <th scope="col" class="px-5 py-3 sm:px-7">آیتم</th><th scope="col" class="px-4 py-3">سفارش</th><th scope="col" class="px-4 py-3">تعداد</th>
                                @if($store->sellerCan('view_revenue'))
                                    <th scope="col" class="px-4 py-3">فروش خالص</th><th scope="col" class="px-4 py-3">درآمد فروشگاه</th>
                                @endif
                                @if($store->sellerCan('view_commission'))
                                    <th scope="col" class="px-4 py-3">نرخ مؤثر</th>
                                    @if($store->sellerCan('view_revenue'))<th scope="col" class="px-4 py-3">سهم پلتفرم</th>@endif
                                @endif
                            </tr></thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($section['items'] as $item)
                                    <tr>
                                        <th scope="row" class="px-5 py-4 font-black sm:px-7">{{ $item['title'] }}</th><td class="px-4 py-4">{{ number_format($item['orders_count']) }}</td><td class="px-4 py-4">{{ number_format($item['units']) }}</td>
                                        @if($store->sellerCan('view_revenue'))
                                            <td class="px-4 py-4">{{ number_format($item['net_amount']) }} تومان</td><td class="px-4 py-4 font-black text-emerald-600 dark:text-emerald-400">{{ number_format($item['seller_amount']) }} تومان</td>
                                        @endif
                                        @if($store->sellerCan('view_commission'))
                                            <td class="px-4 py-4">{{ number_format($item['effective_seller_percent'], 2) }}٪</td>
                                            @if($store->sellerCan('view_revenue'))<td class="px-4 py-4 text-slate-500 dark:text-slate-400">{{ number_format($item['platform_amount']) }} تومان</td>@endif
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif
    @endforeach

    <section class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-100 p-5 sm:px-7 dark:border-slate-800"><h2 class="text-lg font-black">ریز فروش‌های پرداخت‌شده</h2><p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">هر ردیف، یک آیتم سفارش است. درصد کمیسیون و مبالغ، همان مقادیر ثبت‌شده هنگام پرداخت هستند؛ سفارش‌های مستردشده در گزارش نیستند.</p></div>
        @if($report['recent_items']->isEmpty())
            <p class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">در این بازه فروشی ثبت نشده است.</p>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="ریز فروش‌های پرداخت‌شده؛ جدول قابل پیمایش افقی">
                <table class="w-full {{ $store->sellerCan('view_revenue') ? 'min-w-[850px]' : 'min-w-[480px]' }} text-right text-sm">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
                        <th scope="col" class="px-5 py-3 sm:px-7">سفارش / تاریخ</th><th scope="col" class="px-4 py-3">آیتم</th><th scope="col" class="px-4 py-3">تعداد</th>
                        @if($store->sellerCan('view_revenue'))<th scope="col" class="px-4 py-3">مبلغ فروش</th>@endif
                        @if($store->sellerCan('view_commission'))<th scope="col" class="px-4 py-3">درصد فروشگاه</th>@endif
                        @if($store->sellerCan('view_revenue'))<th scope="col" class="px-4 py-3">درآمد فروشگاه</th>@endif
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($report['recent_items'] as $item)
                            <tr>
                                <td class="px-5 py-4 sm:px-7"><div class="font-black" dir="ltr">#{{ $item->order?->order_number }}</div><div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $item->order?->paid_at ? jalali_date($item->order->paid_at, 'Y/m/d H:i') : 'تاریخ نامشخص' }}</div></td>
                                <td class="px-4 py-4"><div class="font-bold">{{ $item->report_title }}</div><div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $item->purchasable_type === $report['addon_type'] ? 'افزونه' : ($item->purchasable_type === $report['sale_item_type'] ? 'محصول' : 'سایر') }}</div></td>
                                <td class="px-4 py-4">{{ number_format($item->quantity) }}</td>
                                @if($store->sellerCan('view_revenue'))<td class="px-4 py-4">{{ number_format($item->final_total_amount) }} تومان</td>@endif
                                @if($store->sellerCan('view_commission'))<td class="px-4 py-4">{{ number_format($item->sellerCommissionBasisPoints() / 100, 2) }}٪</td>@endif
                                @if($store->sellerCan('view_revenue'))<td class="px-4 py-4 font-black text-emerald-600 dark:text-emerald-400">{{ number_format($item->sellerCommissionAmount()) }} تومان</td>@endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($report['recent_items']->hasPages())<div class="border-t border-slate-100 p-5 dark:border-slate-800">{{ $report['recent_items']->links() }}</div>@endif
        @endif
    </section>
@endsection
