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

        <div
            x-data="adminListQuickEdit(@js(session('quick_edit_row_id')))"
            x-on:keydown.window="onEscape($event)"
        >
            <x-admin.list.table>
                <x-admin.list.thead>
                    <tr>
                        <x-admin.list.th width="44px" align="center">
                            <x-admin.list.checkbox/>
                        </x-admin.list.th>

                        <x-admin.list.th width="90px">تصویر</x-admin.list.th>
                        <x-admin.list.th>عنوان</x-admin.list.th>
                        <x-admin.list.th width="140px">وضعیت</x-admin.list.th>
                        <x-admin.list.th width="170px">پیشرفت</x-admin.list.th>
                        <x-admin.list.th>برچسب‌ها</x-admin.list.th>
                        <x-admin.list.th width="130px">آخرین بروزرسانی</x-admin.list.th>
                    </tr>
                </x-admin.list.thead>

                <x-admin.list.tbody>
                    @if($projects->isNotEmpty())
                        @foreach($projects as $project)
                            <x-admin.list.tr
                                ::class="{ 'bg-brand-50/40 ring-1 ring-inset ring-brand-100': isOpen({{ $project->id }}) }"
                            >
                                <x-admin.list.td align="center">
                                    <x-admin.list.checkbox name="ids[]" :value="$project->id"/>
                                </x-admin.list.td>

                                <x-admin.list.td>
                                    <div
                                        class="h-14 w-20 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
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
                                        </x-admin.list.row-actions>
                                    </div>
                                </x-admin.list.td>

                                <x-admin.list.td>
                                    <x-admin.badge color="brand">
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
                                                            class="border-t border-slate-200 pt-3 text-xs text-slate-500 leading-6">
                                                            این بخش فقط برای پیش‌نمایش سریع تغییرات است و بعد از ذخیره،
                                                            داده
                                                            واقعی به‌روزرسانی می‌شود.
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
                            description="هنوز هیچ پروژه‌ای ایجاد نشده است."
                        />
                    @endif
                </x-admin.list.tbody>
            </x-admin.list.table>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminListQuickEdit', (initialOpenId = null) => ({
                openId: initialOpenId,

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
            }));
        });
    </script>
@endpush
