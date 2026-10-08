@extends('front.master')

@section('content')
    <main class="py-8" x-data="">
        <div class="mx-auto max-w-7xl px-4">
            <x-front.breadcrumbs />
            {{-- Header --}}
            <div
                class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 mb-6 dark:border-slate-800 dark:bg-slate-900">
                {{-- subtle background decoration --}}
                <div
                    class="pointer-events-none absolute -top-12 -left-12 h-32 w-32 rounded-[48px] bg-brand-100/60 blur-2xl dark:bg-brand-500/10"></div>
                <div class="pointer-events-none absolute top-4 left-6 h-2.5 w-2.5 rounded-full bg-brand-500"></div>

                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-black leading-tight text-slate-900 dark:text-slate-100">
                            @if($searchTerm !== '')
                                نتایج جستجو برای
                                <span class="text-brand-600 dark:text-brand-300">"{{ $searchTerm }}"</span>
                            @else
                                همه محتواهای سایت
                            @endif
                        </h1>

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            @if($searchTerm !== '')
                                نتایج مرتبط در عنوان، خلاصه و متن محتوا نمایش داده شده‌اند.
                            @else
                                جدیدترین نوشته‌ها، محصولات، پروژه‌ها و ویدیوها را از تب‌های زیر مشاهده کنید.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="relative overflow-hidden w-full flex flex-wrap items-center gap-2 bg-white rounded-2xl border border-slate-200 p-2 dark:border-slate-800 dark:bg-slate-900">
                {{-- subtle bg --}}
                <div
                    class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/50 blur-xl dark:bg-brand-500/10"></div>

                {{-- Posts --}}
                <a href="{{ front_route('search', ['q' => $searchTerm, 'type' => 'posts']) }}"
                   class="group relative flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition
                    {{ $type == 'posts' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-slate-100 dark:hover:bg-slate-800' }}">

                    @if($type == 'posts')
                        <span class="absolute inset-0 rounded-xl ring-1 ring-black/5"></span>
                    @endif

                    <span class="opacity-80 group-hover:opacity-100 transition">
                        {{ $postsCount }}
                    </span>

                    <span>نوشته‌ها</span>

                    <span class="opacity-70 group-hover:opacity-100 transition">
                        @include('icons.file-02')
                    </span>
                </a>

                {{-- Products --}}
                <a href="{{ front_route('search', ['q' => $searchTerm, 'type' => 'products']) }}"
                   class="group relative flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition
                    {{ $type == 'products' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-slate-100 dark:hover:bg-slate-800' }}">

                    @if($type == 'products')
                        <span class="absolute inset-0 rounded-xl ring-1 ring-black/5"></span>
                    @endif

                    <span class="opacity-80 group-hover:opacity-100 transition">
                        {{ $productsCount }}
                    </span>

                    <span>محصولات</span>

                    <span class="opacity-70 group-hover:opacity-100 transition [&_svg]:w-4 [&_svg]:h-4">
                        @include('icons.shopping-bag-03')
                    </span>
                </a>

                {{-- Projects --}}
                <a href="{{ front_route('search', ['q' => $searchTerm, 'type' => 'projects']) }}"
                   class="group relative flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition
                    {{ $type == 'projects' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-slate-100 dark:hover:bg-slate-800' }}">

                    @if($type == 'projects')
                        <span class="absolute inset-0 rounded-xl ring-1 ring-black/5"></span>
                    @endif

                    <span class="opacity-80 group-hover:opacity-100 transition">
                        {{ $projectsCount }}
                    </span>

                    <span>پروژه‌ها</span>

                    <span class="opacity-70 group-hover:opacity-100 transition">
                        @include('icons.folder-management')
                    </span>
                </a>

                {{-- Videos --}}
                <a href="{{ front_route('search', ['q' => $searchTerm, 'type' => 'videos']) }}"
                   class="group relative flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition
                    {{ $type == 'videos' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-slate-100 dark:hover:bg-slate-800' }}">

                    @if($type == 'videos')
                        <span class="absolute inset-0 rounded-xl ring-1 ring-black/5"></span>
                    @endif

                    <span class="opacity-80 group-hover:opacity-100 transition">
                        {{ $videosCount }}
                    </span>

                    <span>ویدیوها</span>

                    <span class="opacity-70 group-hover:opacity-100 transition [&_svg]:h-4 [&_svg]:w-4">
                        @include('icons.video-01')
                    </span>
                </a>
            </div>

            @if($type == 'posts')
                <section>
                    <div>
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($posts as $post)
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
                                                wrapper-class="w-full transition-transform duration-500 group-hover:scale-[1.02]"
                                                :lazy="$loop->index >= 4"
                                                :async="$loop->index >= 4"
                                                :fetchpriority="$loop->index < 4 ? 'high' : null"
                                                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                                            />

                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>

                                            <div
                                                class="absolute top-3 right-3 inline-flex flex-wrap items-center gap-2">
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
                                                        {{ jalali_date($post->created_at, 'd F Y') }}
                                                    </span>
                                                    @if($views)
                                                        <span
                                                            class="h-1 w-1 rounded bg-slate-300 dark:bg-slate-600"></span>
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
                            @endforeach
                        </div>

                        @if($posts->isEmpty())
                            <x-front.cart class="text-center">
                                <div
                                    class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 [&_svg]:h-6 [&_svg]:w-6 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-500">
                                    @include('icons.inbox')
                                </div>

                                <h3 class="mt-4 text-sm font-extrabold text-slate-800 dark:text-slate-200">نوشته ای یافت
                                    نشد!</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                    فیلترها را تغییر بده یا عبارت دیگری جستجو کن.
                                </p>
                            </x-front.cart>
                        @endif

                        @if($posts->hasPages())
                            <div
                                class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                                <x-front.list.pagination :model="$posts"/>
                            </div>
                        @endif
                    </div>
                </section>
            @elseif($type == 'products')
                <section class="mt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($products as $product)
                            <x-front.product.card :product="$product"/>
                        @endforeach
                    </div>

                    @if($products->isEmpty())
                        <div
                            class="col-span-3 rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 p-6 text-center sm:p-8 dark:border-slate-700 dark:bg-slate-900">
                            <div
                                class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 [&_svg]:h-6 [&_svg]:w-6 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-500">
                                @include('icons.inbox')
                            </div>

                            <h3 class="mt-4 text-sm font-extrabold text-slate-800 dark:text-slate-200">
                                محصولی یافت نشد!
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                فیلترها را تغییر بده یا عبارت دیگری جستجو کن.
                            </p>
                        </div>
                    @endif

                    @if($products->hasPages())
                        <div
                            class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                            <x-front.list.pagination :model="$products"/>
                        </div>
                    @endif
                </section>
            @elseif($type == 'projects')
                <section class="mt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($projects as $project)
                            <x-front.project.card :project="$project"/>
                        @endforeach
                    </div>

                    @if($projects->isEmpty())
                        <div
                            class="col-span-3 rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 p-6 text-center sm:p-8 dark:border-slate-700 dark:bg-slate-900">
                            <div
                                class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 [&_svg]:h-6 [&_svg]:w-6 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-500">
                                @include('icons.inbox')
                            </div>

                            <h3 class="mt-4 text-sm font-extrabold text-slate-800 dark:text-slate-200">
                                پروژه ای یافت نشد!
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                فیلترها را تغییر بده یا عبارت دیگری جستجو کن.
                            </p>
                        </div>
                    @endif

                    @if($projects->hasPages())
                        <div
                            class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                            <x-front.list.pagination :model="$projects"/>
                        </div>
                    @endif
                </section>
            @elseif($type == 'videos')
                <section class="mt-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        @foreach($videos as $video)
                            @include('front.videos.partials.card', ['video' => $video])
                        @endforeach
                    </div>

                    @if($videos->isEmpty())
                        <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 p-6 text-center sm:p-8 dark:border-slate-700 dark:bg-slate-900">
                            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-500 [&_svg]:h-6 [&_svg]:w-6">
                                @include('icons.video-01')
                            </div>

                            <h3 class="mt-4 text-sm font-extrabold text-slate-800 dark:text-slate-200">
                                ویدیویی یافت نشد!
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                عبارت دیگری جستجو کنید یا نتایج سایر بخش‌ها را ببینید.
                            </p>
                        </div>
                    @endif

                    @if($videos->hasPages())
                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                            <x-front.list.pagination :model="$videos"/>
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </main>
@endsection
