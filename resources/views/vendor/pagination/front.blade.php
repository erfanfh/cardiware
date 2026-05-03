@php
    $baseUrl = request()->attributes->get('seo_pagination_base_url');

    $pageUrl = function (int $page) use ($baseUrl, $paginator): string {
        if (! $baseUrl) {
            return $paginator->url($page);
        }

        $baseUrl = rtrim($baseUrl, '/') . '/';

        if ($page <= 1) {
            return $baseUrl;
        }

        return $baseUrl . 'page/' . $page . '/';
    };

    $previousPageUrl = $paginator->onFirstPage()
        ? null
        : $pageUrl($paginator->currentPage() - 1);

    $nextPageUrl = $paginator->hasMorePages()
        ? $pageUrl($paginator->currentPage() + 1)
        : null;
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation">
        {{-- Mobile --}}
        <div class="flex items-center justify-center gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-400 cursor-default dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a
                    href="{{ $previousPageUrl }}"
                    rel="prev"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:shadow-black/20 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:focus:ring-brand-500/20">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $nextPageUrl }}"
                    rel="next"
                    class="inline-flex items-center justify-center rounded-full border border-brand-200 bg-brand-50 px-3 py-2 text-xs text-brand-700 shadow-sm transition hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-200 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 dark:shadow-black/20 dark:hover:bg-brand-500/20 dark:focus:ring-brand-500/20">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-400 cursor-default dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:flex-col sm:items-center sm:gap-2">
            <div class="flex items-center justify-center">
                <span
                    class="inline-flex items-center gap-1 rounded-2xl border border-slate-200 bg-white p-1 rtl:flex-row-reverse dark:border-slate-700 dark:bg-slate-900">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 cursor-default dark:text-slate-600"
                                aria-hidden="true">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </span>
                    @else
                        <a
                            href="{{ $previousPageUrl }}"
                            rel="prev"
                            class="cursor-pointer inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brand-200 active:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 dark:focus:ring-brand-500/20 dark:active:bg-slate-700"
                            aria-label="{{ __('pagination.previous') }}">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- Three Dots Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span
                                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl px-2.5 text-[13px] text-slate-400 cursor-default dark:text-slate-600">
                                    {{ $element }}
                                </span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span
                                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl bg-brand-500 px-2.5 text-[13px] font-extrabold text-white shadow-sm cursor-default dark:bg-brand-500 dark:shadow-black/20">
                                            {{ $page }}
                                        </span>
                                    </span>
                                @else
                                    <a
                                        href="{{ $pageUrl((int) $page) }}"
                                        class="cursor-pointer inline-flex h-9 min-w-9 items-center justify-center rounded-xl px-2.5 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brand-200 active:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 dark:focus:ring-brand-500/20 dark:active:bg-slate-700"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a
                            href="{{ $nextPageUrl }}"
                            rel="next"
                            class="cursor-pointer inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-600 transition hover:bg-brand-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brand-200 active:bg-brand-100 dark:text-slate-300 dark:hover:bg-brand-500/10 dark:focus:ring-brand-500/20 dark:active:bg-brand-500/20"
                            aria-label="{{ __('pagination.next') }}">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 cursor-default dark:text-slate-600"
                                aria-hidden="true">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
