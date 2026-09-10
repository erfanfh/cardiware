@php
    $quickEditRows = $posts
        ->mapWithKeys(function ($post) {
            return [
                $post->id => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'urls' => [
                        'detail' => front_route('contents.detail', $post->slug),
                        'edit' => route('admin.posts.edit', $post),
                        'destroy' => route('admin.posts.destroy', $post),
                    ],
                    'status' => [
                        'value' => $post->status?->value,
                        'label' => $post->status?->label() ?? 'نامشخص',
                        'color' => $post->status === \App\Domain\Content\Enums\PostStatusEnum::ACTIVE ? 'green' : 'red',
                    ],
                    'tags' => $post->tags
                        ->map(fn($tag) => [
                            'id' => $tag->id,
                            'title' => $tag->title,
                            'filter_url' => route('admin.posts.index', ['tag_ids' => [$tag->id]]),
                        ])
                        ->values()
                        ->all(),
                    'categories' => $post->categories
                        ->map(fn($category) => [
                            'id' => $category->id,
                            'title' => $category->title,
                            'filter_url' => route('admin.posts.index', ['category_ids' => [$category->id]]),
                        ])
                        ->values()
                        ->all(),
                ],
            ];
        })
        ->all();
@endphp

<div
    x-data="adminListQuickEdit(@js($quickEditRows), @js(session('quick_edit_row_id')))"
    x-on:keydown.window="onEscape($event)"
    class="space-y-4"
