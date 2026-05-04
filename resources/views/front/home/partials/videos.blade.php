@php
    $videosBlock = app(\App\Application\System\Actions\Home\GetHomeVideosBlockAction::class)->execute();
@endphp

<section id="video-tutorials">
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                @include('icons.play')
            </span>

            <h3 class="text-base font-extrabold dark:text-slate-100">
                {{ $videosBlock['title'] }}
            </h3>
        </div>
    </div>

    <div
        class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($videosBlock['posts'] as $post)
                    <a href="{{ front_route('contents.detail', $post) }}"
                       class="group rounded-2xl border border-slate-200 bg-slate-900 overflow-hidden shadow-sm hover:shadow-xl hover:scale-[102%] transition duration-300 dark:border-slate-700 dark:shadow-black/20 dark:hover:shadow-black/30">
                        <div class="relative h-40">
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

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-white truncate">
                                        {{ $post->title }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-xs text-white/60">
                                    انتشار: {{ time_ago($post->published_at) }}
                                </span>

                                <span class="inline-flex items-center gap-2 text-xs font-extrabold text-brand-200">
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

            <div
                class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 dark:border-slate-700 dark:bg-slate-950">
                <div class="text-sm text-slate-700 leading-7 dark:text-slate-300">
                    {!! $videosBlock['description'] !!}
                </div>

                <a href="{{ $videosBlock['buttonUrl'] }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-white border border-slate-200 px-4 py-2 text-sm font-extrabold text-brand-700 hover:bg-brand-50 transition dark:border-slate-700 dark:bg-slate-900 dark:text-brand-300 dark:hover:bg-brand-500/10">
                    {{ $videosBlock['buttonTitle'] }}
                    <span class="[&_svg]:w-4 [&_svg]:h-4">
                        @include('icons.arrow-left-02')
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>
