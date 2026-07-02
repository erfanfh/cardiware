@extends('admin.layout')

@section('title', 'ایجاد کدتخفیف جدید')

@section('content')
    <x-admin.form action="{{ route('admin.coupons.store') }}" method="post">
        @include('admin.promotion.coupons.partials.form-fields', [
            'coupon' => null,
        ])

        <x-admin.form.save-button label="ایجاد کدتخفیف"/>
    </x-admin.form>
@endsection

