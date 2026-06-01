@extends('admin.layout')

@section('title', 'لیست محصولات')

@section('content')
    @include('admin.catalog.products.partials.filters-panel')

    @php
        $productIds = $products->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.products.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند محصول را انتخاب کنید."
            selected-text="محصول انتخاب شده"
        />

        @include('admin.catalog.products.partials.list-table')

        <x-admin.list.pagination :model="$products"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
