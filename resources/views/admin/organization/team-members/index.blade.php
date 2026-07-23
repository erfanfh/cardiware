@extends('admin.layout')

@section('title', 'لیست اعضای تیم')

@section('content')
    @include(
        'admin.organization.team-members.partials.filters-panel'
    )

    @php
        $teamMemberIds = $teamMembers
            ->pluck('id')
            ->values()
            ->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.team-members.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند عضو تیم را انتخاب کن."
            selected-text="عضو تیم انتخاب شده"
        />

        @include(
            'admin.organization.team-members.partials.list-table'
        )

        <x-admin.list.pagination :model="$teamMembers"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
