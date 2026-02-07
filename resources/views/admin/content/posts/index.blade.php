@extends('admin.layout')

@section('title', 'مقالات')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                مدیریت و جستجوی مقالات
            </p>
        </div>

        <div class="flex gap-2">
            <button type="button"
                    class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow border border-gray-200 dark:border-gray-700 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
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
               class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-500 text-white rounded-full shadow-lg hover:shadow-xl text-sm font-medium transition-all hover:-translate-y-0.5">
                <span class="text-lg">+</span>
                <span>ایجاد مقاله جدید</span>
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.posts.index') }}"
          class="bg-white dark:bg-gray-800 rounded-lg p-4 md:p-5 shadow border border-gray-100 dark:border-gray-700 mb-5 {{ request()->getQueryString() ? 'block' : 'hidden' }}"
          id="filtersBox">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400" for="id">شناسه</label>
                <input type="text" name="id"
                       class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 dark:focus:ring-orange-500 outline-none transition-colors"
                       placeholder="مثلاً ۱۲۳"
                       value="{{ request('id') }}">
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400" for="title">عنوان</label>
                <input type="text" name="title"
                       class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 dark:focus:ring-orange-500 outline-none transition-colors"
                       placeholder="مثلاً چگونه آشپر خوبی باشیم؟"
                       value="{{ request('title') }}">
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400" for="author">نویسنده</label>
                <input type="text" name="author"
                       class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 dark:focus:ring-orange-500 outline-none transition-colors"
                       placeholder="مثلاً راضیه ملکی"
                       value="{{ request('author') }}">
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400" for="is_active">وضعیت</label>
                <select name="is_active"
                        class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-500 dark:focus:ring-orange-500 outline-none transition-colors appearance-none">
                    <option value="">همه وضعیت‌ها</option>
                    <option @selected(request('is_active') === 'true') value="true">منتشر شده</option>
                    <option @selected(request('is_active') === 'false') value="false">منتشر نشده</option>
                </select>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pt-3 border-t border-dashed border-gray-200 dark:border-gray-600">
            <div class="order-2 md:order-1"></div>

            <div class="order-1 md:order-2 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('admin.posts.index') }}"
                   class="px-4 py-2 rounded-full border border-gray-200 dark:border-gray-600 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                    حذف فیلترها
                </a>

                <button type="submit"
                        class="px-4 py-2 rounded-full bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 text-orange-600 dark:text-orange-400 text-sm font-medium hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors text-center">
                    جستجو
                </button>
            </div>
        </div>
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto -mx-0.5">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[200px]">مقاله</th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[50px]">شناسه</th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[110px]">نویسنده</th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[100px]">وضعیت</th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[135px]">تاریخ انتشار</th>
                    <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">عملیات</th>
                </tr>
                </thead>

                <tbody>
                @forelse($posts as $post)
                    <tr class="even:bg-gray-50/50 dark:even:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                        <td class="p-3">
                            <a href="{{ route('posts.show', $post) }}" target="_blank"
                               class="flex items-center gap-3 group">
                                <div class="w-8 h-8 min-w-8 rounded-lg bg-gradient-to-r from-orange-500 to-orange-500 text-white flex items-center justify-center text-sm font-semibold overflow-hidden">
                                    @if($post->picture?->url)
                                        <img src="{{ $post->picture->url }}" alt="{{ $post->title }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        {{ mb_substr($post->title ?? 'مقاله', 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 dark:text-gray-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                        {{ $post->title }}
                                    </div>
                                </div>
                            </a>
                        </td>

                        <td class="p-3 text-gray-600 dark:text-gray-300">{{ $post->id }}</td>

                        <td class="p-3 text-gray-600 dark:text-gray-300">{{ $post->author->first_name . ' ' . $post->author->last_name }}</td>

                        <td class="p-3">
                            @if($post->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    منتشر شده
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    منتشر نشده
                                </span>
                            @endif
                        </td>

                        <td class="p-3 text-gray-600 dark:text-gray-300">
                            {{ jalali_date($post->published_at, 'Y/m/d H:i') }}
                        </td>

                        <td class="p-3">
                            <div class="flex gap-1.5 justify-start">
                                <a href="{{ route('admin.posts.edit', $post) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    <svg width="14" height="14" viewBox="0 0 24 24">
                                        <path d="M5 7h14M5 12h14M5 17h14"
                                              stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    </svg>
                                    ویرایش
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.posts.destroy', $post) }}"
                                      class="js-confirm inline-block"
                                      data-confirm-title="حذف مقاله"
                                      data-confirm-text="مقاله برای همیشه حذف خواهد شد."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-medium hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                             viewBox="0 0 24 24">
                                            <path fill="none" stroke="currentColor" stroke-linecap="round"
                                                  stroke-linejoin="round" stroke-width="1.5" d="M18 6L6 18m12 0L6 6"/>
                                        </svg>
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center">
                            <h3 class="text-gray-500 dark:text-gray-400 font-medium">هیچ مقاله ای یافت نشد!</h3>
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
