@extends('front.master')

@section('content')
    @php
        $boxTitle = get_setting('posts', 'first_box_title') ?: 'آخرین نوشته ها';
        $boxSubtitle = get_setting('posts', 'first_box_subtitle');
        if(request()->routeIs('categories') and isset($category)){
            $boxTitle = $category->title;
            $boxSubtitle = "آخرین نوشته های دسته $category->title";
        }elseif(request()->routeIs('tags') and isset($tag)){
            $boxTitle = $tag->title;
            $boxSubtitle = "آخرین نوشته های برچسب $tag->title";
        }
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-8">
        <section class="mb-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden">
                <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>
                <div>
                    @if(get_setting('posts', 'first_box_label'))
                        <div
                            class="mb-5 inline-flex items-center gap-2 text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                            {{ get_setting('posts', 'first_box_label') }}
                        </div>
                    @endif

                    <h1 class="text-xl font-black leading-tight">
                        {{ $boxTitle }}
                    </h1>

                    @if($boxSubtitle)
                        <p class="mt-2 text-xs leading-6 text-slate-600">
                            {{ $boxSubtitle }}
                        </p>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div class="lg:col-span-7 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($posts as $post)
                        @php $views = approximate_view(page_views($post)); @endphp
                        <article
                            class="rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                            <a class="flex flex-col h-full" href="{{ route('contents.detail', $post) }}">
                                <div class="relative">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                                    <img src="{{ $post->picture?->url }}" alt="{{ $post->title }}"
                                         title="{{ $post->title }}"
                                         class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
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
                                    <h3 class="text-sm font-extrabold leading-6 group-hover:text-brand-700 transition">
                                        {{ $post->title }}
                                    </h3>
                                    <p class="text-xs text-slate-500 leading-6 line-clamp-2">
                                        {{ $post->excerpt }}
                                    </p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <div class="flex items-center gap-2 text-[0.7rem] text-slate-500">
                                            <span class="inline-flex items-center gap-2">
                                                {{ jalali_date($post->created_at, 'd F Y') }}
                                            </span>
                                            @if($views)
                                                <span class="h-1 w-1 rounded bg-slate-300"></span>
                                                <span>{{ $views }} بازدید</span>
                                            @endif
                                        </div>
                                        <span
                                            class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 group-hover:text-brand-800 transition [&_svg]:w-3 [&_svg]:h-3">
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
                                class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 [&_svg]:h-6 [&_svg]:w-6">
                                @include('icons.inbox')
                            </div>

                            <h3 class="mt-4 text-sm font-extrabold text-slate-800">نوشته ای یافت نشد!</h3>
                        </x-front.cart>
                    @endforelse
                </div>

                @if($posts->hasMorePages())
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <x-front.list.pagination :model="$posts"/>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3">
                @include('front.sidebar.index')
            </div>
        </section>
    </main>
@endsection
