@extends('admin.layout')

@section('title', 'ویرایش محصول')

@section('content')
    <x-admin.form action="{{ route('admin.products.update', $product) }}" method="put">
        @include('admin.catalog.products.partials.form-fields', [
            'product' => $product,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
