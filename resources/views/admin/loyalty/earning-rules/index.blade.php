@extends('admin.layout')

@section('title', 'قوانین کسب امتیاز')

@section('content')
    @include('admin.loyalty.earning-rules.partials.filters-panel')

    @php
        $earningRuleIds = $earningRules->pluck('id')->values()->all();
    @endphp

    <div x-data="adminBulkSelection()" class="space-y-4">
        <x-admin.list.bulk-bar
            :action="route('admin.earning-rules.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند قانون امتیاز را انتخاب کنید."
            selected-text="قانون امتیاز انتخاب شده"
        />

        @include('admin.loyalty.earning-rules.partials.list-table')

        <x-admin.list.pagination :model="$earningRules"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
