@extends('admin.layout')

@section('title', 'لیست پروژه‌ها')

@section('content')
    @include('admin.projects.projects.partials.filters-panel')

    @php
        $projectIds = $projects->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.projects.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند پروژه را انتخاب کن."
            selected-text="پروژه انتخاب شده"
        />

        @include('admin.projects.projects.partials.list-table')

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
@endsection

@push('scripts')
    @vite(['resources/js/admin/project-index.js'])
@endpush
