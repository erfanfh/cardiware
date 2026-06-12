@extends('admin.layout')

@section('title', 'ایجاد فروشنده جدید')

@section('content')
    <x-admin.form action="{{ route('admin.stores.store') }}" method="post">
        @include('admin.marketplace.stores.partials.form-fields', [
            'store' => null,
        ])

        <x-admin.form.save-button label="ذخیره فروشنده"/>
    </x-admin.form>
@endsection
