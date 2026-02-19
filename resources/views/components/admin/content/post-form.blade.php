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
    $excerpt = old('excerpt', $post->excerpt ?? '');
    $body = old('body', $post->body ?? '');
    $is_active = old('is_active', $post->is_active ?? false);
    $published_at = old('published_at', isset($post?->published_at) ? $post->published_at : '');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-[var(--radius-xl)] shadow-[var(--shadow-soft)] border border-gray-200 dark:border-gray-700 p-5 mb-0 md:mb-12">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-6">
        <div>
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">محتوای نوشته</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">عنوان، تصویر، محتوا و تاریخ انتشار را تعیین کنید.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <livewire:admin.content.slug-generator
                        :initialTitle="$title"
                        :initialSlug="old('slug', $post->slug ?? '')"
                        :postId="$post->id ?? null"
                    />

                </div>

                <div class="flex flex-col gap-1">
                    <label for="published_at" class="text-xs font-medium text-gray-500 dark:text-gray-400">تاریخ انتشار</label>
                    <input
                        data-jdp
                        autocomplete="off"
                        id="published_at"
                        name="published_at"
                        value="{{ $published_at ? jalali_date($published_at) : '' }}"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                    >
                </div>

                <div class="flex flex-col gap-1">
                    <label for="categories" class="text-xs font-medium text-gray-500 dark:text-gray-400">دسته بندی</label>
                    <select name="categories[]"
                            placeholder="جستجو کنید..."
                            id="category-select"
                            multiple
                            autocomplete="off"
                            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                        @foreach($post ? $post->categories : [] as $category)
                            <option selected value="{{ $category->id }}">{{ $category->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1" x-data="tagInput()">
                    <label for="tags" class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        <span class="text-red-500">*</span>
                        برچسب ها
                    </label>
                    <div class="relative flex gap-2">
                        <input
                            type="text"
                            id="tags"
                            x-model="newTags"
                            @input.debounce.300ms="searchTags()"
                            @keydown.enter.prevent="addTags(selectedSuggestion ?? newTags)"
                            placeholder="تگ‌ها را با کاما جدا کنید"
                            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                        >
                        <button type="button" @click="addTags()" class="bg-[var(--brand-500)] text-white text-[0.86rem] inline-flex items-center justify-center rounded-full py-[7px] px-[14px] cursor-pointer hover:bg-[var(--brand-600)] dark:hover:bg-[var(--brand-400)] transition-transform">افزودن</button>

                        <ul x-show="suggestions.length > 0" class="absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 mt-1 rounded shadow z-50 max-h-40 overflow-auto">
                            <template x-for="(tag, index) in suggestions" :key="index">
                                <li
                                    @click="addTags(tag)"
                                    class="px-3 py-1 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                    x-text="tag"
                                ></li>
                            </template>
                        </ul>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-2">
                        <template x-for="(tag, index) in tags" :key="index">
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-700 flex items-center gap-1">
                                <span x-text="tag"></span>
                                <button type="button" @click="removeTag(index)" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-bold cursor-pointer">&times;</button>
                            </span>
                        </template>
                    </div>

                    <input type="hidden" name="tags" :value="tags.join(',')">
                </div>

                <div class="md:col-span-2 flex flex-col gap-1">
                    <label for="body" class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        <span class="text-red-500">*</span>
                        متن نوشته
                    </label>
                    <textarea
                        id="body"
                        name="body"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                        rows="6"
                    >{{ $body }}</textarea>
                </div>

                <div class="flex flex-col gap-1">
                    <label for="excerpt" class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        <span class="text-red-500">*</span>
                        خلاصه نوشته
                    </label>
                    <input
                        type="text"
                        id="excerpt"
                        name="excerpt"
                        value="{{ $excerpt }}"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                    >
                </div>

                <div class="flex flex-col gap-1">
                    <label for="status" class="text-xs font-medium text-gray-500 dark:text-gray-400">وضعیت</label>
                    <select name="status" autocomplete="off" id="status"
                            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                        @foreach (\App\Domain\Content\Enums\StatusEnum::cases() as $status)
                            <option @if($post and $post->status === $status->value) selected @endif value="{{ $status->value }}">
                                {{ $status->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <x-admin.save-button :label="$submitLabel"/>

    <script>
        function tagInput() {
            return {
                newTags: '',
                tags: @json($post?->tags->pluck('title')->toArray() ?? []),
                suggestions: [],
                selectedSuggestion: null,

                addTags(tag = null) {
                    tag = tag ?? this.newTags;
                    if (!tag) return;

                    let splitTags = tag.split(',').map(t => t.trim()).filter(t => t);

                    splitTags.forEach(t => {
                        if (!this.tags.includes(t)) {
                            this.tags.push(t);
                        }
                    });

                    this.newTags = '';
                    this.suggestions = [];
                    this.selectedSuggestion = null;
                },

                removeTag(index) {
                    this.tags.splice(index, 1);
                },

                async searchTags() {
                    if (!this.newTags.trim()) {
                        this.suggestions = [];
                        return;
                    }

                    try {
                        const response = await fetch(`/admin/tags/search?query=${encodeURIComponent(this.newTags)}`);
                        const data = await response.json();
                        this.suggestions = data.filter(t => !this.tags.includes(t));
                    } catch (err) {
                        console.error(err);
                    }
                }
            }
        }
    </script>

    <script>
        let formChanged = false;

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');

            form.addEventListener('input', function () {
                formChanged = true;
            });

            form.addEventListener('submit', function () {
                formChanged = false;
            });
        });

        window.addEventListener('beforeunload', function (e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</form>
