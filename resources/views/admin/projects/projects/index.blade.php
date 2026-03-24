@extends('admin.layout')

@section('title', 'لیست پروژه‌ها')

@section('content')
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
            @forelse($projects as $project)
                <x-admin.list.tr>
                    <x-admin.list.td align="center">
                        <x-admin.list.checkbox name="ids[]" :value="$project->id"/>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="h-14 w-20 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            @if($project->cover_url)
                                <img src="{{ $project->cover_url }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xs text-slate-400">
                                    بدون تصویر
                                </div>
                            @endif
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[260px]">
                            <a href="{{ route('admin.projects.edit', $project) }}"
                               class="line-clamp-1 text-sm font-bold text-slate-800 transition hover:text-brand-600">
                                {{ $project->title }}
                            </a>

                            <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                <span>#{{ $project->id }}</span>
                                <span>•</span>
                                <span>{{ $project->slug }}</span>
                            </div>

                            <x-admin.list.row-actions class="opacity-0 transition group-hover:opacity-100">
                                <a href="{{ route('admin.projects.edit', $project) }}"
                                   class="font-medium text-brand-600 hover:text-brand-700">
                                    ویرایش
                                </a>

                                <span class="text-slate-300">|</span>

                                <button type="button" class="font-medium text-slate-600 hover:text-slate-900">
                                    ویرایش سریع
                                </button>

                                <span class="text-slate-300">|</span>

                                <a href="{{ route('projects.detail', $project) }}" target="_blank"
                                   class="font-medium text-slate-600 hover:text-slate-900">
                                    مشاهده
                                </a>

                                <span class="text-slate-300">|</span>

                                <button type="button" class="font-medium text-rose-600 hover:text-rose-700">
                                    حذف
                                </button>
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
                            @forelse($project->tags ?? [] as $tag)
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                                {{ $tag }}
                            </span>
                            @empty
                                <span class="text-xs text-slate-400">بدون برچسب</span>
                            @endforelse
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td muted>
                        <div class="text-xs leading-6">
                            <div>{{ jalali_date($project->updated_at, 'Y/m/d') }}</div>
                            <div>{{ jalali_date($project->updated_at, 'H:i') }}</div>
                        </div>
                    </x-admin.list.td>
                </x-admin.list.tr>
            @empty
                <x-admin.list.empty
                    colspan="7"
                    title="پروژه‌ای برای نمایش وجود ندارد"
                    description="هنوز هیچ پروژه‌ای ایجاد نشده است."
                />
            @endforelse
        </x-admin.list.tbody>
    </x-admin.list.table>
@endsection
