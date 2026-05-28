@extends('admin.layout')

@section('title', 'ایجاد محصول جدید')

@section('content')
    <x-admin.form action="{{ route('admin.products.store') }}" method="post">
        @include('admin.catalog.products.partials.form-fields', [
            'product' => null,
        ])

        <x-admin.form.save-button label="انتشار محصول"/>
    </x-admin.form>
@endsection
