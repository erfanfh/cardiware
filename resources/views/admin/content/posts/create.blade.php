@extends('admin.layout')

@section('title', 'ایجاد نوشته جدید')

@section('content')
    <x-admin.form action="{{ route('admin.posts.store') }}" method="post">
        @include('admin.content.posts.partials.form-fields', [
            'post' => null,
        ])

        <x-admin.save-button label="انتشار نوشته"/>
    </x-admin.form>
@endsection
