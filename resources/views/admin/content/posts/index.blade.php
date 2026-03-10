@extends('admin.layout')

@section('title', 'نوشته ها')

@section('content')
    <livewire:admin.content.post.quick-edit-form/>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                مدیریت و جستجوی نوشته ها
            </p>
        </div>

        <div class="flex w-full md:w-auto gap-2">
            <button type="button"
                    class="w-1/2 md:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-full shadow-sm border border-gray-300 dark:border-gray-700 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 cursor-pointer"
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

            <a href="{{ route('admin.posts.create') }}"
               class="w-1/2 md:w-auto bg-[var(--brand-500)] text-white text-[0.86rem] inline-flex items-center justify-center rounded-full py-[7px] px-[14px] cursor-pointer hover:bg-[var(--brand-600)] hover:scale-105 transition-all duration-200">
                <span class="text-lg">+</span>
                <span>ایجاد نوشته جدید</span>
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.posts.index') }}"
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
                        <option selected
                                value="{{ request('category') }}">{{ \App\Domain\Taxonomy\Models\TaxonomyTerm::findOrFail(request('category'))->title }}</option>
                    @endif
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label for="tag" class="text-xs font-medium text-gray-500 dark:text-gray-400">برچسب</label>
                <select name="tag"
                        placeholder="جستجو کنید..."
                        id="tag-select"
                        autocomplete="off"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                    @if(request('tag'))
                        <option selected
                                value="{{ request('category') }}">{{ \App\Domain\Taxonomy\Models\TaxonomyTerm::findOrFail(request('tag'))->title }}</option>
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
                <a href="{{ route('admin.posts.index') }}"
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

    <livewire:admin.content.post.posts-list/>

    <a href="{{ route('admin.posts.history') }}"
       class="text-xs text-gray-400 my-3 inline-flex items-center gap-2 underline">
        <span>
            مشاهده مواد حذف شده
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5"
                  d="m19.5 5.5l-.62 10.025c-.158 2.561-.237 3.842-.88 4.763a4 4 0 0 1-1.2 1.128c-.957.584-2.24.584-4.806.584c-2.57 0-3.855 0-4.814-.585a4 4 0 0 1-1.2-1.13c-.642-.922-.72-2.205-.874-4.77L4.5 5.5M3 5.5h18m-4.944 0l-.683-1.408c-.453-.936-.68-1.403-1.071-1.695a2 2 0 0 0-.275-.172C13.594 2 13.074 2 12.035 2c-1.066 0-1.599 0-2.04.234a2 2 0 0 0-.278.18c-.395.303-.616.788-1.058 1.757L8.053 5.5m1.447 11v-6m5 6v-6"/>
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
