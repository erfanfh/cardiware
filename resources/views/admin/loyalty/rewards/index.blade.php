@extends('admin.layout')

@section('title', 'پاداش‌های باشگاه')

@section('content')
    @include('admin.loyalty.rewards.partials.filters-panel')

    @php($rewardOfferIds = $rewardOffers->pluck('id')->values()->all())

    <div x-data="adminBulkSelection()" class="space-y-4">
        <x-admin.list.bulk-bar
            :action="route('admin.reward-offers.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای عملیات گروهی، ابتدا یک یا چند پاداش را انتخاب کنید."
            selected-text="پاداش انتخاب شده"
        />

        @include('admin.loyalty.rewards.partials.list-table')
        <x-admin.list.pagination :model="$rewardOffers"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
