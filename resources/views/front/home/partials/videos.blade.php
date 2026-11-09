@php
    $videosBlock = app(\App\Application\System\Actions\Home\GetHomeVideosBlockAction::class)->execute();
@endphp

@if($videosBlock['posts']->isNotEmpty())
    <section id="video-tutorials">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                    @include('icons.play')
                </span>

                <h3 class="text-base font-extrabold dark:text-slate-100">
                    {{ $videosBlock['title'] }}
                </h3>
            </div>

            <a href="{{ $videosBlock['buttonUrl'] }}"
               class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2 dark:text-brand-300 dark:hover:text-brand-200">
                مشاهده همه
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    @include('icons.arrow-left-02')
                </span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($videosBlock['posts'] as $post)
                <a href="{{ front_route('contents.detail', $post) }}"
                   class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-sm transition duration-300 hover:scale-[1.015] hover:border-brand-500/40 hover:shadow-xl dark:border-slate-700 dark:shadow-black/20 dark:hover:shadow-black/40">
                    <div class="relative aspect-video bg-slate-950">
                        <x-front.responsive-image
                            :media="$post->picture"
                            :fallback="$post->picture?->url"
                            :alt="$post->title"
                            :title="$post->title"
                            variant="medium_large"
                            class="h-full w-full object-cover opacity-90"
                            wrapper-class="h-full w-full absolute inset-0"
                            :lazy="true"
                            :async="true"
                            :fetchpriority="null"
                            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                        />

                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>

                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 grid h-11 w-11 place-items-center rounded-full bg-brand-500/95 text-white shadow-[0_0_0_6px_rgba(249,115,22,.22)] transition duration-300 group-hover:scale-110 group-hover:bg-brand-400 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                            @include('icons.play')
                        </span>
                    </div>

                    <div class="p-4">
                        <div class="text-sm font-extrabold text-white line-clamp-2 leading-7">
                            {{ $post->title }}
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-xs text-slate-400">
                                {{ time_ago($post->published_at) }}
                            </span>

                            <span class="inline-flex items-center gap-2 text-xs font-extrabold text-brand-300 transition group-hover:text-brand-200">
                                مشاهده
                                <span class="[&_svg]:w-4 [&_svg]:h-4">
                                    @include('icons.arrow-left-02')
                                </span>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
