@extends('admin.layout')

@section('title', 'ایجاد کاربر جدید')

@section('content')
    <x-admin.form action="{{ route('admin.users.store') }}" method="post">
        @include('admin.identity.users.partials.form-fields', [
            'user' => null,
        ])

        <x-admin.form.save-button label="ایجاد کاربر"/>
    </x-admin.form>
@endsection

