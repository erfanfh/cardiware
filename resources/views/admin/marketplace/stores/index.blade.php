@extends('admin.layout')

@section('title', 'لیست فروشنده‌ها')

@section('content')
    @include('admin.marketplace.stores.partials.filters-panel')

    @php
        $storeIds = $stores->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.stores.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند فروشنده را انتخاب کنید."
            selected-text="فروشنده انتخاب شده"
        />

        @include('admin.marketplace.stores.partials.list-table')

        <x-admin.list.pagination :model="$stores"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
