@extends('admin.layout')

@section('title', 'لیست پروژه‌ها')

@section('content')
    <div x-data="adminListQuickEdit()">
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
                        <x-admin.list.tr ::class="{ 'bg-brand-50/40': isOpen({{ $project->id }}) }">
                            <x-admin.list.td align="center">
                                <x-admin.list.checkbox name="ids[]" :value="$project->id"/>
                            </x-admin.list.td>

                            <x-admin.list.td>
                                <div class="h-14 w-20 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                    @if($project->cover_url)
                                        <img src="{{ $project->cover_url }}" alt="" class="h-full w-full object-cover">
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
                            <form
                                method="POST"
                                action="{{ route('admin.projects.quick-update', $project) }}"
                                class="space-y-5"
                            >
                                @csrf
                                @method('PATCH')

                                <div
                                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-800">
                                            ویرایش سریع پروژه
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500">
                                            تغییرات اصلی پروژه را بدون ورود به صفحه ویرایش انجام بده.
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
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
                                                :options="$statuses"
                                                :value="old('status', $project->status?->value)"
                                            />
                                        </x-admin.field>
                                    </div>

                                    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
                                        <x-admin.field label="درصد پیشرفت">
                                            <div class="space-y-2">
                                                <x-admin.form.input
                                                    type="number"
                                                    name="progress_percentage"
                                                    min="0"
                                                    max="100"
                                                    :value="old('progress_percentage', $project->progress_percentage ?? 0)"
                                                />

                                                <p class="text-xs text-slate-500">
                                                    مقداری بین 0 تا 100 وارد کن.
                                                </p>
                                            </div>
                                        </x-admin.field>
                                    </div>

                                    <div class="col-span-12">
                                        <x-admin.field label="برچسب‌ها">
                                            <x-admin.form.input
                                                name="tags"
                                                :value="old('tags', is_array($project->tags ?? null) ? implode('، ', $project->tags) : ($project->tags ?? ''))"
                                                placeholder="مثلاً: لاراول، Livewire، Tailwind"
                                            />
                                        </x-admin.field>
                                    </div>
                                </div>
                            </form>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminListQuickEdit', () => ({
                openId: null,

                isOpen(id) {
                    return this.openId === id;
                },

                toggle(id) {
                    this.openId = this.openId === id ? null : id;
                },

                close() {
                    this.openId = null;
                },
            }));
        });
    </script>
@endpush
