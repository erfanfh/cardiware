@php
    $videosBlock = app(\App\Application\System\Actions\Home\GetHomeVideosBlockAction::class)->execute();
@endphp

@if($videosBlock['posts']->isNotEmpty())
    <section id="video-tutorials">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-400 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                    @include('icons.play')
                </span>

                <h3 class="text-base font-extrabold dark:text-slate-100">{{ $videosBlock['title'] }}</h3>
            </div>

            <a href="{{ $videosBlock['buttonUrl'] }}"
               class="text-sm font-semibold text-rose-600 hover:text-rose-700 inline-flex items-center gap-2 dark:text-rose-400 dark:hover:text-rose-300">
                مشاهده همه
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    @include('icons.arrow-left-02')
                </span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($videosBlock['posts'] as $post)
                <a href="{{ front_route('contents.detail', $post) }}"
                   class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-sm transition duration-300 hover:border-rose-500/40 hover:shadow-xl hover:shadow-rose-500/10 dark:border-slate-700 dark:hover:border-rose-500/40">
                    {{-- تصویر با اوورلی قرمز-تیره ویدیو --}}
                    <div class="relative aspect-video overflow-hidden bg-slate-950">
                        <x-front.responsive-image
                            :media="$post->picture"
                            :fallback="$post->picture?->url"
                            :alt="$post->title"
                            :title="$post->title"
                            variant="medium_large"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                            wrapper-class="h-full w-full absolute inset-0"
                            :lazy="true"
                            :async="true"
                            :fetchpriority="null"
                            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                        />

                        {{-- گرادیان ویدیویی: تیره از پایین + قرمز هاور --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent transition duration-300 group-hover:from-rose-950/70 group-hover:via-rose-950/10"></div>

                        {{-- نشان مدت زمان / ویدیو — گوشه پایین راست --}}
                        <span class="absolute bottom-2 right-2 inline-flex items-center gap-1 rounded-lg bg-black/60 px-1.5 py-0.5 text-[10px] font-bold text-white/90 backdrop-blur-sm">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                            ویدیو
                        </span>

                        {{-- دکمه پخش: قرمز اپارات با هاله روی هاور --}}
                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 grid h-12 w-12 place-items-center rounded-full bg-rose-600/95 text-white shadow-[0_0_0_6px_rgba(244,63,94,.18)] transition duration-300 group-hover:scale-115 group-hover:bg-rose-500 group-hover:shadow-[0_0_0_10px_rgba(244,63,94,.15)] [&_svg]:h-5 [&_svg]:w-5">
                            @include('icons.play')
                        </span>
                    </div>

                    {{-- متن --}}
                    <div class="relative p-4">
                        <div class="text-sm font-extrabold text-white line-clamp-2 leading-7 transition group-hover:text-rose-200">
                            {{ $post->title }}
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-xs text-slate-400 transition group-hover:text-rose-300/70">
                                {{ time_ago($post->published_at) }}
                            </span>

                            <span class="inline-flex items-center gap-2 text-xs font-extrabold text-rose-400 transition group-hover:text-rose-300 group-hover:gap-2.5">
                                مشاهده
                                <span class="[&_svg]:w-4 [&_svg]:h-4">
                                    @include('icons.arrow-left-02')
                                </span>
                            </span>
                        </div>

                        {{-- خط زیرین قرمز روی هاور --}}
                        <span class="absolute bottom-0 left-4 right-4 h-0.5 origin-right scale-x-0 rounded-full bg-gradient-to-l from-rose-500 to-rose-400 transition-transform duration-300 group-hover:scale-x-100" aria-hidden="true"></span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
