@extends('admin.layout')

@section('title', 'ایجاد برگه جدید')

@section('content')
    <x-admin.form action="{{ route('admin.pages.store') }}" method="post">
        @include('admin.content.pages.partials.form-fields', [
            'page' => null,
        ])

        <x-admin.form.save-button label="انتشار برگه"/>
    </x-admin.form>
@endsection
