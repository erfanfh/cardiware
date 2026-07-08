@extends('admin.layout')

@section('title', 'ویرایش کدتخفیف')

@section('content')
    <x-admin.form action="{{ route('admin.coupons.update', $coupon) }}" method="put">
        @include('admin.promotion.coupons.partials.form-fields', [
            'coupon' => $coupon,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
