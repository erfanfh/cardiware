@extends('front.master')

@section('content')
    <main
        class="py-8"
        x-data="searchResultsPage()"
    >
        <div class="mx-auto max-w-7xl px-4">

            <!-- Header -->
            <section class="relative overflow-hidden rounded-xl2 border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                <div class="relative">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-2xl border border-brand-100 bg-brand-50 px-3 py-2 text-[11px] font-extrabold text-brand-800">
                            <span class="inline-block h-2 w-2 rounded-full bg-brand-500"></span>
                            صفحه نتایج جستجو
                        </span>

                            <div class="space-y-2">
                                <h1 class="text-2xl sm:text-3xl font-black leading-tight text-slate-900">
                                    نتایج جستجو برای
                                    <span class="text-brand-600">"فیفا"</span>
                                </h1>

                                <p class="text-sm leading-7 text-slate-600">
                                    تمام مطالب، اخبار، آموزش‌ها، مودها، فایل‌ها و صفحات مرتبط با عبارت جستجو شده در این بخش نمایش داده می‌شوند.
                                </p>
                            </div>
                        </div>

                        <div class="w-full max-w-xl">
                            <form class="relative">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0a7 7 0 0114 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>

                                <input
                                    type="text"
                                    value="فیفا"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 pr-10 pl-28 py-3 text-sm outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                    placeholder="جستجو در مطالب سایت..."
                                >

                                <button
                                    type="submit"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-brand-700 transition hover:bg-brand-50"
                                >
                                    جستجو
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700">
                        <span x-text="filteredCount"></span> نتیجه پیدا شد
                    </span>
                        <span class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700">
                        <span x-text="visibleGroupsCount"></span> دسته فعال
                    </span>
                        <span class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700">
                        فیلتر:
                        <span x-text="activeTabLabel"></span>
                    </span>
                    </div>
                </div>
            </section>

            <!-- Filter Tabs -->
            <section class="mt-6 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <div class="flex min-w-max items-center gap-2">
                    <template x-for="tab in tabs" :key="tab.key">
                        <button
                            type="button"
                            @click="activeTab = tab.key"
                            class="h-10 rounded-2xl px-4 text-sm font-extrabold transition border"
                            :class="activeTab === tab.key
                            ? 'border-brand-100 bg-brand-50 text-slate-900'
                            : 'border-slate-200 bg-white text-slate-800 hover:bg-slate-50'"
                            x-text="tab.label"
                        ></button>
                    </template>
                </div>
            </section>

            <!-- Results Full Width -->
            <section class="mt-6">
                <div class="space-y-8">

                    <template x-for="group in visibleGroups" :key="group.key">
                        <section class="space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700">
                                    <template x-if="group.key === 'news'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none">
                                            <path d="M7 8h10M7 12h7M5 4h14a1 1 0 011 1v14l-3-2-3 2-3-2-3 2-3-2V5a1 1 0 011-1z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </template>

                                    <template x-if="group.key === 'tutorials'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none">
                                            <path d="M4 6.5A2.5 2.5 0 016.5 4h11A2.5 2.5 0 0120 6.5v11a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 17.5v-11zm4 0h8M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </template>

                                    <template x-if="group.key === 'mods'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none">
                                            <path d="M8 7h8M8 12h8M8 17h5M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </template>

                                    <template x-if="group.key === 'downloads'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 3v11m0 0l4-4m-4 4l-4-4M5 17v1a2 2 0 002 2h10a2 2 0 002-2v-1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </template>

                                    <template x-if="group.key === 'pages'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none">
                                            <path d="M7 5h7l5 5v9a1 1 0 01-1 1H7a2 2 0 01-2-2V7a2 2 0 012-2zm7 0v5h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </template>
                                </span>

                                    <div>
                                        <h2 class="text-base font-extrabold" x-text="group.label"></h2>
                                        <p class="text-xs text-slate-500">
                                            <span x-text="group.items.length"></span> نتیجه در این دسته
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- News style -->
                            <template x-if="group.key === 'news'">
                                <div class="space-y-4">
                                    <template x-for="item in group.items" :key="item.id">
                                        <article class="group rounded-xl2 border border-slate-200 bg-white p-4 sm:p-5 shadow-sm transition hover:shadow-soft">
                                            <div class="flex flex-col gap-4 sm:flex-row">
                                                <a :href="item.url" class="block shrink-0 sm:w-64">
                                                    <div class="relative overflow-hidden rounded-2xl bg-slate-100">
                                                        <img
                                                            :src="item.image"
                                                            :alt="item.title"
                                                            class="h-44 w-full object-cover transition duration-300 group-hover:scale-[1.02] sm:h-40"
                                                        >
                                                        <span class="absolute right-3 top-3 rounded-xl bg-black/40 px-2 py-1 text-[11px] font-extrabold text-white ring-1 ring-white/15">
                                                        <span x-text="item.typeLabel"></span>
                                                    </span>
                                                    </div>
                                                </a>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-800" x-text="item.groupLabel"></span>
                                                        <template x-if="item.tag">
                                                            <span class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700" x-text="item.tag"></span>
                                                        </template>
                                                        <span class="text-xs text-slate-500" x-text="item.meta"></span>
                                                    </div>

                                                    <h3 class="mt-3 text-base font-extrabold leading-7 text-slate-900">
                                                        <a :href="item.url" class="transition hover:text-brand-700" x-text="item.title"></a>
                                                    </h3>

                                                    <p class="mt-2 text-sm leading-7 text-slate-600" x-text="item.excerpt"></p>

                                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                                        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                                                            <template x-if="item.author">
                                                                <span x-text="'نویسنده: ' + item.author"></span>
                                                            </template>
                                                            <template x-if="item.views">
                                                                <span x-text="'بازدید: ' + item.views"></span>
                                                            </template>
                                                        </div>

                                                        <a :href="item.url" class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 active:bg-brand-700">
                                                            مشاهده مطلب
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    </template>
                                </div>
                            </template>

                            <!-- Grid style -->
                            <template x-if="group.key !== 'news' && group.key !== 'downloads'">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                    <template x-for="item in group.items" :key="item.id">
                                        <article class="rounded-xl2 border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-soft">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-800" x-text="item.typeLabel"></span>
                                                <template x-if="item.meta">
                                                    <span class="text-xs text-slate-500" x-text="item.meta"></span>
                                                </template>
                                            </div>

                                            <h3 class="mt-3 text-sm sm:text-base font-extrabold leading-7">
                                                <a :href="item.url" class="transition hover:text-brand-700" x-text="item.title"></a>
                                            </h3>

                                            <p class="mt-2 text-sm leading-7 text-slate-600" x-text="item.excerpt"></p>

                                            <div class="mt-4">
                                                <a :href="item.url" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 transition hover:text-brand-800">
                                                    مشاهده
                                                    <span class="font-bold">←</span>
                                                </a>
                                            </div>
                                        </article>
                                    </template>
                                </div>
                            </template>

                            <!-- Downloads style -->
                            <template x-if="group.key === 'downloads'">
                                <div class="space-y-3">
                                    <template x-for="item in group.items" :key="item.id">
                                        <article class="rounded-xl2 border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-soft">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700" x-text="item.fileType"></span>
                                                        <span class="rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-800" x-text="item.typeLabel"></span>
                                                    </div>

                                                    <h3 class="mt-2 text-sm sm:text-base font-extrabold leading-7 text-slate-900" x-text="item.title"></h3>
                                                    <p class="mt-1 text-sm leading-7 text-slate-600" x-text="item.excerpt"></p>
                                                </div>

                                                <a :href="item.url" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 active:bg-brand-700">
                                                    دانلود فایل
                                                </a>
                                            </div>
                                        </article>
                                    </template>
                                </div>
                            </template>
                        </section>
                    </template>

                    <!-- Empty State -->
                    <section
                        x-show="filteredCount === 0"
                        x-transition
                        class="rounded-xl2 border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm"
                    >
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl border border-brand-100 bg-brand-50 text-brand-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                                <path d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0a7 7 0 0114 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <h2 class="mt-4 text-base font-extrabold text-slate-900">
                            نتیجه‌ای برای این فیلتر پیدا نشد
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-slate-600">
                            دسته دیگری را انتخاب کنید یا عبارت جستجو را تغییر دهید.
                        </p>
                    </section>

                    <!-- Pagination -->
                    <section class="rounded-xl2 border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-slate-500">
                                نمایش
                                <span class="font-extrabold text-slate-800">۱ تا ۱۲</span>
                                از
                                <span class="font-extrabold text-slate-800" x-text="filteredCount"></span>
                                نتیجه
                            </p>

                            <nav class="flex items-center gap-2">
                                <a href="#" class="inline-flex h-10 min-w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                                    قبلی
                                </a>
                                <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 text-sm font-extrabold text-slate-900">
                                    1
                                </a>
                                <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                                    2
                                </a>
                                <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                                    3
                                </a>
                                <span class="px-1 text-slate-400">...</span>
                                <a href="#" class="inline-flex h-10 min-w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                                    بعدی
                                </a>
                            </nav>
                        </div>
                    </section>

                </div>
            </section>
        </div>
    </main>
@endsection
