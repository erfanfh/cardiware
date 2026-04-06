@extends('admin.layout')

@section('title', 'ویرایش برگه')

@section('content')
    <x-admin.form action="{{ route('admin.pages.update', $page) }}" method="put">
        @include('admin.content.pages.partials.form-fields', [
            'page' => $page,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
