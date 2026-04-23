@extends('admin.layout')

@section('title', 'لیست دیدگاه‌ها')

@section('content')
    <div class="space-y-4">
        @include('admin.discussion.comments.partials.list-table')

        <x-admin.list.pagination :model="$comments"/>
    </div>
@endsection
