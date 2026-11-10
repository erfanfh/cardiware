<x-front.sidebar.sidebar-item
    :title="get_setting('sidebarWidgets', 'tools_title')"
    :subtitle="get_setting('sidebarWidgets', 'tools_subtitle')"
    iconName="dashboard-square-03"
>
    <div class="grid grid-cols-2 gap-3">
        @foreach(get_setting('sidebarWidgets', 'tools_items') ?? [] as $item)
            @php
                $hasDescription = filled($item['description'] ?? null);
            @endphp

            <div
                @if($hasDescription)
                    x-data="{ open: false }"
                    x-id="['tool-description']"
                    @mouseenter="if (window.matchMedia('(hover: hover)').matches) open = true"
                    @mouseleave="if (!$el.contains(document.activeElement)) open = false"
                    @focusin="open = true"
                    @focusout="if (!$el.contains($event.relatedTarget)) open = false"
                    @click.outside="open = false"
                    @keydown.escape.stop.prevent="open = false"
                    :class="open ? 'z-50' : ''"
                @endif
                class="group relative min-w-0"
            >
                <a href="{{ $item['url'] }}"
                   @if($hasDescription) :aria-describedby="$id('tool-description')" @endif
                   class="flex min-w-0 items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 transition duration-200 hover:border-brand-200 hover:bg-brand-50/40 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-brand-500/30 dark:hover:bg-slate-800 dark:hover:shadow-black/20 dark:focus-visible:ring-offset-slate-900">
                    <div
                        class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900">
                        <x-front.responsive-image
                            :fallback="$item['image']['path'] ?? null"
                            :alt="$item['title'] ?? ''"
                            :title="$item['title'] ?? null"
                            variant="thumbnail"
                            class="w-full h-full object-cover"
                            wrapper-class="h-11 w-11 rounded-2xl"
                            :lazy="true"
                            :async="true"
                            sizes="44px"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-xs font-extrabold leading-6 text-slate-900 transition-colors group-hover:text-brand-700 dark:text-slate-100 dark:group-hover:text-brand-300">{{ $item['title'] }}</div>
                        @if(filled($item['subtitle'] ?? null))
                            <div @class(['mt-0.5 truncate text-[0.65rem] leading-5 text-slate-500 dark:text-slate-400', 'pl-4' => $hasDescription])>{{ $item['subtitle'] }}</div>
                        @endif
                    </div>
                </a>

                @if($hasDescription)
                    <button
                        type="button"
                        aria-label="توضیحات {{ $item['title'] }}"
                        :aria-describedby="$id('tool-description')"
                        :aria-expanded="open.toString()"
                        @click.stop="open = true"
                        class="absolute bottom-1 left-1 grid h-6 w-6 cursor-help place-items-center rounded-full text-slate-400 transition hover:bg-brand-50 hover:text-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-brand-300"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 11v6M12 7h.01" />
                        </svg>
                    </button>

                    <div
                        :id="$id('tool-description')"
                        role="tooltip"
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1.5 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        @class([
                            'absolute bottom-full z-50 w-72 max-w-[calc(100vw-2rem)] pb-2.5',
                            'right-0' => $loop->odd,
                            'left-0' => $loop->even,
                        ])
                    >
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white text-right shadow-xl shadow-slate-900/15 ring-1 ring-black/5 dark:border-slate-700 dark:bg-slate-800 dark:shadow-black/40 dark:ring-white/5">
                            {{-- سربرگ پاپ‌آپ: تصویر + عنوان ابزار --}}
                            <div class="flex items-center gap-2.5 border-b border-slate-100 bg-slate-50/70 px-3 py-2.5 dark:border-slate-700/70 dark:bg-slate-900/60">
                                <span class="grid h-9 w-9 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950">
                                    <x-front.responsive-image
                                        :fallback="$item['image']['path'] ?? null"
                                        :alt="''"
                                        variant="thumbnail"
                                        class="w-full h-full object-cover"
                                        wrapper-class="h-9 w-9 rounded-xl"
                                        :lazy="true"
                                        :async="true"
                                        sizes="36px"
                                    />
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-xs font-extrabold text-slate-900 dark:text-slate-100">{{ $item['title'] }}</span>
                                    @if(filled($item['subtitle'] ?? null))
                                        <span class="block truncate text-[0.65rem] text-slate-500 dark:text-slate-400">{{ $item['subtitle'] }}</span>
                                    @endif
                                </span>
                            </div>
                            {{-- توضیح کامل ابزار --}}
                            <p class="max-h-44 overflow-y-auto px-3.5 py-2.5 text-xs leading-6 break-words text-slate-600 dark:text-slate-300">{{ $item['description'] }}</p>
                            <span
                                aria-hidden="true"
                                @class([
                                    'absolute -bottom-1 h-2.5 w-2.5 rotate-45 border-b border-r border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800',
                                    'right-6' => $loop->odd,
                                    'left-6' => $loop->even,
                                ])
                            ></span>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</x-front.sidebar.sidebar-item>
