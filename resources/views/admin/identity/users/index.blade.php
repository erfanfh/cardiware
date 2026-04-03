@extends('admin.layout')

@section('title', 'لیست کاربران')

@section('content')
    @include('admin.identity.users.partials.filters-panel')

    @php
        $userIds = $users->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.users.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند کاربر را انتخاب کن."
            selected-text="کاربر انتخاب شده"
        />

        @include('admin.identity.users.partials.list-table')

        <x-admin.list.pagination :model="$users"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
