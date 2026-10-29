@extends('front.marketplace.seller.layout')

@section('seller-title', 'نمای کلی')
@section('seller-heading', 'پنل فروشگاه ' . $store->title)
@section('seller-description', 'اطلاعات و بخش‌های در دسترس فروشگاه را یک‌جا دنبال کنید.')

@section('seller-content')
    @if(! $store->sellerCan('view_revenue') && ! $store->sellerCan('view_sales_count'))
        <p class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900">نمایش آمار فروش و درآمد این فروشگاه توسط مدیریت غیرفعال شده است.</p>
    @else
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><h2 class="text-sm font-black text-slate-700 dark:text-slate-200">عملکرد {{ $report['days'] }} روز اخیر</h2><span class="text-xs text-slate-500 dark:text-slate-400">فقط سفارش‌های پرداخت‌شده؛ سفارش‌های مستردشده محاسبه نشده‌اند.</span></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['permission' => 'view_revenue', 'label' => 'درآمد فروشگاه', 'value' => number_format((int) $report['summary']['seller']).' تومان', 'detail' => 'بر پایهٔ کمیسیون ثبت‌شده', 'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['permission' => 'view_sales_count', 'label' => 'سفارش‌های پرداخت‌شده', 'value' => number_format((int) $report['summary']['orders']), 'detail' => 'سفارش یکتا', 'color' => 'text-brand-600 dark:text-brand-300'],
            ['permission' => 'view_sales_count', 'label' => 'تعداد فروش محصول', 'value' => number_format((int) $report['summary']['units']), 'detail' => 'بدون احتساب افزونه به‌عنوان محصول', 'color' => 'text-sky-600 dark:text-sky-400'],
            ['permission' => 'view_revenue', 'label' => 'فروش کل', 'value' => number_format((int) $report['summary']['gross']).' تومان', 'detail' => 'محصول و افزونه', 'color' => 'text-violet-600 dark:text-violet-400'],
        ] as $card)
            @continue(! $store->sellerCan($card['permission']))
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                <div class="mt-4 break-all text-xl font-black sm:text-2xl {{ $card['color'] }}">{{ $card['value'] }}</div>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $card['detail'] }}</p>
            </div>
        @endforeach
    </div>
    @if(! $store->sellerCan('view_revenue'))
        <div class="mt-4 flex gap-2" aria-label="بازه گزارش">
            @foreach([7, 30, 90] as $range)
                <a href="{{ route('seller.stores.dashboard', [$store, 'days' => $range]) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold dark:border-slate-700">{{ $range }} روز</a>
            @endforeach
        </div>
    @endif
    @endif

    <div @class(['mt-5 grid gap-5', 'xl:grid-cols-[minmax(0,1.8fr)_minmax(280px,1fr)]' => $store->sellerCan('view_revenue')])>
        @if($store->sellerCan('view_revenue'))
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black">روند درآمد فروشگاه</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">مبالغ بر اساس سهم ثبت‌شده در زمان پرداخت هر سفارش محاسبه شده‌اند.</p>
                </div>
                <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" aria-label="بازه گزارش">
                    @foreach([7, 30, 90] as $range)
                        <a href="{{ route('seller.stores.dashboard', [$store, 'days' => $range]) }}" class="rounded-lg px-3 py-2 text-xs font-bold {{ $report['days'] === $range ? 'bg-white text-brand-600 shadow-sm dark:bg-slate-700 dark:text-brand-300' : 'text-slate-500 dark:text-slate-400' }}">{{ $range }} روز</a>
                    @endforeach
                </div>
            </div>
            <div id="seller-income-chart" class="mt-5 h-72 w-full" role="img" aria-label="نمودار درآمد روزانه فروشگاه" data-chart='@json($report['chart'])'></div>
            <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 border-t border-slate-100 pt-4 text-xs font-bold dark:border-slate-800">
                <span class="text-emerald-600 dark:text-emerald-400">سهم فروشگاه: {{ number_format($report['summary']['seller']) }} تومان</span>
                @if($store->sellerCan('view_commission'))
                    <span class="text-slate-500 dark:text-slate-400">سهم پلتفرم: {{ number_format($report['summary']['platform']) }} تومان</span>
                @endif
            </div>
        </section>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-black">دسترسی سریع</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">کارهای روزمرهٔ فروشگاه</p>
            @if($store->sellerCan('view_revenue'))
            <div class="mt-5 rounded-2xl bg-slate-900 p-4 text-white dark:bg-slate-800">
                <div class="text-[11px] font-bold text-slate-300">درآمد فروشگاه از ابتدا</div>
                <div class="mt-1 break-all text-xl font-black">{{ number_format($report['lifetime']['seller']) }} تومان</div>
                @if($store->sellerCan('view_sales_count'))
                    <div class="mt-1 text-[11px] text-slate-300">از {{ number_format($report['lifetime']['orders']) }} سفارش پرداخت‌شده</div>
                @endif
            </div>
            @endif
            @if($store->sellerCan('manage_withdrawals'))
            <a href="{{ route('seller.stores.withdrawals', $store) }}" class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 transition hover:border-emerald-400 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:hover:border-emerald-400/40">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">ماندهٔ قابل درخواست وجه</span>
                <strong class="break-all text-sm text-emerald-700 dark:text-emerald-300">{{ number_format($withdrawalBalance['available']) }} تومان ←</strong>
            </a>
            @endif
            <div class="mt-5 space-y-3">
                @foreach([
                    ['route' => 'seller.stores.products', 'title' => 'محصولات فروشگاه', 'count' => $productsCount, 'tone' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'],
                    ['route' => 'seller.stores.comments', 'title' => 'دیدگاه‌های محصولات', 'count' => $commentsCount, 'tone' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
                    ['route' => 'seller.stores.tickets', 'title' => 'تیکت‌های نیازمند پاسخ', 'count' => $sellerUnansweredTicketCount, 'tone' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
                ] as $item)
                    @php($permission = \App\Domain\Marketplace\Enums\SellerPermissionEnum::forRoute($item['route']))
                    @continue($permission && ! $store->sellerCan($permission))
                    <a href="{{ route($item['route'], $store) }}" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 p-4 transition hover:border-brand-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                        <span class="min-w-0 text-sm font-bold">{{ $item['title'] }}</span>
                        <span class="shrink-0 rounded-lg px-2.5 py-1 text-xs font-black {{ $item['tone'] }}">{{ number_format($item['count']) }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    @if($store->sellerCan('view_revenue'))
    <section class="mt-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="text-lg font-black">محصولات برتر</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">بر اساس سهم درآمد فروشگاه در همین بازه</p></div><a href="{{ route('seller.stores.products', $store) }}" class="text-xs font-bold text-brand-600 dark:text-brand-300">همهٔ محصولات</a></div>
        <div class="mt-5 grid gap-3 lg:grid-cols-2">
            @forelse($report['top_products'] as $topProduct)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="flex flex-wrap items-center justify-between gap-2"><span class="min-w-0 break-words text-sm font-bold">{{ $topProduct->title }}</span><strong class="break-all text-sm text-emerald-600 dark:text-emerald-400">{{ number_format((int) $topProduct->seller_amount) }} تومان</strong></div>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700"><div class="h-full rounded-full bg-emerald-500" style="width: {{ $report['top_products']->first()->seller_amount > 0 ? round(100 * $topProduct->seller_amount / $report['top_products']->first()->seller_amount) : 0 }}%"></div></div>
                    @if($store->sellerCan('view_sales_count'))
                        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">{{ number_format((int) $topProduct->units_count) }} فروش محصول</p>
                    @endif
                </div>
            @empty
                <p class="py-5 text-sm text-slate-500 dark:text-slate-400">در این بازه فروشی ثبت نشده است.</p>
            @endforelse
        </div>
    </section>
    @endif

    @if($store->sellerCan('view_tickets'))
    <section class="mt-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-black">آخرین تیکت‌ها</h2><a href="{{ route('seller.stores.tickets', $store) }}" class="text-xs font-bold text-brand-600 dark:text-brand-300">مشاهدهٔ همه</a></div>
        <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($recentTickets as $ticket)
                <a href="{{ route('seller.stores.tickets.show', [$store, $ticket]) }}" class="flex flex-wrap items-center justify-between gap-3 py-4 hover:text-brand-600 dark:hover:text-brand-300">
                    <span class="min-w-0 break-words text-sm font-bold">{{ $ticket->title }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $ticket->status->label() }} · {{ $ticket->last_message_at ? jalali_date($ticket->last_message_at, 'Y/m/d') : 'بدون پیام' }}</span>
                </a>
            @empty
                <p class="py-5 text-sm text-slate-500 dark:text-slate-400">هنوز تیکتی برای محصولات این فروشگاه ثبت نشده است.</p>
            @endforelse
        </div>
    </section>
    @endif
@endsection

@push('scripts')
    @if($store->sellerCan('view_revenue'))
    @vite('resources/js/front/seller-dashboard.js')
    @endif
@endpush
