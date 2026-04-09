@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        <section class="mb-6">
            <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden">
                <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>
                <div>
                    <div
                        class="inline-flex items-center gap-2 text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                        {{ get_setting('posts', 'first_box_label') }}
                    </div>
                    <h1 class="mt-3 text-2xl sm:text-3xl font-black leading-tight">
                        {{ get_setting('posts', 'first_box_title') }}
                    </h1>
                    <p class="mt-2 text-sm leading-7 text-slate-600">
                        {!! get_setting('posts', 'first_box_subtitle') !!}
                    </p>
                </div>
            </div>
        </section>

        <section>
            <div>
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($posts as $post)
                        <article
                            class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                            <a href="{{ route('contents.detail', $post) }}" class="block relative">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                                <img src="{{ $post->picture?->path }}" alt=""
                                     class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                                <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                    @foreach($post->categories as $category)
                                        <span
                                            class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">
                                            {{ $category->title }}
                                        </span>
                                    @endforeach
                                </div>
                            </a>
                            <div class="p-4">
                                <h3 class="text-sm font-extrabold leading-6">
                                    <a href="{{ route('contents.detail', $post) }}" class="hover:text-brand-700 transition">
                                        {{ $post->title }}
                                    </a>
                                </h3>
                                <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                    {{ $post->excerpt }}
                                </p>
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>{{ jalali_date($post->created_at, 'd F Y') }}</span>
                                        <span>•</span><span>{{ approximate_view(0) }} بازدید</span>
                                    </div>
                                    <a href="{{ route('contents.detail', $post) }}"
                                       class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                        ادامه <span class="font-black">←</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6 rounded-xl2 border border-slate-200 bg-white p-4 shadow-sm">
                    <x-front.list.pagination :model="$posts"/>
                </div>
            </div>
        </section>
    </main>
@endsection
