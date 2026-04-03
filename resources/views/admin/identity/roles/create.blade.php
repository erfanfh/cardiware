@extends('admin.layout')

@section('title', 'ایجاد نقش جدید')

@section('content')
    <x-admin.form action="{{ route('admin.roles.store') }}" method="post">
        @include('admin.identity.roles.partials.form-fields', [
            'role' => null,
            'selectedPermissions' => [],
        ])

        <x-admin.save-button label="ایجاد نقش"/>
    </x-admin.form>
@endsection
