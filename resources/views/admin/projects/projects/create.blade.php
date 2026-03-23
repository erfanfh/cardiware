@extends('admin.layout')

@section('title', 'ایجاد پروژه جدید')

@section('content')
    <x-admin.form action="{{ route('admin.projects.store') }}" method="post">
        @include('admin.projects.projects.form', [
            'project' => null,
            'roadmapItems' => [],
            'changelogs' => [],
        ])
    </x-admin.form>
@endsection
