@extends('front.master')

@section('content')
    <main class="py-8">
        <div class="mx-auto max-w-7xl px-4">
            <div class="relative pt-4 pb-10 px-4">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="space-y-3">
                        <div class="space-y-2">
                            <h1 class="text-2xl sm:text-3xl font-black leading-tight text-slate-900">
                                نتایج جستجو برای
                                <span class="text-brand-600">"{{ request()->q }}"</span>
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden w-full flex items-center gap-4 bg-white rounded-xl p-3 shadow-sm">
                <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                <a href="{{ route('search', ['q' => request()->q, 'type' => 'posts']) }}" class="flex items-center gap-2 @if($type == 'posts') bg-brand-500 text-white @else text-gray-600 hover:text-gray-800 @endif px-4 py-2 rounded-lg">
                    <span>{{ $postsCount }}</span>
                    <span>نوشته‌ها</span>
                    @include('icons.file-02')
                </a>

                <a href="{{ route('search', ['q' => request()->q, 'type' => 'projects']) }}" class="flex items-center gap-2 @if($type == 'projects') bg-brand-500 text-white @else text-gray-600 hover:text-gray-800 @endif px-4 py-2 rounded-lg">
                    <span>{{ $projectsCount }}</span>
                    <span>پروژه‌ها</span>
                    @include('icons.folder-management')
                </a>
            </div>

            @if($type == 'posts')
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

                        @if($posts->isEmpty())
                            <div class="mt-6 rounded-xl2 border border-slate-200 bg-white p-6 text-center shadow-sm">
                                <div class="text-base font-extrabold">نوشته ای پیدا نشد</div>
                                <div class="mt-2 text-sm text-slate-500">فیلترها را تغییر بده یا عبارت دیگری جستجو کن.</div>
                            </div>
                        @endif

                        <div class="mt-6 rounded-xl2 border border-slate-200 bg-white p-4 shadow-sm">
                            <x-front.list.pagination :model="$posts"/>
                        </div>
                    </div>
                </section>
            @elseif($type == 'projects')
                <section class="mt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($projects as $project)
                            <article
                                class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm hover:shadow-lg transition">

                                <div
                                    class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                                <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                                <div class="flex items-center gap-4">
                                    <div class="shrink-0">
                                        <div class="h-12 w-12 rounded-2xl border border-slate-200 bg-white grid place-items-center">
                                            <span class="text-xs font-extrabold text-slate-400">SVG</span>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm sm:text-base font-extrabold truncate">{{ $project->title }}</div>
                                                <div class="text-xs text-slate-500 mt-1">{{ $project->excerpt }}</div>
                                            </div>

                                            <span
                                                class="shrink-0 inline-flex items-center gap-2 rounded-2xl bg-brand-50 border border-brand-100 px-3 py-1.5 text-[11px] font-extrabold text-brand-800">
                                    <span
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-white text-[11px] font-black">%</span>
                                    {{ $project->progress_percent }}٪ تکمیل
                                  </span>
                                        </div>

                                        <div class="mt-3">
                                            <div class="flex items-center justify-between text-xs text-slate-500">
                                                <span>پیشرفت</span>
                                                <span class="font-extrabold text-slate-700">{{ $project->progress_percent }}%</span>
                                            </div>
                                            <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                                <div
                                                    class="h-full rounded-full transition-all duration-300"
                                                    :class="{
                                            'bg-emerald-500': ({{ $project->progress_percent }} ?? 0) >= 100,
                                            'bg-sky-500': {{ $project->progress_percent }} >= 70 && ({{ $project->progress_percent }} ?? 0) < 100,
                                            'bg-yellow-500': ({{ $project->progress_percent }} ?? 0) >= 40 && ({{ $project->progress_percent }} ?? 0) < 70,
                                            'bg-red-500': ({{ $project->progress_percent }} ?? 0) >= 1 && ({{ $project->progress_percent }} ?? 0) < 40,
                                            'bg-slate-300': ({{ $project->progress_percent }} ?? 0) < 1
                                        }"
                                                    style="width: {{ $project->progress_percent . '%' }}"
                                                ></div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex items-center justify-between gap-3">
                                            <a href="{{ route('projects.detail', $project) }}"
                                               class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                                                اطلاعات پروژه
                                                <span class="font-black" dir="ltr">←</span>
                                            </a>

                                            @if($project->last_updated_at)
                                                <span class="text-xs text-slate-500 hidden sm:inline">
                                        بروزرسانی: {{ jalali_date($project->last_updated_at) }}
                                    </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="hidden sm:block shrink-0">
                                        <div
                                            class="h-14 w-14 rounded-3xl border border-slate-200 bg-slate-50 grid place-items-center">
                                            <span class="text-xs font-extrabold text-slate-400">SVG</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if($projects->isEmpty())
                        <div class="mt-6 rounded-xl2 border border-slate-200 bg-white p-6 text-center shadow-sm">
                            <div class="text-base font-extrabold">پروژه‌ای پیدا نشد</div>
                            <div class="mt-2 text-sm text-slate-500">فیلترها را تغییر بده یا عبارت دیگری جستجو کن.</div>
                        </div>
                    @endif

                    <div class="mt-6 rounded-xl2 border border-slate-200 bg-white p-4 shadow-sm">
                        <x-front.list.pagination :model="$projects"/>
                    </div>
                </section>
            @endif
        </div>
    </main>
@endsection
