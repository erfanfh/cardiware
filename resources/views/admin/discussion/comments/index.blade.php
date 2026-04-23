@extends('admin.layout')

@section('title', 'لیست دیدگاه‌ها')

@section('content')
    @php
        $commentIds = $comments->pluck('id')->values()->all();
    @endphp

    <div
            x-data="adminBulkSelection()"
            class="space-y-4"
    >
        <x-admin.list.bulk-bar
                :action="route('admin.comments.bulk-action')"
                :options="$bulkActionOptions"
                button-text="اعمال عملیات گروهی"
                idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند دیدگاه را انتخاب کنید."
                selected-text="دیدگاه انتخاب شده"
        />

        @include('admin.discussion.comments.partials.list-table')

        <x-admin.list.pagination :model="$comments"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
