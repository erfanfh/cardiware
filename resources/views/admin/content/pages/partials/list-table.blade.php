@php
    $quickEditRows = $pages
        ->mapWithKeys(function ($page) {
            return [
                $page->id => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'urls' => [
                        'detail' => front_route('contents.detail', $page->slug),
                        'edit' => route('admin.pages.edit', $page),
                        'destroy' => route('admin.pages.destroy', $page),
                    ],
                    'status' => [
                        'value' => $page->status?->value,
                        'label' => $page->status?->label() ?? 'نامشخص',
                        'color' => ($page->status->value === \App\Domain\Content\Enums\PostStatusEnum::ACTIVE->value) ? 'green' : 'red',
                    ],
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
                        :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($pageIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($pageIds) . ')'"
                    />
                </x-admin.list.th>

                <x-admin.list.th width="90px">تصویر</x-admin.list.th>

                <x-admin.list.th>عنوان</x-admin.list.th>

                <x-admin.list.th>نویسنده</x-admin.list.th>

                <x-admin.list.th width="140px">وضعیت</x-admin.list.th>

                <x-admin.list.th>بازدید‌ها</x-admin.list.th>

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
            @if($pages->isNotEmpty())
                @foreach($pages as $page)
                    <x-admin.list.tr
                        ::class="{
                            'bg-brand-50/50 ring-1 ring-inset ring-brand-100 shadow-[inset_0_1px_0_rgba(255,255,255,0.6)]': isOpen({{ $page->id }}),
                            'bg-slate-50/90': !isOpen({{ $page->id }}) && isSelected({{ $page->id }})
                        }"
                    >
                        <x-admin.list.td align="center">
                            <x-admin.list.bulk-checkbox
                                name="ids[]"
                                :value="$page->id"
                                x-bind:checked="isSelected({{ $page->id }})"
                                x-on:change="toggleSelection({{ $page->id }})"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.list.thumbnail
                                :src="$page->picture?->url"
                                :alt="$page->title"
                                size="md"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="min-w-[280px]">
                                <a
                                    :href="rows[{{ $page->id }}]?.urls?.detail"
                                    target="_blank"
                                    class="inline-block leading-6 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                                    x-text="rows[{{ $page->id }}]?.title ?? ''"
                                ></a>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span>#{{ $page->id }}</span>

                                    <template x-if="rows[{{ $page->id }}]?.slug">
                                        <span class="text-slate-300 pb-1">•</span>
                                    </template>

                                    <template x-if="rows[{{ $page->id }}]?.slug">
                                        <span dir="ltr" x-text="rows[{{ $page->id }}]?.slug"></span>
                                    </template>
                                </div>

                                <x-admin.list.row-actions>
                                    <x-admin.list.action
                                        tag="a"
                                        ::href="rows[{{ $page->id }}]?.urls?.edit"
                                        variant="primary"
                                    >
                                        ویرایش
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.list.action
                                        tag="button"
                                        x-on:click="toggle({{ $page->id }})"
                                        x-text="isOpen({{ $page->id }}) ? 'بستن ویرایش سریع' : 'ویرایش سریع'"
                                    >
                                        ویرایش سریع
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.list.action
                                        tag="a"
                                        ::href="rows[{{ $page->id }}]?.urls?.detail"
                                        target="_blank"
                                    >
                                        مشاهده
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.form
                                        bind-action="rows[{{ $page->id }}]?.urls?.destroy"
                                        method="delete"
                                        class="js-confirm"
                                        data-confirm-title="حذف برگه"
                                        data-confirm-text="آیا از حذف برگه «{{ $page->title }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست."
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
                                {{ $page->author->display_name }}
                                ({{ $page->author->id }})
                            </x-admin.badge>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.badge
                                color="green"
                                x-show="rows[{{ $page->id }}]?.status?.color === 'green'"
                                x-text="rows[{{ $page->id }}]?.status?.label ?? 'نامشخص'"
                            ></x-admin.badge>

                            <x-admin.badge
                                color="red"
                                x-show="rows[{{ $page->id }}]?.status?.color === 'red'"
                                x-text="rows[{{ $page->id }}]?.status?.label ?? 'نامشخص'"
                            ></x-admin.badge>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.badge size="md">0</x-admin.badge>
                        </x-admin.list.td>

                        <x-admin.list.td muted>
                            <div class="text-xs leading-6">
                                <div>{{ jalali_date($page->published_at, 'Y/m/d') }}</div>
                                <div>{{ jalali_date($page->published_at, 'H:i') }}</div>
                            </div>
                        </x-admin.list.td>
                    </x-admin.list.tr>

                    @include('admin.content.pages.partials.quick-edit')
                @endforeach
            @else
                <x-admin.list.empty
                    title="برگه‌ای برای نمایش وجود ندارد"
                    description="هنوز هیچ برگه‌ای ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                    action-text="افزودن برگه جدید"
                    :action-href="route('admin.pages.create')"
                />
            @endif
        </x-admin.list.tbody>
    </x-admin.list.table>
</div>
