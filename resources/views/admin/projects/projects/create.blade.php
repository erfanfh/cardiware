@extends('admin.layout')

@section('title', 'ایجاد پروژه جدید')

@section('content')
    <form method="POST" action="{{ route('admin.projects.store') }}">
        @csrf
        @include('admin.projects.projects.form', [
            'project' => null,
            'roadmapItems' => [],
            'changelogs' => [],
        ])
    </form>
@endsection
