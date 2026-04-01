@extends('admin.layout')

@section('title', 'لیست نقش‌ها')

@section('content')
    @include('admin.identity.roles.partials.filters-panel')

    @php
        $roleIds = $roles->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.roles.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند نقش را انتخاب کن."
            selected-text="نقش انتخاب شده"
        />

        @include('admin.identity.roles.partials.list-table')

        <x-admin.list.pagination :model="$roles"/>
    </div>
@endsection

