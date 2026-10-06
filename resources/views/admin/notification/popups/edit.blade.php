@extends('admin.layout')

@section('title', 'ویرایش پاپ‌آپ')

@section('content')
    <x-admin.form method="put" action="{{ route('admin.popups.update', $popup) }}">
        @include('admin.notification.popups.partials.form-fields', ['popup' => $popup])
        <x-admin.form.save-button label="ذخیره تغییرات"/>
    </x-admin.form>
@endsection
