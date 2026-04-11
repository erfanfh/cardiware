@php
    $downloadSections = $post->downloadSections
        ->loadMissing('entries')
        ->sortBy([
            ['sort_order', 'asc'],
            ['id', 'asc'],
        ])
        ->values();
@endphp

@if($downloadSections->isNotEmpty())
    <section x-data="downloadsBlock()" class="mt-6 space-y-4">
        @foreach($downloadSections as $section)
            @php
                $entries = $section->entries
                    ->sortBy([
                        ['sort_order', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->values();

                $linkEntries = $entries->filter(fn ($entry) => $entry->isLink())->values();

                $copyAllText = $linkEntries
                    ->map(fn ($entry) => $entry->title . ': ' . route('downloads.entry.redirect', $entry->public_id))
                    ->implode("\n");
            @endphp

            @if($entries->isNotEmpty())
                <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                    <div
                        class="border-b border-slate-200 bg-[linear-gradient(135deg,#fff7ed,#ffffff)] px-5 py-4">
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-orange-200 bg-orange-50 text-orange-600 whitespace-nowrap">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2.1">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <path d="M7 10l5 5 5-5"></path>
                                    <path d="M12 15V3"></path>
                                </svg>
                            </span>

                            <h2 class="truncate text-sm font-extrabold text-slate-900 sm:text-base">
                                {{ $section->title }}
                            </h2>

                            <div class="mr-auto flex gap-2">
                                <button
                                    type="button"
                                    @click="copyText(
                                        @js($section->password ?? ''),
                                        $event.currentTarget,
                                        '{{ filled($section->password) ? 'رمز فایل کپی شد.' : 'رمزی برای این بخش ثبت نشده است.' }}'
                                    )"
                                    data-default-text="کپی رمز"
                                    data-copied-text="کپی شد"
                                    class="js-copy-button inline-flex leading-9 items-center gap-1.5 cursor-pointer whitespace-nowrap rounded-2xl bg-slate-900 px-4 text-xs font-extrabold text-white transition hover:bg-slate-700 active:bg-slate-800"
                                >
                                    <span class="js-copy-icon-default inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                             viewBox="0 0 24 24">
                                            <g fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M14.491 15.5h.009m-5 0h.009"/>
                                                <path stroke-width="1.5"
                                                      d="M4.268 18.845c.225 1.67 1.608 2.979 3.292 3.056c1.416.065 2.855.099 4.44.099s3.024-.034 4.44-.1c1.684-.076 3.067-1.385 3.292-3.055c.147-1.09.268-2.207.268-3.345s-.121-2.255-.268-3.345c-.225-1.67-1.608-2.979-3.292-3.056A95 95 0 0 0 12 9c-1.585 0-3.024.034-4.44.1c-1.684.076-3.067 1.385-3.292 3.055C4.12 13.245 4 14.362 4 15.5s.121 2.255.268 3.345Z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M7.5 9V6.5a4.5 4.5 0 0 1 9 0V9"/>
                                            </g>
                                        </svg>
                                    </span>

                                    <span class="js-copy-icon-success hidden items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                             viewBox="0 0 24 24" fill="none">
                                            <path d="M5 12.5l4.2 4.2L19 7" stroke="currentColor" stroke-width="2"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>

                                    <span class="js-copy-text">کپی رمز</span>
                                </button>

                                <button
                                    type="button"
                                    @click="copyText(@js($copyAllText), $event.currentTarget, 'همه لینک‌های این بخش کپی شد.')"
                                    data-default-text="کپی لینک ها"
                                    data-copied-text="کپی شد"
                                    class="js-copy-button inline-flex leading-9 gap-1.5 cursor-pointer whitespace-nowrap items-center justify-center rounded-2xl bg-orange-500 px-4 text-[0.785rem] mr-auto font-extrabold text-white transition hover:bg-orange-600 active:bg-orange-700"
                                >
                                    <span class="js-copy-icon-default inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                             viewBox="0 0 24 24">
                                            <g fill="none" stroke="currentColor" stroke-linecap="round"
                                               stroke-linejoin="round" stroke-width="1.5">
                                                <path
                                                    d="M16.964 8.982c-.003-2.95-.047-4.478-.906-5.524a4 4 0 0 0-.553-.554C14.4 2 12.76 2 9.48 2s-4.92 0-6.024.905a4 4 0 0 0-.553.554C1.998 4.56 1.998 6.2 1.998 9.48s0 4.92.906 6.023q.25.304.553.553c1.046.86 2.575.904 5.525.906"/>
                                                <path
                                                    d="m14.028 9.025l2.966-.043m-2.98 13.02l2.966-.043m4.992-7.937l-.028 2.96M9.01 14.036l-.028 2.96m2.505-7.971c-.832.149-2.17.302-2.477 2.024m10.485 10.91c.835-.137 2.174-.27 2.508-1.986M19.495 9.025c.832.149 2.17.302 2.477 2.024M11.5 21.957c-.833-.148-2.17-.301-2.478-2.023"/>
                                            </g>
                                        </svg>
                                    </span>

                                    <span class="js-copy-icon-success hidden items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                             viewBox="0 0 24 24" fill="none">
                                            <path d="M5 12.5l4.2 4.2L19 7" stroke="currentColor" stroke-width="2"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>

                                    <span class="js-copy-text">کپی لینک ها</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-5 space-y-4">
                        @foreach($entries as $entry)
                            @if($entry->isLink())
                                <div
                                    class="group rounded-2xl bg-slate-100/80 p-3">
                                    <div class="flex gap-3 items-center justify-between flex-wrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                            <a href="{{ route('downloads.entry.redirect', $entry->public_id) }}"
                                               class="truncate text-sm font-extrabold text-slate-900">
                                                {{ $entry->title }}
                                            </a>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                @click="copyText(@js(route('downloads.entry.redirect', $entry->public_id)), $event.currentTarget, 'لینک دانلود کپی شد.')"
                                                data-default-text="کپی لینک"
                                                data-copied-text="کپی شد"
                                                class="js-copy-button inline-flex gap-1.5 cursor-pointer whitespace-nowrap leading-8 text-xs items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 font-extrabold text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
                                            >
                                                <span class="js-copy-icon-default inline-flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                         viewBox="0 0 24 24">
                                                        <g fill="none" stroke="currentColor" stroke-linecap="round"
                                                           stroke-linejoin="round" stroke-width="1.5">
                                                            <path
                                                                d="M16.964 8.982c-.003-2.95-.047-4.478-.906-5.524a4 4 0 0 0-.553-.554C14.4 2 12.76 2 9.48 2s-4.92 0-6.024.905a4 4 0 0 0-.553.554C1.998 4.56 1.998 6.2 1.998 9.48s0 4.92.906 6.023q.25.304.553.553c1.046.86 2.575.904 5.525.906"/>
                                                            <path
                                                                d="m14.028 9.025l2.966-.043m-2.98 13.02l2.966-.043m4.992-7.937l-.028 2.96M9.01 14.036l-.028 2.96m2.505-7.971c-.832.149-2.17.302-2.477 2.024m10.485 10.91c.835-.137 2.174-.27 2.508-1.986M19.495 9.025c.832.149 2.17.302 2.477 2.024M11.5 21.957c-.833-.148-2.17-.301-2.478-2.023"/>
                                                        </g>
                                                    </svg>
                                                </span>

                                                <span class="js-copy-icon-success hidden items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                         viewBox="0 0 24 24" fill="none">
                                                        <path d="M5 12.5l4.2 4.2L19 7" stroke="currentColor"
                                                              stroke-width="2" stroke-linecap="round"
                                                              stroke-linejoin="round"/>
                                                    </svg>
                                                </span>

                                                <span class="js-copy-text">کپی لینک</span>
                                            </button>

                                            <a
                                                href="{{ route('downloads.entry.redirect', $entry->public_id) }}"
                                                class="inline-flex gap-1.5 leading-8 text-xs whitespace-nowrap items-center justify-center rounded-2xl bg-orange-500 px-4 font-extrabold text-white transition hover:bg-orange-600 active:bg-orange-700"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                     viewBox="0 0 24 24">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round"
                                                          stroke-linejoin="round" stroke-width="1.5"
                                                          d="M3 17c0 .93 0 1.395.102 1.777a3 3 0 0 0 2.121 2.121C5.605 21 6.07 21 7 21h10c.93 0 1.395 0 1.776-.102a3 3 0 0 0 2.122-2.121C21 18.395 21 17.93 21 17m-4.5-5.5S13.186 16 12 16s-4.5-4.5-4.5-4.5M12 15V3"/>
                                                </svg>

                                                دانلود
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="min-w-0 flex-1 py-1">
                                    @if($entry->title)
                                        <div class="text-sm font-extrabold text-slate-900">
                                            {{ $entry->title }}
                                        </div>
                                    @endif

                                    @if($entry->content)
                                        <div
                                            class="mt-2 break-words px-3 py-2.5 text-sm leading-7 text-slate-700">
                                            {{ $entry->content }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if(filled($section->password))
                        <div class="border-t border-slate-200 mt-2 px-5 py-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-brand-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                             viewBox="0 0 24 24">
                                            <g fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M14.491 15.5h.009m-5 0h.009"/>
                                                <path stroke-width="1.5"
                                                      d="M4.268 18.845c.225 1.67 1.608 2.979 3.292 3.056c1.416.065 2.855.099 4.44.099s3.024-.034 4.44-.1c1.684-.076 3.067-1.385 3.292-3.055c.147-1.09.268-2.207.268-3.345s-.121-2.255-.268-3.345c-.225-1.67-1.608-2.979-3.292-3.056A95 95 0 0 0 12 9c-1.585 0-3.024.034-4.44.1c-1.684.076-3.067 1.385-3.292 3.055C4.12 13.245 4 14.362 4 15.5s.121 2.255.268 3.345Z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M7.5 9V6.5a4.5 4.5 0 0 1 9 0V9"/>
                                            </g>
                                        </svg>
                                    </span>

                                    <div class="text-sm">
                                        <span class="font-extrabold text-brand-600">رمز فایل ها:</span>
                                        <span class="font-extrabold text-slate-800 mr-2" dir="ltr">
                                            {{ $section->password }}
                                        </span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="copyText(
                                        @js($section->password ?? ''),
                                        $event.currentTarget,
                                        '{{ filled($section->password) ? 'رمز فایل کپی شد.' : 'رمزی برای این بخش ثبت نشده است.' }}'
                                    )"
                                    data-default-text="کپی رمز"
                                    data-copied-text="کپی شد"
                                    class="js-copy-button inline-flex leading-9 items-center gap-1.5 cursor-pointer whitespace-nowrap rounded-2xl bg-slate-900 px-4 text-xs font-extrabold text-white transition hover:bg-slate-700"
                                >
                                    <span class="js-copy-icon-default inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                             viewBox="0 0 24 24">
                                            <g fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M14.491 15.5h.009m-5 0h.009"/>
                                                <path stroke-width="1.5"
                                                      d="M4.268 18.845c.225 1.67 1.608 2.979 3.292 3.056c1.416.065 2.855.099 4.44.099s3.024-.034 4.44-.1c1.684-.076 3.067-1.385 3.292-3.055c.147-1.09.268-2.207.268-3.345s-.121-2.255-.268-3.345c-.225-1.67-1.608-2.979-3.292-3.056A95 95 0 0 0 12 9c-1.585 0-3.024.034-4.44.1c-1.684.076-3.067 1.385-3.292 3.055C4.12 13.245 4 14.362 4 15.5s.121 2.255.268 3.345Z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M7.5 9V6.5a4.5 4.5 0 0 1 9 0V9"/>
                                            </g>
                                        </svg>
                                    </span>

                                    <span class="js-copy-icon-success hidden items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                             viewBox="0 0 24 24" fill="none">
                                            <path d="M5 12.5l4.2 4.2L19 7" stroke="currentColor" stroke-width="2"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>

                                    <span class="js-copy-text">کپی رمز</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </section>
@endif
