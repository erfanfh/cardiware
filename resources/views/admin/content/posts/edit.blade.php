@extends('admin.layout')

@section('title', 'ویرایش نوشته')

@section('content')
    <x-admin.form action="{{ route('admin.posts.update', $post) }}" method="put">
        @include('admin.content.posts.partials.form-fields', [
            'post' => $post,
        ])

        <x-admin.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
