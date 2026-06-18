@extends('admin.layout')

@section('title', 'لیست سفارش‌ها')

@section('content')
    @include('admin.commerce.orders.partials.filters-panel')

    @php
        $orderIds = $orders->pluck('id')->values()->all();
    @endphp

    <div class="space-y-4">
        @include('admin.commerce.orders.partials.list-table')

        <x-admin.list.pagination :model="$orders"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
