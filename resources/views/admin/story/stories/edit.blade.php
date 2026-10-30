@extends('admin.layout')
@section('title', 'ویرایش استوری')

@section('content')
    <x-admin.form action="{{ route('admin.stories.update', $story) }}" method="put">
        @include('admin.story.stories.partials.form-fields')
        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
