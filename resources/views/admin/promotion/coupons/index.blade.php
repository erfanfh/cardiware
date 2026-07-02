@extends('admin.layout')

@section('title', 'لیست کدهای تخفیف')

@section('content')
    @include('admin.promotion.coupons.partials.filters-panel')

    @php
        $couponIds = $coupons->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.coupons.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند کدتخفیف را انتخاب کنید."
            selected-text="کدتخفیف انتخاب شده"
        />

        @include('admin.promotion.coupons.partials.list-table')

        <x-admin.list.pagination :model="$coupons"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
