@extends('admin.layout')

@section('title', 'لیست ویدیوها')

@section('content')
    @include('admin.video.videos.partials.filters-panel')

    @php
        $videoIds = $videos->pluck('id')->values()->all();
    @endphp

    <div x-data="adminBulkSelection()" class="space-y-4">
        <x-admin.list.bulk-bar
            :action="route('admin.videos.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند ویدیو را انتخاب کنید."
            selected-text="ویدیو انتخاب شده"
        />

        @include('admin.video.videos.partials.list-table')

        <x-admin.list.pagination :model="$videos"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
