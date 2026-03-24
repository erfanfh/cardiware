@extends('admin.layout')

@section('title', 'لیست پروژه‌ها')

@section('content')
    @php
        $bulkActionOptions = [
            '' => 'کارهای دسته‌جمعی',
            'publish' => 'انتشار',
            'draft' => 'تبدیل به پیش‌نویس',
            'archive' => 'بایگانی',
            'delete' => 'حذف',
        ];
    @endphp

    <form method="GET" action="{{ route('admin.projects.index') }}" class="space-y-5">
        <x-admin.list.toolbar>
            <div class="flex flex-col gap-4">
                <x-admin.list.toolbar-meta
                    title="لیست پروژه‌ها"
                    description="از این بخش می‌توانی پروژه‌ها را مدیریت، جستجو، فیلتر و ویرایش سریع کنی."
                    :count="$projects->total()"
                    count-label="پروژه"
                />

                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <x-admin.list.toolbar-section stretch>
                        <x-admin.list.search
                            name="search"
                            :value="request('search')"
                            placeholder="جستجو در عنوان، اسلاگ و ..."
                        />

                        <x-admin.list.filters>
                            <div class="min-w-[180px]">
                                <x-admin.form.select
                                    name="status"
                                    :options="['' => 'همه وضعیت‌ها'] + $statuses"
                                    :value="request('status')"
                                />
                            </div>

                            <div class="min-w-[180px]">
                                <x-admin.form.input
                                    name="tag"
                                    :value="request('tag')"
                                    placeholder="فیلتر بر اساس برچسب"
                                />
                            </div>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700"
                            >
                                اعمال فیلتر
                            </button>

                            @if(request()->filled('search') || request()->filled('status') || request()->filled('tag'))
                                <a
                                    href="{{ route('admin.projects.index') }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    حذف فیلترها
                                </a>
                            @endif
                        </x-admin.list.filters>
                    </x-admin.list.toolbar-section>

                    <x-admin.list.toolbar-section>
                        <a
                            href="{{ route('admin.projects.create') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800"
                        >
                            افزودن پروژه جدید
                        </a>
                    </x-admin.list.toolbar-section>
                </div>
            </div>
        </x-admin.list.toolbar>
    </form>

    @php
        $activeFilters = [];

        if (filled(request('search'))) {
            $activeFilters[] = [
                'label' => 'جستجو: ' . request('search'),
                'remove_url' => request()->fullUrlWithQuery(['search' => null]),
            ];
        }

        if (filled(request('status'))) {
            $activeFilters[] = [
                'label' => 'وضعیت: ' . ($statuses[request('status')] ?? request('status')),
                'remove_url' => request()->fullUrlWithQuery(['status' => null]),
            ];
        }

        if (filled(request('tag'))) {
            $activeFilters[] = [
                'label' => 'برچسب: ' . request('tag'),
                'remove_url' => request()->fullUrlWithQuery(['tag' => null]),
            ];
        }
    @endphp

    <x-admin.list.active-filters
        :items="$activeFilters"
        :clear-url="route('admin.projects.index')"
    />

    @php
        $summaryText = null;

        if (filled(request('search'))) {
            $summaryText = 'نتایج جستجو برای «' . request('search') . '» نمایش داده می‌شود.';
        } elseif (filled(request('status')) && isset($statuses[request('status')])) {
            $summaryText = 'فقط پروژه‌های با وضعیت «' . $statuses[request('status')] . '» نمایش داده می‌شوند.';
        } elseif (filled(request('tag'))) {
            $summaryText = 'پروژه‌های دارای برچسب «' . request('tag') . '» نمایش داده می‌شوند.';
        }
    @endphp

    <x-admin.list.summary :text="$summaryText"/>

    <form method="POST" action="{{ route('admin.projects.bulk-action') }}" class="space-y-4">
        @csrf

        <div
            class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <x-admin.list.bulk-actions
                    name="bulk_action"
                    :options="$bulkActionOptions"
                    button-text="اعمال"
                />
            </div>

            <div class="text-xs text-slate-500">
                برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند پروژه را انتخاب کن.
            </div>
        </div>

        @php
            $projectIds = $projects->pluck('id')->values()->all();
        @endphp

        <div
            x-data="adminListQuickEdit(@js(session('quick_edit_row_id')))"
            x-on:keydown.window="onEscape($event)"
            class="space-y-4"
        >
            <x-admin.list.selection-bar>
                <button
                    type="submit"
                    formaction="{{ route('admin.projects.bulk-action') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    اعمال عملیات گروهی
                </button>

                <button
                    type="button"
                    x-on:click="clearSelection()"
                    class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                >
                    لغو انتخاب
                </button>
            </x-admin.list.selection-bar>

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

                        <x-admin.list.sortable-th
                            label="عنوان"
                            key="title"
                            :current-sort="request('sort')"
                            :current-direction="request('direction', 'desc')"
                        />

                        <x-admin.list.sortable-th
                            width="140px"
                            label="وضعیت"
                            key="status"
                            :current-sort="request('sort')"
                            :current-direction="request('direction', 'desc')"
                        />

                        <x-admin.list.sortable-th
                            width="170px"
                            label="پیشرفت"
                            key="progress_percentage"
                            :current-sort="request('sort')"
                            :current-direction="request('direction', 'desc')"
                        />

                        <x-admin.list.th>برچسب‌ها</x-admin.list.th>

                        <x-admin.list.sortable-th
                            width="130px"
                            label="آخرین بروزرسانی"
                            key="updated_at"
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
                                        class="h-14 w-20 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm">
                                        @if($project->cover_url)
                                            <img src="{{ $project->cover_url }}" alt=""
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
                                        <a href="{{ route('admin.projects.edit', $project) }}"
                                           class="line-clamp-1 text-sm font-bold text-slate-800 transition hover:text-brand-600">
                                            {{ $project->title }}
                                        </a>

                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                            <span>#{{ $project->id }}</span>

                                            @if($project->slug)
                                                <span class="text-slate-300">•</span>
                                                <span dir="ltr">{{ $project->slug }}</span>
                                            @endif
                                        </div>

                                        <x-admin.list.row-actions
                                            class="opacity-100 transition md:opacity-0 md:group-hover:opacity-100">
                                            <x-admin.list.action
                                                href="{{ route('admin.projects.edit', $project) }}"
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
                                                href="{{ route('projects.detail', $project) }}"
                                                target="_blank"
                                            >
                                                مشاهده
                                            </x-admin.list.action>

                                            <span class="text-slate-300">|</span>

                                            <x-admin.list.action
                                                tag="button"
                                                variant="danger"
                                            >
                                                حذف
                                            </x-admin.list.action>

                                            <x-admin.dropdown>
                                                <x-slot:trigger>
                                                    <button
                                                        type="button"
                                                        x-on:click="open = !open"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                                                    >
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                            <circle cx="5" cy="12" r="1.8"/>
                                                            <circle cx="12" cy="12" r="1.8"/>
                                                            <circle cx="19" cy="12" r="1.8"/>
                                                        </svg>
                                                    </button>
                                                </x-slot:trigger>

                                                <x-admin.dropdown-item
                                                    href="{{ route('admin.projects.edit', $project) }}">
                                                    <span>ویرایش کامل</span>
                                                </x-admin.dropdown-item>

                                                <x-admin.dropdown-item href="{{ route('projects.detail', $project) }}"
                                                                       target="_blank">
                                                    <span>مشاهده در سایت</span>
                                                </x-admin.dropdown-item>

                                                <x-admin.dropdown-item tag="button">
                                                    <span>تکثیر پروژه</span>
                                                </x-admin.dropdown-item>

                                                <x-admin.dropdown-item tag="button" variant="danger"
                                                                       x-on:click="$dispatch('confirm-delete-project', { id: {{ $project->id }} })">
                                                    <span>حذف پروژه</span>
                                                </x-admin.dropdown-item>
                                            </x-admin.dropdown>
                                        </x-admin.list.row-actions>
                                    </div>
                                </x-admin.list.td>

                                <x-admin.list.td>
                                    @php
                                        $projectStatusBadgeColors = [
                                            'draft' => 'slate',
                                            'active' => 'green',
                                            'in_progress' => 'blue',
                                            'completed' => 'emerald',
                                            'archived' => 'yellow',
                                            'inactive' => 'red',
                                        ];
                                        $statusValue = $project->status?->value;
                                        $statusColor = $projectStatusBadgeColors[$statusValue] ?? 'slate';
                                    @endphp

                                    <x-admin.badge :color="$statusColor">
                                        {{ $project->status?->label() ?? 'نامشخص' }}
                                    </x-admin.badge>
                                </x-admin.list.td>

                                <x-admin.list.td>
                                    <x-admin.progress.mini :value="$project->progress_percentage ?? 0"/>
                                </x-admin.list.td>

                                <x-admin.list.td>
                                    <div class="flex flex-wrap gap-2">
                                        @if(!empty($project->tags) && count($project->tags))
                                            @foreach($project->tags as $tag)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                                            {{ $tag }}
                                        </span>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-slate-400">بدون برچسب</span>
                                        @endif
                                    </div>
                                </x-admin.list.td>

                                <x-admin.list.td muted>
                                    <div class="text-xs leading-6">
                                        <div>{{ jalali_date($project->updated_at, 'Y/m/d') }}</div>
                                    </div>
                                </x-admin.list.td>
                            </x-admin.list.tr>

                            <x-admin.list.quick-edit-row :row-id="$project->id" colspan="7">
                                <div
                                    x-data="{
                                status: @js(old('status', $project->status?->value)),
                                progress: @js(old('progress_percentage', $project->progress_percentage ?? 0))
                            }"
                                    class="space-y-5"
                                >
                                    <form
                                        method="POST"
                                        action="{{ route('admin.projects.quick-update', $project) }}"
                                        class="space-y-5"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <div
                                            class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                                            <div class="space-y-2">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-sm font-bold text-slate-800">
                                                        ویرایش سریع پروژه
                                                    </h3>

                                                    <x-admin.badge color="blue">
                                                        شناسه: {{ $project->id }}
                                                    </x-admin.badge>
                                                </div>

                                                <p class="text-xs text-slate-500">
                                                    اطلاعات اصلی پروژه را سریع و بدون خروج از لیست ویرایش کن.
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2">
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700"
                                                >
                                                    ذخیره تغییرات
                                                </button>

                                                <button
                                                    type="button"
                                                    x-on:click="close()"
                                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                                >
                                                    انصراف
                                                </button>
                                            </div>
                                        </div>

                                        <x-admin.form.error-box/>

                                        <div class="grid grid-cols-12 gap-5">
                                            <div class="col-span-12 lg:col-span-4">
                                                <x-admin.field label="عنوان پروژه">
                                                    <x-admin.form.input
                                                        name="title"
                                                        :value="old('title', $project->title)"
                                                    />
                                                </x-admin.field>
                                            </div>

                                            <div class="col-span-12 lg:col-span-3">
                                                <x-admin.field label="اسلاگ">
                                                    <x-admin.form.input
                                                        name="slug"
                                                        dir="ltr"
                                                        :value="old('slug', $project->slug)"
                                                    />
                                                </x-admin.field>
                                            </div>

                                            <div class="col-span-12 sm:col-span-6 lg:col-span-2">
                                                <x-admin.field label="وضعیت">
                                                    <x-admin.form.select
                                                        name="status"
                                                        x-model="status"
                                                        :options="$statuses"
                                                        :value="old('status', $project->status?->value)"
                                                    />
                                                </x-admin.field>
                                            </div>

                                            <div class="col-span-12 sm:col-span-6 lg:col-span-3">
                                                <x-admin.field label="پیشرفت">
                                                    <div class="space-y-3">
                                                        <x-admin.form.input
                                                            type="number"
                                                            name="progress_percentage"
                                                            min="0"
                                                            max="100"
                                                            x-model="progress"
                                                            :value="old('progress_percentage', $project->progress_percentage ?? 0)"
                                                        />

                                                        <div class="space-y-2">
                                                            <div class="flex items-center justify-between text-xs">
                                                                <span class="text-slate-500">میزان پیشرفت</span>
                                                                <span class="font-bold text-slate-700"
                                                                      x-text="`${progress || 0}%`"></span>
                                                            </div>

                                                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                                                <div
                                                                    class="h-full rounded-full bg-brand-500 transition-all duration-300"
                                                                    :style="`width: ${Math.max(0, Math.min(100, progress || 0))}%`"
                                                                ></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </x-admin.field>
                                            </div>

                                            <div class="col-span-12 lg:col-span-6">
                                                <x-admin.field label="برچسب‌ها">
                                                    <x-admin.form.input
                                                        name="tags"
                                                        :value="old('tags', is_array($project->tags ?? null) ? implode('، ', $project->tags) : ($project->tags ?? ''))"
                                                        placeholder="مثلاً: لاراول، Livewire، Tailwind"
                                                    />
                                                </x-admin.field>
                                            </div>

                                            <div class="col-span-12 lg:col-span-6">
                                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                                    <div class="mb-3 flex items-center justify-between">
                                                        <h4 class="text-xs font-bold text-slate-700">
                                                            پیش‌نمایش سریع
                                                        </h4>

                                                        <x-admin.badge color="slate">
                                                            Quick Edit
                                                        </x-admin.badge>
                                                    </div>

                                                    <div class="space-y-3 text-sm">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <span class="text-slate-500">وضعیت انتخاب‌شده</span>
                                                            <span class="font-bold text-slate-800"
                                                                  x-text="status || 'نامشخص'"></span>
                                                        </div>

                                                        <div class="flex items-center justify-between gap-3">
                                                            <span class="text-slate-500">پیشرفت</span>
                                                            <span class="font-bold text-slate-800"
                                                                  x-text="`${progress || 0}%`"></span>
                                                        </div>

                                                        <div
                                                            class="border-t border-slate-200 pt-3 text-xs leading-6 text-slate-500">
                                                            این بخش فقط برای پیش‌نمایش سریع تغییرات است و بعد از ذخیره،
                                                            داده واقعی به‌روزرسانی می‌شود.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </x-admin.list.quick-edit-row>
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

            @if(method_exists($projects, 'links'))
                <x-admin.list.pagination-wrap>
                    <div class="text-xs text-slate-500">
                        @if(method_exists($projects, 'total'))
                            نمایش
                            <span class="font-bold text-slate-700">{{ $projects->firstItem() }}</span>
                            تا
                            <span class="font-bold text-slate-700">{{ $projects->lastItem() }}</span>
                            از
                            <span class="font-bold text-slate-700">{{ $projects->total() }}</span>
                            پروژه
                        @endif
                    </div>

                    <div
                        class="[&_nav]:flex [&_nav]:items-center [&_nav]:gap-1 [&_span]:rounded-lg [&_span]:border [&_span]:border-slate-200 [&_span]:bg-white [&_span]:px-3 [&_span]:py-1.5 [&_span]:text-sm [&_a]:rounded-lg [&_a]:border [&_a]:border-slate-200 [&_a]:bg-white [&_a]:px-3 [&_a]:py-1.5 [&_a]:text-sm [&_a]:text-slate-700 [&_a]:transition [&_a:hover]:bg-slate-50">
                        {{ $projects->links() }}
                    </div>
                </x-admin.list.pagination-wrap>
            @endif
        </div>
    </form>

    <x-admin.modal.confirm
        name="deleteProject"
        title="حذف پروژه"
        description="با حذف این پروژه، اطلاعات وابسته ممکن است تحت تأثیر قرار بگیرند. این عملیات را فقط در صورت اطمینان انجام بده."
        confirm-text="بله، حذف شود"
        cancel-text="انصراف"
    />

    <form
        x-data="{ deleteId: null }"
        x-on:confirm-delete-project.window="
        $dispatch('open-confirm-modal', {
            name: 'deleteProject',
            payload: { id: $event.detail.id }
        })
    "
        x-on:confirm-modal-accepted.window="
        if ($event.detail.name !== 'deleteProject') return;
        deleteId = $event.detail.payload.id;
        $refs.projectDeleteForm.action = '{{ url('/admin/projects') }}/' + deleteId;
        $refs.projectDeleteForm.submit();
    "
        x-ref="projectDeleteFormWrapper"
        class="hidden"
    >
        <form x-ref="projectDeleteForm" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminListQuickEdit', (initialOpenId = null) => ({
                openId: initialOpenId,
                selected: [],

                isOpen(id) {
                    return this.openId === id;
                },

                open(id) {
                    this.openId = id;
                },

                close() {
                    this.openId = null;
                },

                toggle(id) {
                    this.openId = this.openId === id ? null : id;
                },

                onEscape(event) {
                    if (event.key === 'Escape') {
                        this.close();
                    }
                },

                toggleSelection(id) {
                    if (this.selected.includes(id)) {
                        this.selected = this.selected.filter(item => item !== id);
                        return;
                    }

                    this.selected.push(id);
                },

                isSelected(id) {
                    return this.selected.includes(id);
                },

                toggleAll(ids) {
                    if (this.selected.length === ids.length) {
                        this.selected = [];
                        return;
                    }

                    this.selected = [...ids];
                },

                clearSelection() {
                    this.selected = [];
                },

                get selectedCount() {
                    return this.selected.length;
                },

                isAllSelected(ids) {
                    return ids.length > 0 && this.selected.length === ids.length;
                }
            }));
        });
    </script>
@endpush
