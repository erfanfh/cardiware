@extends('admin.layout')
@section('title', 'ویرایش ویدیو')
@section('content')
    <x-admin.form action="{{ route('admin.videos.update', $video) }}" method="put">
        @include('admin.video.videos.partials.form-fields', ['video' => $video])
        <x-admin.form.save-button label="ذخیره تغییرات"/>
    </x-admin.form>
@endsection