>
    <x-admin.list.table>
        <x-admin.list.thead>
            <tr>
                <x-admin.list.th width="44px" align="center">
                    <x-admin.list.bulk-checkbox
                        :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($postIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($postIds) . ')'"
                    />
                </x-admin.list.th>

                <x-admin.list.th width="90px">تصویر</x-admin.list.th>

                <x-admin.list.th>عنوان</x-admin.list.th>

                <x-admin.list.th>نویسنده</x-admin.list.th>

                <x-admin.list.th width="140px">وضعیت</x-admin.list.th>

                <x-admin.list.th>دسته‌بندی‌ها</x-admin.list.th>

                <x-admin.list.th>برچسب‌ها</x-admin.list.th>

                <x-admin.list.th>دیدگاه‌ها</x-admin.list.th>

                <x-admin.list.th>بازدید‌ها</x-admin.list.th>

                <x-admin.list.th>امتیاز سئو</x-admin.list.th>

                <x-admin.list.th
                    width="130px"
                    label="زمان ایجاد"
                    sortable
                    sortKey="published_at"
                    :current-sort="request('sort')"
                    :current-direction="request('direction', 'desc')"
                />
            </tr>
        </x-admin.list.thead>

        <x-admin.list.tbody>
            @if($posts->isNotEmpty())
                @foreach($posts as $post)
                    <x-admin.list.tr
                        ::class="{
                            'bg-brand-50/50 ring-1 ring-inset ring-brand-100 shadow-[inset_0_1px_0_rgba(255,255,255,0.6)]': isOpen({{ $post->id }}),
                            'bg-slate-50/90': !isOpen({{ $post->id }}) && isSelected({{ $post->id }})
                        }"
                    >
                        <x-admin.list.td align="center">
                            <x-admin.list.bulk-checkbox
                                name="ids[]"
                                :value="$post->id"
                                x-bind:checked="isSelected({{ $post->id }})"
                                x-on:change="toggleSelection({{ $post->id }})"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.list.thumbnail
                                :src="$post->picture?->url"
                                :alt="$post->title"
                                size="md"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="min-w-[280px]">
                                <a
                                    :href="rows[{{ $post->id }}]?.urls?.detail"
                                    target="_blank"
                                    class="inline-block leading-6 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                                    x-text="rows[{{ $post->id }}]?.title ?? ''"
                                ></a>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span>#{{ $post->id }}</span>

                                    <template x-if="rows[{{ $post->id }}]?.slug">
                                        <span class="text-slate-300 pb-1">•</span>
                                    </template>

                                    <template x-if="rows[{{ $post->id }}]?.slug">
                                        <span dir="ltr" x-text="rows[{{ $post->id }}]?.slug"></span>
                                    </template>
                                </div>

                                <x-admin.list.row-actions>
                                    <x-admin.list.action
                                        tag="a"
                                        ::href="rows[{{ $post->id }}]?.urls?.edit"
                                        variant="primary"
                                    >
                                        ویرایش
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.list.action
                                        tag="button"
                                        x-on:click="toggle({{ $post->id }})"
                                        x-text="isOpen({{ $post->id }}) ? 'بستن ویرایش سریع' : 'ویرایش سریع'"
                                    >
                                        ویرایش سریع
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.list.action
                                        tag="a"
                                        ::href="rows[{{ $post->id }}]?.urls?.detail"
                                        target="_blank"
                                    >
                                        مشاهده
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.form
                                        bind-action="rows[{{ $post->id }}]?.urls?.destroy"
                                        method="delete"
                                        class="js-confirm"
                                        data-confirm-title="حذف نوشته"
                                        data-confirm-text="آیا از حذف نوشته «{{ $post->title }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست."
                                    >
                                        <x-admin.list.action
                                            tag="button"
                                            type="submit"
                                            variant="danger"
                                        >
                                            حذف
                                        </x-admin.list.action>
                                    </x-admin.form>
                                </x-admin.list.row-actions>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.badge color="slate" size="md"
                                           class="whitespace-nowrap [&_svg]:w-[13px] [&_svg]:h-[13px]">
                                @include('icons.user')
                                {{ $post->author->display_name }}
                                ({{ $post->author->id }})
                            </x-admin.badge>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.badge
                                color="green"
                                x-show="rows[{{ $post->id }}]?.status?.color === 'green'"
                                x-text="rows[{{ $post->id }}]?.status?.label ?? 'نامشخص'"
                            ></x-admin.badge>

                            <x-admin.badge
                                color="red"
                                x-show="rows[{{ $post->id }}]?.status?.color === 'red'"
                                x-text="rows[{{ $post->id }}]?.status?.label ?? 'نامشخص'"
                            ></x-admin.badge>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="flex flex-wrap gap-2">
                                <template x-if="rows[{{ $post->id }}]?.categories?.length">
                                    <template x-for="category in rows[{{ $post->id }}].categories" :key="category.id">
                                        <a
                                            :href="category.filter_url"
                                            :title="`نمایش نوشته‌های دسته «${category.title}»`"
                                            class="rounded-full transition hover:opacity-75 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                        >
                                            <x-admin.badge
                                                x-text="category.title"
                                                size="md"
                                                class="cursor-pointer whitespace-nowrap"
                                            ></x-admin.badge>
                                        </a>
                                    </template>
                                </template>

                                <template x-if="!(rows[{{ $post->id }}]?.categories?.length)">
                                    <span class="text-xs text-slate-400">---</span>
                                </template>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="flex flex-wrap gap-2">
                                <template x-if="rows[{{ $post->id }}]?.tags?.length">
                                    <template x-for="tag in rows[{{ $post->id }}].tags" :key="tag.id">
                                        <a
                                            :href="tag.filter_url"
                                            :title="`نمایش نوشته‌های برچسب «${tag.title}»`"
                                            class="rounded-full transition hover:opacity-75 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                        >
                                            <x-admin.badge
                                                x-text="tag.title"
                                                size="md"
                                                class="cursor-pointer whitespace-nowrap"
                                            ></x-admin.badge>
                                        </a>
                                    </template>
                                </template>

                                <template x-if="!(rows[{{ $post->id }}]?.tags?.length)">
                                    <span class="text-xs text-slate-400">---</span>
                                </template>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <a href="{{ route('admin.comments.index') . '?post_id=' . $post->id }}">
                                <x-admin.badge size="md">{{ $post->comments_count }}</x-admin.badge>
                            </a>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.badge size="md">{{ page_views($post) }}</x-admin.badge>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.seo.score-indicator :score="$post->seoAnalysis?->score"/>
                        </x-admin.list.td>

                        <x-admin.list.td muted>
                            <div class="text-xs leading-6">
                                <div>{{ jalali_date($post->published_at, 'Y/m/d') }}</div>
                                <div>{{ jalali_date($post->published_at, 'H:i') }}</div>
                            </div>
                        </x-admin.list.td>
                    </x-admin.list.tr>

                    @include('admin.content.posts.partials.quick-edit')
                @endforeach
            @else
                <x-admin.list.empty
                    title="نوشته‌ای برای نمایش وجود ندارد"
                    description="هنوز هیچ نوشته‌ای ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                    action-text="افزودن نوشته جدید"
                    :action-href="route('admin.posts.create')"
                />
            @endif
        </x-admin.list.tbody>
    </x-admin.list.table>
</div>
