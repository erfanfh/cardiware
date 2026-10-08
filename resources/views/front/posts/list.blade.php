@extends('front.master')

@section('content')
    @php
        $boxTitle = get_setting('posts', 'first_box_title') ?: 'آخرین نوشته ها';
        $boxSubtitle = get_setting('posts', 'first_box_subtitle');
        if(request()->routeIs('posts.categories') and isset($category)){
            $boxTitle = $category->title;
            $boxSubtitle = "آخرین نوشته های دسته $category->title";
        }elseif(request()->routeIs('posts.tags') and isset($tag)){
            $boxTitle = $tag->title;
            $boxSubtitle = "آخرین نوشته های برچسب $tag->title";
        }elseif(request()->routeIs('author') and isset($author)){
            $boxTitle = $author->display_name;
            $boxSubtitle = "آخرین نوشته های $author->display_name";
        }
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-8">
        <x-front.breadcrumbs />
        <section class="mb-6">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                <div
                    class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] dark:bg-brand-500/10"></div>
                <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>
                <div>
                    @if(get_setting('posts', 'first_box_label'))
                        <div
                            class="mb-5 inline-flex items-center gap-2 text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                            {{ get_setting('posts', 'first_box_label') }}
                        </div>
                    @endif

                    <h1 class="text-xl font-black leading-tight dark:text-slate-100">
                        {{ $boxTitle }}
                    </h1>

                    @if($boxSubtitle)
                        <h2 class="mt-2 text-xs leading-6 text-slate-600 dark:text-slate-400">
                            {{ $boxSubtitle }}
                        </h2>
                    @endif
                </div>
            </div>
        </section>

        <section data-smart-sidebar-layout class="grid grid-cols-1 gap-6 lg:grid-cols-10 lg:items-start">
            <div data-smart-sidebar-content class="lg:col-span-7 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($posts as $post)
                        @php $views = approximate_view(public_page_views($post)); @endphp
                        <article
                            class="rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/30">
                            <a class="flex flex-col h-full" href="{{ front_route('contents.detail', $post) }}">
                                <div class="relative overflow-hidden bg-slate-100 dark:bg-slate-800">
                                    <x-front.responsive-image
                                        :media="$post->picture"
                                        :fallback="$post->picture?->url"
                                        :alt="$post->title"
                                        :title="$post->title"
                                        variant="medium_large"
                                        class="h-40 w-full object-cover"
                                        wrapper-class="w-full transition-transform duration-300 group-hover:scale-[1.02]"
                                        :lazy="$loop->index >= 3"
                                        :async="$loop->index >= 3"
                                        :fetchpriority="$loop->index < 3 ? 'high' : null"
                                        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                                    />

                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>

                                    <div class="absolute top-3 right-3 inline-flex flex-wrap items-center gap-2">
                                        @foreach($post->categories as $category)
                                            <span
                                                class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">
                                                {{ $category->title }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="p-4 flex flex-col gap-2 grow">
                                    <h3 class="text-sm font-extrabold leading-6 group-hover:text-brand-700 transition dark:text-slate-100 dark:group-hover:text-brand-300">
                                        {{ $post->title }}
                                    </h3>
                                    <p class="text-xs text-slate-500 leading-6 line-clamp-2 dark:text-slate-400">
                                        {{ $post->excerpt }}
                                    </p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <div
                                            class="flex items-center gap-2 text-[0.7rem] text-slate-500 dark:text-slate-400">
                                            <span class="inline-flex items-center gap-2">
                                                {{ jalali_date($post->published_at, 'd F Y') }}
                                            </span>
                                            @if($views)
                                                <span class="h-1 w-1 rounded bg-slate-300 dark:bg-slate-600"></span>
                                                <span>{{ $views }} بازدید</span>
                                            @endif
                                        </div>
                                        <span
                                            class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 group-hover:text-brand-800 transition [&_svg]:w-3 [&_svg]:h-3 dark:text-brand-300 dark:group-hover:text-brand-200">
                                            ادامه
                                            @include('icons.arrow-left-double')
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @empty
                        <x-front.cart class="col-span-3 text-center">
                            <div
                                class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 [&_svg]:h-6 [&_svg]:w-6 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-500">
                                @include('icons.inbox')
                            </div>

                            <h3 class="mt-4 text-sm font-extrabold text-slate-800 dark:text-slate-200">نوشته ای یافت
                                نشد!</h3>
                        </x-front.cart>
                    @endforelse
                </div>

                @if($posts->hasPages())
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <x-front.list.pagination :model="$posts"/>
                    </div>
                @endif
            </div>

            <div data-smart-sidebar-column class="lg:col-span-3">
                <div data-smart-sidebar-box>
                    @include('front.sidebar.index')
                </div>
            </div>
        </section>
    </main>
@endsection
