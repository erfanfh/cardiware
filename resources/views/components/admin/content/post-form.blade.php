@php use App\Domain\Content\Models\Post; @endphp
@props([
    'post' => null,
    'mode' => 'create',
])

@php
    /** @var Post|null $post */

    $isEdit = $mode === 'edit';

    $action = $isEdit
        ? route('admin.posts.update', $post)
        : route('admin.posts.store');

    $submitLabel = $isEdit ? 'ذخیره تغییرات' : 'ذخیره نوشته';

    $title = old('title', $post->title ?? '');
    $body = old('body', $post->body ?? '');
    $is_active = old('is_active', $post->is_active ?? false);
    $published_at = old('published_at', isset($post?->published_at) ? $post->published_at : '');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-5 mb-12">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-6">
        <div class="pb-6 border-b border-dashed border-gray-200 dark:border-gray-700">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">محتوای نوشته</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">عنوان، تصویر، محتوا و تاریخ انتشار را تعیین کنید.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label for="title" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <span class="text-red-500">*</span>
                        عنوان نوشته
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ $title }}"
                        class="px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-colors"
                        placeholder="مثلاً: چگونه آشپر خوبی باشیم؟"
                    >
                </div>

                <div class="flex flex-col gap-1">
                    <label for="published_at" class="text-sm font-medium text-gray-700 dark:text-gray-300">تاریخ انتشار</label>
                    <input
                        data-jdp
                        autocomplete="off"
                        id="published_at"
                        name="published_at"
                        value="{{ $published_at ? jalali_date($published_at) : '' }}"
                        class="px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-colors"
                    >
                </div>

                <div class="col-span-2 flex flex-col gap-1">
                    <label for="body" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <span class="text-red-500">*</span>
                        متن نوشته
                    </label>
                    <textarea
                        id="body"
                        name="body"
                        class="px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-colors editor"
                        rows="6"
                    >{{ $body }}</textarea>
                </div>

                <div class="col-span-2 grid grid-cols-2 gap-4">
                    <div class="col-span-2 flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">وضعیت انتشار</label>
                        <div class="flex items-center gap-3 cursor-pointer select-none" onclick="document.getElementById('is_active').click()">
                            <div class="relative">
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    name="is_active"
                                    value="1"
                                    {{ $is_active ? 'checked' : '' }}
                                    class="sr-only peer"
                                >
                                <div class="w-10 h-6 rounded-full bg-gray-300 dark:bg-gray-600 transition-colors duration-300 ease-in-out peer-checked:bg-gradient-to-r peer-checked:from-orange-500 peer-checked:to-orange-500">
                                    <div class="absolute top-1 left-1 w-4 h-4 rounded-full bg-white shadow transform transition-transform duration-300 ease-in-out peer-checked:translate-x-4"></div>
                                </div>
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">انتشار نوشته</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3 mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
        <button type="submit"
                class="px-5 py-2.5 rounded-full bg-gradient-to-r from-orange-500 to-orange-500 text-white text-sm font-medium shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
            {{ $submitLabel }}
        </button>
    </div>
</form>
