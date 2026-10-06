@extends('admin.layout')

@section('title', 'لیست پاپ‌آپ‌ها')

@section('content')
    @include('admin.notification.popups.partials.filters-panel')

    @php($popupIds = $popups->pluck('id')->values()->all())

    <div x-data="adminBulkSelection()" class="space-y-4">
        @if($hasBulkActions)
            <x-admin.list.bulk-bar
                :action="route('admin.popups.bulk-action')"
                :options="$bulkActionOptions"
                button-text="اعمال عملیات گروهی"
                idle-text="برای اجرای عملیات، ابتدا یک یا چند پاپ‌آپ را انتخاب کنید."
                selected-text="پاپ‌آپ انتخاب شده"
            />
        @endif

        @include('admin.notification.popups.partials.list-table')
        <x-admin.list.pagination :model="$popups"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
