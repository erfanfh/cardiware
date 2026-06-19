@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
        JS
        : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation">
            {{-- Mobile --}}
            <div class="flex items-center justify-center gap-2 sm:hidden">
                <span>
                    @if ($paginator->onFirstPage())
                        <span
                            class="inline-flex cursor-default items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                            {!! __('pagination.previous') !!}
                        </span>
                    @else
                        <button
                            type="button"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-200 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:shadow-black/20 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:focus:ring-brand-500/20"
                        >
                            {!! __('pagination.previous') !!}
                        </button>
                    @endif
                </span>

                <span>
                    @if ($paginator->hasMorePages())
                        <button
                            type="button"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                            class="inline-flex items-center justify-center rounded-full border border-brand-200 bg-brand-50 px-3 py-2 text-xs text-brand-700 shadow-sm transition hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-200 disabled:opacity-50 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 dark:shadow-black/20 dark:hover:bg-brand-500/15 dark:focus:ring-brand-500/20"
                        >
                            {!! __('pagination.next') !!}
                        </button>
                    @else
                        <span
                            class="inline-flex cursor-default items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                            {!! __('pagination.next') !!}
                        </span>
                    @endif
                </span>
            </div>

            {{-- Desktop --}}
            <div class="hidden sm:flex sm:flex-col sm:items-center sm:gap-2">
                <div class="flex items-center justify-center">
                    <span
                        class="inline-flex items-center gap-1 rounded-2xl border border-slate-200 bg-white p-1 shadow-sm rtl:flex-row-reverse dark:border-slate-700 dark:bg-slate-800/80 dark:shadow-black/20">
                        <span>
                            {{-- Previous Page Link --}}
                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                    <span
                                        class="inline-flex h-9 w-9 cursor-default items-center justify-center rounded-xl text-slate-400 dark:text-slate-500"
                                        aria-hidden="true"
                                    >
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                                    class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brand-200 active:bg-slate-100 disabled:opacity-50 dark:text-slate-300 dark:hover:bg-slate-700 dark:focus:ring-brand-500/20 dark:active:bg-slate-700/80"
                                    aria-label="{{ __('pagination.previous') }}"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            @endif
                        </span>

                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span
                                        class="inline-flex h-9 min-w-9 cursor-default items-center justify-center rounded-xl px-2.5 text-[13px] text-slate-400 dark:text-slate-500">
                                        {{ $element }}
                                    </span>
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <span aria-current="page">
                                                <span
                                                    class="inline-flex h-9 min-w-9 cursor-default items-center justify-center rounded-xl bg-brand-500 px-2.5 text-[13px] font-extrabold text-white shadow-sm dark:bg-brand-500 dark:shadow-black/20">
                                                    {{ $page }}
                                                </span>
                                            </span>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                                class="inline-flex h-9 min-w-9 cursor-pointer items-center justify-center rounded-xl px-2.5 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brand-200 active:bg-slate-100 disabled:opacity-50 dark:text-slate-300 dark:hover:bg-slate-700 dark:focus:ring-brand-500/20 dark:active:bg-slate-700/80"
                                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                            >
                                                {{ $page }}
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        @endforeach

                        <span>
                            {{-- Next Page Link --}}
                            @if ($paginator->hasMorePages())
                                <button
                                    type="button"
                                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                                    class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-slate-600 transition hover:bg-brand-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brand-200 active:bg-brand-100 disabled:opacity-50 dark:text-slate-300 dark:hover:bg-brand-500/10 dark:focus:ring-brand-500/20 dark:active:bg-brand-500/15"
                                    aria-label="{{ __('pagination.next') }}"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            @else
                                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                    <span
                                        class="inline-flex h-9 w-9 cursor-default items-center justify-center rounded-xl text-slate-400 dark:text-slate-500"
                                        aria-hidden="true"
                                    >
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>
