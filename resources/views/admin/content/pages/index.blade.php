@extends('admin.layout')

@section('title', 'لیست برگه ها')

@section('content')
    @include('admin.content.pages.partials.filters-panel')

    @php
        $pageIds = $pages->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.pages.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند برگه را انتخاب کنید."
            selected-text="برگه انتخاب شده"
        />

        @include('admin.content.pages.partials.list-table')

        <x-admin.list.pagination :model="$pages"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
