@extends('admin.layout')
@section('title', 'مدیریت استوری‌ها')

@section('content')
    @include('admin.story.stories.partials.filters-panel')
    @php($storyIds = $stories->pluck('id')->values()->all())
    <div x-data="adminBulkSelection()" class="space-y-4">
        @if($hasBulkActions)
            <x-admin.list.bulk-bar :action="route('admin.stories.bulk-action')" :options="$bulkActionOptions"
                button-text="اعمال عملیات گروهی" idle-text="برای اجرای عملیات، ابتدا یک یا چند استوری را انتخاب کنید." selected-text="استوری انتخاب شده"/>
        @endif
        @include('admin.story.stories.partials.list-table')
        <x-admin.list.pagination :model="$stories"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
