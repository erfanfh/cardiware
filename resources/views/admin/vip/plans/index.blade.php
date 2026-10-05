@extends('admin.layout')

@section('title', 'لیست پلن‌های VIP')

@section('content')
    @include('admin.vip.plans.partials.filters-panel')

    @php($planIds = $plans->pluck('id')->values()->all())

    <div x-data="adminBulkSelection()" class="space-y-4">
        @if($hasBulkActions)
            <x-admin.list.bulk-bar
                :action="route('admin.vip-plans.bulk-action')"
                :options="$bulkActionOptions"
                button-text="اعمال عملیات گروهی"
                idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند پلن را انتخاب کنید."
                selected-text="پلن انتخاب شده"
            />
        @endif

        @include('admin.vip.plans.partials.list-table')

        <x-admin.list.pagination :model="$plans"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
