@extends('admin.layout')

@section('title', 'اطلاعیه‌های حساب کاربری')

@section('content')
    @include('admin.notification.account-announcements.partials.filters-panel')

    <div class="space-y-4">
        @include('admin.notification.account-announcements.partials.list-table')

        <x-admin.list.pagination :model="$announcements"/>
    </div>
@endsection
