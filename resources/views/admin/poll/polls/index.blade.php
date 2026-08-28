@extends('admin.layout')

@section('title', 'لیست نظرسنجی‌ها')

@section('content')
    @include('admin.poll.polls.partials.filters-panel')

    <div class="space-y-4">
        @include('admin.poll.polls.partials.list-table')

        <x-admin.list.pagination :model="$polls"/>
    </div>
@endsection
