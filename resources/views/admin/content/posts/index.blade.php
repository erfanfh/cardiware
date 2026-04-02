@extends('admin.layout')

@section('title', 'لیست نوشته‌ها')

@section('content')
    @include('admin.content.posts.partials.filters-panel')

    @php
        $postIds = $posts->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.posts.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند نوشته را انتخاب کنید."
            selected-text="نوشته انتخاب شده"
        />

        @include('admin.content.posts.partials.list-table')

        <x-admin.list.pagination :model="$posts"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
