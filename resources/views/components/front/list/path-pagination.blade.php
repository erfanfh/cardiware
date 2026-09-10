@props([
    'model',
    'baseRoute',
    'pageRoute',
    'routeParameters' => [],
])

@php
    $window = \Illuminate\Pagination\UrlWindow::make($model);
    $elements = array_filter([
        $window['first'],
        is_array($window['slider']) ? '…' : null,
        $window['slider'],
        is_array($window['last']) ? '…' : null,
        $window['last'],
    ]);
    $query = request()->except('page');
    $pageUrl = static function (int $page) use ($baseRoute, $pageRoute, $routeParameters, $query): string {
        $parameters = array_merge($query, $routeParameters);

        if ($page <= 1) {
            return front_route($baseRoute, $parameters);
        }

        return front_route($pageRoute, [...$parameters, 'page' => $page]);
    };
@endphp

@if($model->hasPages())
    <nav aria-label="صفحه‌بندی محصولات" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-center text-xs font-bold text-slate-500 dark:text-slate-400 sm:text-right">
            نمایش {{ number_format($model->firstItem()) }} تا {{ number_format($model->lastItem()) }}
            از {{ number_format($model->total()) }} محصول
        </p>

        <div class="flex flex-wrap items-center justify-center gap-1.5" dir="ltr">
            @if($model->onFirstPage())
                <span aria-disabled="true" class="grid h-10 min-w-10 cursor-not-allowed place-items-center rounded-xl border border-slate-200 bg-slate-100 px-3 text-xs font-black text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                    قبلی
                </span>
            @else
                <a href="{{ $pageUrl($model->currentPage() - 1) }}" rel="prev" class="grid h-10 min-w-10 place-items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-700 transition hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-brand-600 dark:hover:text-brand-300">
                    قبلی
                </a>
            @endif

            @foreach($elements as $element)
                @if(is_string($element))
                    <span class="grid h-10 min-w-8 place-items-center text-slate-400">{{ $element }}</span>
                @else
                    @foreach($element as $page => $unusedUrl)
                        @if($page === $model->currentPage())
                            <span aria-current="page" class="grid h-10 min-w-10 place-items-center rounded-xl bg-brand-600 px-3 text-xs font-black text-white shadow-sm">
                                {{ number_format($page) }}
                            </span>
                        @else
                            <a href="{{ $pageUrl($page) }}" aria-label="رفتن به صفحه {{ $page }}" class="grid h-10 min-w-10 place-items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-700 transition hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-brand-600 dark:hover:text-brand-300">
                                {{ number_format($page) }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if($model->hasMorePages())
                <a href="{{ $pageUrl($model->currentPage() + 1) }}" rel="next" class="grid h-10 min-w-10 place-items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-700 transition hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-brand-600 dark:hover:text-brand-300">
                    بعدی
                </a>
            @else
                <span aria-disabled="true" class="grid h-10 min-w-10 cursor-not-allowed place-items-center rounded-xl border border-slate-200 bg-slate-100 px-3 text-xs font-black text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                    بعدی
                </span>
            @endif
        </div>
    </nav>
@endif
