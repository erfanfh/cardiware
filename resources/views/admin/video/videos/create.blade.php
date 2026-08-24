@extends('admin.layout')
@section('title', 'افزودن ویدیو')
@section('content')
    <x-admin.form action="{{ route('admin.videos.store') }}" method="post">
        @include('admin.video.videos.partials.form-fields', ['video' => null])
        <x-admin.form.save-button label="ایجاد ویدیو"/>
    </x-admin.form>
@endsection
