@extends('admin.layout')

@section('title', 'نوشته های حذف شده')

@section('content')
    @php
        function sortLink($column): string
        {
            $currentSort = request('sort');
            $currentDirection = request('direction', 'asc');

            $direction = ($currentSort === $column && $currentDirection === 'asc')
                ? 'desc'
                : 'asc';

            return route(
                request()->route()->getName(),
                array_merge(request()->query(), [
                    'sort' => $column,
                    'direction' => $direction,
                ])
            );
        }

        function sortIcon($column): string
        {
            if (request('sort') !== $column) return '';

            return request('direction') === 'asc'
                ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><!-- Icon from Huge Icons by Hugeicons - undefined --><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v10m1.603-14.586l.76.967c1.234 1.565 1.851 2.348 1.57 2.984c-.281.635-1.245.635-3.172.635H11.24c-1.927 0-2.89 0-3.172-.635c-.281-.636.336-1.419 1.57-2.984l.76-.967C11.14 4.471 11.511 4 12 4s.86.471 1.603 1.414"/></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><!-- Icon from Huge Icons by Hugeicons - undefined --><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m10.397 18.586l-.76-.967c-1.234-1.565-1.851-2.348-1.57-2.984C8.348 14 9.312 14 11.24 14h1.522c1.927 0 2.89 0 3.172.635c.281.636-.336 1.419-1.57 2.984l-.76.967C12.86 19.529 12.488 20 12 20s-.86-.471-1.603-1.414M12 14V4"/></svg>';
        }
    @endphp

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                مدیریت و جستجوی نوشته های حذف شده
            </p>
        </div>

        <div class="flex w-full md:w-auto gap-2">
            <button type="button"
                    class="w-full md:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-full shadow-sm border border-gray-300 dark:border-gray-700 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 cursor-pointer"
                    onclick="toggleFilters()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h3M3 17h6m9 0h3M15 7h6"/>
                        <path
                            d="M6 7c0-.932 0-1.398.152-1.765a2 2 0 0 1 1.083-1.083C7.602 4 8.068 4 9 4s1.398 0 1.765.152a2 2 0 0 1 1.083 1.083C12 5.602 12 6.068 12 7s0 1.398-.152 1.765a2 2 0 0 1-1.083 1.083C10.398 10 9.932 10 9 10s-1.398 0-1.765-.152a2 2 0 0 1-1.083-1.083C6 8.398 6 7.932 6 7Zm6 10c0-.932 0-1.398.152-1.765a2 2 0 0 1 1.083-1.083C13.602 14 14.068 14 15 14s1.398 0 1.765.152a2 2 0 0 1 1.083 1.083C18 15.602 18 16.068 18 17s0 1.398-.152 1.765a2 2 0 0 1-1.083 1.083C16.398 20 15.932 20 15 20s-1.398 0-1.765-.152a2 2 0 0 1-1.083-1.083C12 18.398 12 17.932 12 17Z"/>
                    </g>
                </svg>
                فیلترها
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.posts.history') }}"
          class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg p-4 md:p-5 shadow-sm border border-gray-200 dark:border-gray-700 mb-5 {{ request()->hasAny('title', 'id', 'author', 'category', 'tag', 'comments', 'views', 'from_published_at', 'to_published_at', 'status') ? 'block' : 'hidden' }}"
          id="filtersBox">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-4">
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif

            @if(request('direction'))
                <input type="hidden" name="direction" value="{{ request('direction') }}">
            @endif

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400" for="id">شناسه</label>
                <input type="text" name="id"
                       class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                       value="{{ request('id') }}">
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400" for="title">عنوان</label>
                <input type="text" name="title"
                       class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                       value="{{ request('title') }}">
            </div>

            <div class="flex flex-col gap-1">
                <label for="author" class="text-xs font-medium text-gray-500 dark:text-gray-400">نویسنده</label>
                <select name="author"
                        placeholder="جستجو کنید..."
                        id="author-select"
                        autocomplete="off"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                    @if(request('author'))
                        <option selected
                                value="{{ request('author') }}">{{ \App\Domain\Identity\Models\User::findOrFail(request('author'))->full_name }}</option>
                    @endif
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label for="category" class="text-xs font-medium text-gray-500 dark:text-gray-400">دسته بندی</label>
                <select name="category"
                        placeholder="جستجو کنید..."
                        id="category-select"
                        autocomplete="off"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                    @if(request('category'))
                        <option selected value="{{ request('category') }}">{{ \App\Domain\Taxonomy\Models\TaxonomyTerm::findOrFail(request('category'))->title }}</option>
                    @endif
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label for="tag" class="text-xs font-medium text-gray-500 dark:text-gray-400">دسته بندی</label>
                <select name="tag"
                        placeholder="جستجو کنید..."
                        id="tag-select"
                        autocomplete="off"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                    @if(request('tag'))
                        <option selected value="{{ request('category') }}">{{ \App\Domain\Taxonomy\Models\TaxonomyTerm::findOrFail(request('tag'))->title }}</option>
                    @endif
                </select>
            </div>

            <div class="flex flex-row gap-1">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">تعداد کامنت از</label>
                    <input type="text" name="comments_from"
                           class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                           value="{{ request('comments_from') }}">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">تعداد کامنت تا</label>
                    <input type="text" name="comments_to"
                           class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                           value="{{ request('comments_to') }}">
                </div>
            </div>

            <div class="flex flex-row gap-1">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">تعداد بازدید از</label>
                    <input type="text" name="views_to"
                           class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                           value="{{ request('views_to') }}">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">تعداد بازدید تا</label>
                    <input type="text" name="views_to"
                           class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                           value="{{ request('views_to') }}">
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">انتشار از</label>
                <input
                    data-jdp
                    autocomplete="off"
                    name="from_published_at"
                    value="{{ request('from_published_at') ? request('from_published_at') : '' }}"
                    class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                >
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">انتشار تا</label>
                <input
                    data-jdp
                    autocomplete="off"
                    name="to_published_at"
                    value="{{ request('to_published_at') ? request('to_published_at') : '' }}"
                    class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                >
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400" for="is_active">وضعیت</label>
                <select name="status"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach(\App\Domain\Content\Enums\StatusEnum::cases() as $status)
                        <option
                            @selected(request('status') === $status->value) value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pt-3 border-t border-dashed border-gray-200 dark:border-gray-600">
            <div class="order-2 md:order-1"></div>

            <div class="order-1 md:order-2 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('admin.posts.history') }}"
                   class="px-4 py-2 rounded-full border border-gray-200 dark:border-gray-600 text-sm font-medium text-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    حذف فیلترها
                </a>

                <button type="submit"
                        class="bg-[var(--brand-500)] text-white text-[0.86rem] inline-flex items-center justify-center rounded-full py-[7px] px-[14px] cursor-pointer hover:bg-[var(--brand-600)] hover:scale-105 transition-all duration-200">
                    جستجو
                </button>
            </div>
        </div>
    </form>

    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto -mx-0.5">
            <table class="w-full text-sm border-collapse table-auto">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">تصویر
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">
                        <a href="{{ sortLink('title') }}" class="flex items-center gap-1">
                            نوشته {!! sortIcon('title') !!}
                            @if(request('sort') !== 'title')
                                <div class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="1.5"
                                              d="m10.397 18.586l-.76-.967c-1.234-1.565-1.851-2.348-1.57-2.984C8.348 14 9.312 14 11.24 14h1.522c1.927 0 2.89 0 3.172.635c.281.636-.336 1.419-1.57 2.984l-.76.967C12.86 19.529 12.488 20 12 20s-.86-.471-1.603-1.414M12 14V4"/>
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="1.5"
                                              d="M12 10v10m1.603-14.586l.76.967c1.234 1.565 1.851 2.348 1.57 2.984c-.281.635-1.245.635-3.172.635H11.24c-1.927 0-2.89 0-3.172-.635c-.281-.636.336-1.419 1.57-2.984l.76-.967C11.14 4.471 11.511 4 12 4s.86.471 1.603 1.414"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">شناسه
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">
                        نویسنده
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">دسته
                        ها
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">برچسب
                        ها
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">وضعیت
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">
                        <a href="{{ sortLink('published_at') }}" class="flex items-center gap-1">
                            تاریخ انتشار {!! sortIcon('published_at') !!}
                            @if(request('sort') !== 'published_at')
                                <div class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="1.5"
                                              d="m10.397 18.586l-.76-.967c-1.234-1.565-1.851-2.348-1.57-2.984C8.348 14 9.312 14 11.24 14h1.522c1.927 0 2.89 0 3.172.635c.281.636-.336 1.419-1.57 2.984l-.76.967C12.86 19.529 12.488 20 12 20s-.86-.471-1.603-1.414M12 14V4"/>
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="1.5"
                                              d="M12 10v10m1.603-14.586l.76.967c1.234 1.565 1.851 2.348 1.57 2.984c-.281.635-1.245.635-3.172.635H11.24c-1.927 0-2.89 0-3.172-.635c-.281-.636.336-1.419 1.57-2.984l.76-.967C11.14 4.471 11.511 4 12 4s.86.471 1.603 1.414"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">تعداد
                        بازدید
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">تعداد
                        کامنت ها
                    </th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">عملیات</th>
                </tr>
                </thead>

                <tbody>
                @forelse($posts as $post)
                    <tr wire:key="{{ $post->id }}"
                        class="even:bg-gray-50/50 dark:even:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                        <td></td>

                        <td class="p-3 break-words whitespace-normal">
                            <a href="#" target="_blank"
                               class="flex items-start gap-3 group">
                                <div>
                                    <div
                                        class="font-semibold text-gray-800 dark:text-gray-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                        {{ $post->title }}
                                    </div>
                                </div>
                            </a>
                        </td>

                        <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal">{{ $post->id }}</td>

                        <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal">{{ $post->author->first_name . ' ' . $post->author->last_name }}</td>

                        <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal max-w-xs">{{ $post->categories->pluck('title')->implode(', ') }}</td>

                        <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal max-w-xs">{{ $post->tags->pluck('title')->implode(', ') }}</td>

                        <td class="p-3 break-words whitespace-normal">
                            @if($post->status == \App\Domain\Content\Enums\StatusEnum::DRAFT)
                                <span
                                    class=" whitespace-nowrap inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::DRAFT->label() }}
                                </span>
                            @elseif($post->status == \App\Domain\Content\Enums\StatusEnum::PENDING_REVIEW)
                                <span
                                    class=" whitespace-nowrap inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::PENDING_REVIEW->label() }}
                                </span>
                            @elseif($post->status == \App\Domain\Content\Enums\StatusEnum::PUBLISHED)
                                <span
                                    class=" whitespace-nowrap inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::PUBLISHED->label() }}
                                </span>
                            @endif
                        </td>

                        <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal">
                            {{ jalali_date($post->published_at, 'Y/m/d H:i') }}
                        </td>

                        <td></td>

                        <td></td>

                        <td class="p-3 break-words whitespace-normal">
                            <div class="flex gap-1.5 justify-start">
                                <a href="{{ route('admin.posts.restore', $post->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                                            <path
                                                d="m19.5 5.5l-.5 6m-14.5-6l.605 10.037c.154 2.57.232 3.855.874 4.78a4 4 0 0 0 1.2 1.132c.582.356 1.284.496 2.321.551"/>
                                            <path stroke-linejoin="round"
                                                  d="m11 15.5l1.136 1.466a4 4 0 0 1 7.364-.901M21 20.5l-1.136-1.464a4 4 0 0 1-7.328.965"/>
                                            <path
                                                d="M3 5.5h18m-4.944 0l-.683-1.408c-.453-.936-.68-1.403-1.071-1.695a2 2 0 0 0-.275-.172C13.594 2 13.074 2 12.035 2c-1.066 0-1.599 0-2.04.234a2 2 0 0 0-.278.18c-.395.303-.616.788-1.058 1.757L8.053 5.5"/>
                                        </g>
                                    </svg>
                                    بازیابی
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.posts.force-delete', $post->id) }}"
                                      class="inline-block"
                                      data-confirm-title="حذف نوشته"
                                      data-confirm-text="نوشته برای همیشه حذف خواهد شد."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-medium hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 24 24">
                                            <path fill="none" stroke="currentColor" stroke-linecap="round"
                                                  stroke-width="1.5"
                                                  d="m19.5 5.5l-.62 10.025c-.158 2.561-.237 3.842-.88 4.763a4 4 0 0 1-1.2 1.128c-.957.584-2.24.584-4.806.584c-2.57 0-3.855 0-4.814-.585a4 4 0 0 1-1.2-1.13c-.642-.922-.72-2.205-.874-4.77L4.5 5.5M3 5.5h18m-4.944 0l-.683-1.408c-.453-.936-.68-1.403-1.071-1.695a2 2 0 0 0-.275-.172C13.594 2 13.074 2 12.035 2c-1.066 0-1.599 0-2.04.234a2 2 0 0 0-.278.18c-.395.303-.616.788-1.058 1.757L8.053 5.5m1.447 11v-6m5 6v-6"/>
                                        </svg>
                                        حذف دائمی
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center">
                            <h3 class="text-gray-500 dark:text-gray-400 font-medium">هیچ نوشته ای یافت نشد!</h3>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 dark:border-gray-700">
            {{ $posts->links() }}
        </div>
    </div>

    <a href="{{ route('admin.posts.index') }}"
       class="text-xs text-gray-400 my-3 inline-flex items-center gap-2 underline">
        <span>
            بازگشت
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                <path d="M11 6h4.5a4.5 4.5 0 1 1 0 9H4"/>
                <path d="M7 12s-3 2.21-3 3s3 3 3 3"/>
            </g>
        </svg>
    </a>
@endsection

@push('scripts')
    <script>
        function toggleFilters() {
            const filters = document.getElementById('filtersBox');
            filters.classList.toggle('hidden');
            filters.classList.toggle('block');
        }
    </script>
@endpush
