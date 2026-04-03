@extends('admin.layout')

@section('title', 'ویرایش کاربر')

@section('content')
    <x-admin.form action="{{ route('admin.users.update', $user) }}" method="put">
        @include('admin.identity.users.partials.form-fields', [
            'user' => $user,
        ])

        <x-admin.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
