@extends('admin.layout')

@section('title', 'پاپ‌آپ جدید')

@section('content')
    <x-admin.form method="post" action="{{ route('admin.popups.store') }}">
        @include('admin.notification.popups.partials.form-fields', ['popup' => null])
        <x-admin.form.save-button label="ایجاد پاپ‌آپ"/>
    </x-admin.form>
@endsection
