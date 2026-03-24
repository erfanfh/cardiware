@extends('admin.layout')

@section('title', 'ایجاد پروژه جدید')

@section('content')
    <x-admin.form action="{{ route('admin.projects.store') }}" method="post">
        @include('admin.projects.projects.partials.form-fields', [
            'project' => null,
            'roadmapItems' => [],
            'changelogs' => [],
        ])

        <x-admin.save-button label="انتشار پروژه"/>
    </x-admin.form>
@endsection
