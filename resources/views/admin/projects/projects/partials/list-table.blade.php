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
                    <label class="inline-flex cursor-pointer items-center justify-center">
                        <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                            :checked="isAllSelected(@js($projectIds))"
                            x-on:change="toggleAll(@js($projectIds))"
                        >
                    </label>
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
                            <label class="inline-flex cursor-pointer items-center justify-center">
                                <input
                                    type="checkbox"
                                    name="ids[]"
                                    value="{{ $project->id }}"
                                    class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                                    :checked="isSelected({{ $project->id }})"
                                    x-on:change="toggleSelection({{ $project->id }})"
                                >
                            </label>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div
                                class="h-18 w-20 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm">
                                @if($project->cover?->url)
                                    <img src="{{ $project->cover?->url }}" alt="{{ $project->title }}"
                                         class="h-full w-full object-cover">
                                @else
                                    <div
                                        class="flex h-full w-full items-center justify-center text-xs text-slate-400">
                                        بدون تصویر
                                    </div>
                                @endif
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="min-w-[280px]">
                                <a
                                    :href="rows[{{ $project->id }}]?.urls?.detail"
                                    target="_blank"
                                    class="line-clamp-1 text-sm font-bold text-slate-800 transition hover:text-brand-600"
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

                                <x-admin.list.row-actions
                                    class="opacity-100 transition md:opacity-0 md:group-hover:opacity-100">
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
                                        class="cursor-pointer"
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

                                    <x-admin.list.action
                                        class="cursor-pointer"
                                        tag="button"
                                        variant="danger"
                                    >
                                        حذف
                                    </x-admin.list.action>
                                </x-admin.list.row-actions>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1"
                                :class="{
                                    'bg-emerald-50 text-emerald-700 ring-emerald-200': rows[{{ $project->id }}]?.status?.color === 'green',
                                    'bg-rose-50 text-rose-700 ring-rose-200': rows[{{ $project->id }}]?.status?.color === 'red',
                                    'bg-slate-100 text-slate-700 ring-slate-200': !rows[{{ $project->id }}]?.status?.color || !['green', 'red'].includes(rows[{{ $project->id }}]?.status?.color)
                                }"
                                x-text="rows[{{ $project->id }}]?.status?.label ?? 'نامشخص'"
                            ></span>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="min-w-[120px] space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">پیشرفت</span>
                                    <span
                                        class="font-bold text-slate-700"
                                        x-text="`${rows[{{ $project->id }}]?.progress_percent ?? 0}%`"
                                    ></span>
                                </div>

                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full bg-brand-500 transition-all duration-300"
                                        :style="`width: ${Math.max(0, Math.min(100, rows[{{ $project->id }}]?.progress_percent ?? 0))}%`"
                                    ></div>
                                </div>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="flex flex-wrap gap-2">
                                <template x-if="rows[{{ $project->id }}]?.tags?.length">
                                    <template x-for="tag in rows[{{ $project->id }}].tags" :key="tag.id">
                                        <span
                                            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200"
                                            x-text="tag.title"
                                        ></span>
                                    </template>
                                </template>

                                <template x-if="!(rows[{{ $project->id }}]?.tags?.length)">
                                    <span class="text-xs text-slate-400">بدون برچسب</span>
                                </template>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td muted>
                            <div class="text-xs leading-6">
                                <div>{{ jalali_date($project->updated_at, 'Y/m/d') }}</div>
                                <div>{{ jalali_date($project->updated_at, 'H:i') }}</div>
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
