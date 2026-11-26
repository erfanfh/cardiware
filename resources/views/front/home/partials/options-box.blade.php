@php
    $items = get_setting('home', 'services_options_items') ?? [];
@endphp

@if(count($items) > 0)
    <section>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($items as $item)
                @php
                    $title = $item['title'] ?? '';
                    $subtitle = $item['subtitle'] ?? '';
                    $link = filled($item['link'] ?? null) ? $item['link'] : '#';
                    $icon = filled($item['icon'] ?? null) ? $item['icon'] : '✨';
                    $theme = $item['theme'] ?? 'dark';
                    $isDark = $theme === 'dark';
                    $buttonText = filled($item['button_text'] ?? null) ? $item['button_text'] : 'View';
                @endphp

                <a href="{{ $link }}"
                   class="group relative overflow-hidden rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg transition dark:border-slate-700 dark:hover:shadow-black/20
                          {{ $isDark ? 'bg-gradient-to-br from-slate-900 to-slate-800' : 'bg-white dark:bg-slate-900' }}">

                    @if($isDark)
                        <div
                            class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_20%_30%,#fb923c,transparent_55%),radial-gradient(circle_at_80%_70%,#60a5fa,transparent_55%)]"></div>

                        <div class="absolute -top-10 -left-10 h-28 w-28 rounded-full bg-brand-500/30 blur-2xl"></div>
                    @else
                        <div
                            class="absolute inset-0 bg-gradient-to-l from-brand-50 via-white to-white dark:from-brand-500/10 dark:via-slate-900 dark:to-slate-900"></div>

                        <div class="absolute top-4 left-5 h-3 w-3 rounded-full bg-brand-500"></div>

                        <div
                            class="absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-brand-100 blur-xl dark:bg-brand-500/10"></div>
                    @endif

                    <div class="relative p-5 flex items-center justify-between gap-4">
                        <div>
                            <div
                                class="font-black text-lg {{ $isDark ? 'text-white' : 'text-slate-900 dark:text-slate-100' }}">
                                {{ $title }}
                            </div>

                            @if(filled($subtitle))
                                <div
                                    class="text-sm mt-1 {{ $isDark ? 'text-white/80' : 'text-slate-600 dark:text-slate-300' }}">
                                    {{ $subtitle }}
                                </div>
                            @endif

                            <div
                                class="mt-4 inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold transition
                                       {{ $isDark
                                            ? 'bg-brand-500 text-white group-hover:bg-brand-600 dark:group-hover:bg-brand-400'
                                            : 'border border-slate-200 bg-white text-slate-800 group-hover:bg-brand-50 group-hover:border-brand-200 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:group-hover:border-brand-500/30 dark:group-hover:bg-brand-500/10'
                                       }}">
                                {{ $buttonText }}
                                <span
                                    class="font-black {{ $isDark ? '' : 'text-brand-700 dark:text-brand-300' }}">←</span>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <div
                                class="h-20 w-24 rounded-2xl grid place-items-center
                                       {{ $isDark
                                            ? 'bg-white/10 border border-white/10 backdrop-blur'
                                            : 'bg-brand-500 text-white shadow-lg dark:shadow-black/20'
                                       }}">
                                <span class="text-3xl">{{ $icon }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
