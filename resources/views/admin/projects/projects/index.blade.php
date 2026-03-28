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

        <x-admin.list.pagination :model="$projects"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/project-index.js'])
@endpush
