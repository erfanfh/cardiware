@extends('admin.layout')
@section('title', 'ایجاد استوری')

@section('content')
    <x-admin.form action="{{ route('admin.stories.store') }}" method="post">
        @include('admin.story.stories.partials.form-fields')
        <x-admin.form.save-button label="ایجاد استوری"/>
    </x-admin.form>
@endsection
