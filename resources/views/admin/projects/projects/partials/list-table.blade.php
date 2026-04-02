@php
    $quickEditRows = $projects
        ->mapWithKeys(function ($project) {
            return [
                $project->id => [
                    'id' => $project->id,
                    'title' => $project->title,
                    'slug' => $project->slug,
                    'urls' => [
                        'detail' => route('projects.detail', $project),
                        'edit' => route('admin.projects.edit', $project),
                        'destroy' => route('admin.projects.destroy', $project),
                    ],
                    'status' => [
                        'value' => $project->status?->value,
                        'label' => $project->status?->label() ?? 'نامشخص',
                        'color' => $project->status === \App\Domain\Project\Enums\ProjectStatusEnum::ACTIVE ? 'green' : 'red',
                    ],
                    'progress_percent' => (int) ($project->progress_percent ?? 0),
                    'tags' => $project->tags
                        ->map(fn($tag) => [
                            'id' => $tag->id,
                            'title' => $tag->title,
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
                        :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($projectIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($projectIds) . ')'"
                    />
                </x-admin.list.th>

                <x-admin.list.th width="90px">تصویر</x-admin.list.th>

                <x-admin.list.th>عنوان</x-admin.list.th>

                <x-admin.list.th width="140px">وضعیت</x-admin.list.th>

                <x-admin.list.th
                    width="170px"
                    label="درصد پیشرفت"
                    sortable
                    sortKey="progress_percent"
                    :current-sort="request('sort')"
                    :current-direction="request('direction', 'desc')"
                />

                <x-admin.list.th>برچسب‌ها</x-admin.list.th>

                <x-admin.list.th
                    width="130px"
                    label="زمان ایجاد"
                    sortable
                    sortKey="created_at"
                    :current-sort="request('sort')"
                    :current-direction="request('direction', 'desc')"
                />
            </tr>
        </x-admin.list.thead>

        <x-admin.list.tbody>
            @if($projects->isNotEmpty())
                @foreach($projects as $project)
                    <x-admin.list.tr
                        ::class="{
                                    'bg-brand-50/50 ring-1 ring-inset ring-brand-100 shadow-[inset_0_1px_0_rgba(255,255,255,0.6)]': isOpen({{ $project->id }}),
                                    'bg-slate-50/90': !isOpen({{ $project->id }}) && isSelected({{ $project->id }})
                                }"
                    >
                        <x-admin.list.td align="center">
                            <x-admin.list.bulk-checkbox
                                name="ids[]"
                                :value="$project->id"
                                x-bind:checked="isSelected({{ $project->id }})"
                                x-on:change="toggleSelection({{ $project->id }})"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.list.thumbnail
                                :src="$project->cover?->url"
                                :alt="$project->title"
                                size="md"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="min-w-[280px]">
                                <a
                                    :href="rows[{{ $project->id }}]?.urls?.detail"
                                    target="_blank"
                                    class="inline-block leading-6 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                                    x-text="rows[{{ $project->id }}]?.title ?? ''"
                                ></a>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span>#{{ $project->id }}</span>

                                    <template x-if="rows[{{ $project->id }}]?.slug">
                                        <span class="text-slate-300 pb-1">•</span>
                                    </template>

                                    <template x-if="rows[{{ $project->id }}]?.slug">
                                        <span dir="ltr" x-text="rows[{{ $project->id }}]?.slug"></span>
                                    </template>
                                </div>

                                <x-admin.list.row-actions>
                                    <x-admin.list.action
                                        tag="a"
                                        ::href="rows[{{ $project->id }}]?.urls?.edit"
                                        variant="primary"
                                    >
                                        ویرایش
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.list.action
                                        tag="button"
                                        x-on:click="toggle({{ $project->id }})"
                                        x-text="isOpen({{ $project->id }}) ? 'بستن ویرایش سریع' : 'ویرایش سریع'"
                                    >
                                        ویرایش سریع
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.list.action
                                        tag="a"
                                        ::href="rows[{{ $project->id }}]?.urls?.detail"
                                        target="_blank"
                                    >
                                        مشاهده
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.form
                                        bind-action="rows[{{ $project->id }}]?.urls?.destroy"
                                        method="delete"
                                        class="js-confirm"
                                        data-confirm-title="حذف پروژه"
                                        data-confirm-text="آیا از حذف پروژه «{{ $project->title }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست."
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
                            <x-admin.badge
                                color="green"
                                x-show="rows[{{ $project->id }}]?.status?.color === 'green'"
                                x-text="rows[{{ $project->id }}]?.status?.label ?? 'نامشخص'"
                            ></x-admin.badge>

                            <x-admin.badge
                                color="red"
                                x-show="rows[{{ $project->id }}]?.status?.color === 'red'"
                                x-text="rows[{{ $project->id }}]?.status?.label ?? 'نامشخص'"
                            ></x-admin.badge>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="min-w-[120px]">
                                <div class="mb-1 flex items-center justify-between text-[11px] text-slate-500">
                                    <span>پیشرفت</span>
                                    <span
                                        class="font-bold text-slate-700"
                                        x-text="`${rows[{{ $project->id }}]?.progress_percent ?? 0}%`"
                                    ></span>
                                </div>

                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full transition-all duration-300"
                                        :class="{
                                            'bg-emerald-500': (rows[{{ $project->id }}]?.progress_percent ?? 0) >= 100,
                                            'bg-sky-500': (rows[{{ $project->id }}]?.progress_percent ?? 0) >= 70 && (rows[{{ $project->id }}]?.progress_percent ?? 0) < 100,
                                            'bg-yellow-500': (rows[{{ $project->id }}]?.progress_percent ?? 0) >= 40 && (rows[{{ $project->id }}]?.progress_percent ?? 0) < 70,
                                            'bg-red-500': (rows[{{ $project->id }}]?.progress_percent ?? 0) >= 1 && (rows[{{ $project->id }}]?.progress_percent ?? 0) < 40,
                                            'bg-slate-300': (rows[{{ $project->id }}]?.progress_percent ?? 0) < 1
                                        }"
                                        :style="`width: ${Math.max(0, Math.min(100, rows[{{ $project->id }}]?.progress_percent ?? 0))}%`"
                                    ></div>
                                </div>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="flex flex-wrap gap-2">
                                <template x-if="rows[{{ $project->id }}]?.tags?.length">
                                    <template x-for="tag in rows[{{ $project->id }}].tags" :key="tag.id">
                                        <x-admin.badge
                                            x-text="tag.title"
                                            size="md"
                                            class="whitespace-nowrap"
                                        ></x-admin.badge>
                                    </template>
                                </template>

                                <template x-if="!(rows[{{ $project->id }}]?.tags?.length)">
                                    <span class="text-xs text-slate-400">---</span>
                                </template>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td muted>
                            <div class="text-xs leading-6">
                                <div>{{ jalali_date($project->created_at, 'Y/m/d') }}</div>
                                <div>{{ jalali_date($project->created_at, 'H:i') }}</div>
                            </div>
                        </x-admin.list.td>
                    </x-admin.list.tr>

                    @include('admin.projects.projects.partials.quick-edit')
                @endforeach
            @else
                <x-admin.list.empty
                    colspan="7"
                    title="پروژه‌ای برای نمایش وجود ندارد"
                    description="هنوز هیچ پروژه‌ای ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                    action-text="افزودن پروژه جدید"
                    :action-href="route('admin.projects.create')"
                />
            @endif
        </x-admin.list.tbody>
    </x-admin.list.table>
</div>
