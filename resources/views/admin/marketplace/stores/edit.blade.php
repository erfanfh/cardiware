@extends('admin.layout')

@section('title', 'ویرایش فروشنده')

@section('content')
    <x-admin.form action="{{ route('admin.stores.update', $store) }}" method="put">
        @include('admin.marketplace.stores.partials.form-fields', [
            'store' => $store,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
