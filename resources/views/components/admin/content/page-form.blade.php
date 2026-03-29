@php use App\Domain\Content\Models\Page; @endphp
@props([
    'page' => null,
    'mode' => 'create',
])

@php
    /** @var Page|null $page */

    $isEdit = $mode === 'edit';

    $action = $isEdit
        ? route('admin.pages.update', $page)
        : route('admin.pages.store');

    $submitLabel = $isEdit ? 'ذخیره تغییرات' : 'ذخیره صفحه';

    $title = old('title', $page->title ?? '');
    $excerpt = old('excerpt', $page->excerpt ?? '');
    $body = old('body', $page->body ?? '');
    $is_active = old('is_active', $page->is_active ?? false);
    $published_at = old('published_at', isset($page?->published_at) ? $page->published_at : '');
@endphp

<form action="{{ $action }}" method="post" enctype="multipart/form-data"
      class="js-confirm bg-white dark:bg-gray-800 rounded-[var(--radius-xl)] shadow-[var(--shadow-soft)] border border-gray-200 dark:border-gray-700 p-5 mb-0 md:mb-12">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-6">
        <div>
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">محتوای صفحه</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">عنوان، تصویر، محتوا و تاریخ انتشار را تعیین
                    کنید.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <livewire:admin.content.slug-generator
                        :initialTitle="$title"
                        :initialSlug="old('slug', $page->slug ?? '')"
                        :pageId="$page->id ?? null"
                    />

                </div>

                <div class="flex flex-col gap-1">
                    <label for="published_at" class="text-xs font-medium text-gray-500 dark:text-gray-400">تاریخ
                        انتشار</label>
                    <input
                        data-jdp
                        autocomplete="off"
                        id="published_at"
                        name="published_at"
                        value="{{ $published_at ? jalali_date($published_at) : '' }}"
                        class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                    >
                </div>

                <div class="md:col-span-2 flex flex-col gap-1">
                    <label for="body" class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        <span class="text-red-500">*</span>
                        متن صفحه
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
                        خلاصه صفحه
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
                        @foreach (\App\Domain\Content\Enums\PostStatusEnum::cases() as $status)
                            <option @if($page and $page->status === $status->value) selected
                                    @endif value="{{ $status->value }}">
                                {{ $status->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <x-admin.form.save-button :label="$submitLabel"/>

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
