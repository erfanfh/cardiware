@extends('front.master')

@section('head')
    <title>سبد خرید | {{ get_setting('general', 'title') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description"
          content="مشاهده و مدیریت محصولات انتخاب‌شده در سبد خرید {{ get_setting('general', 'title') }}">
@endsection

@section('content')
    <livewire:front.commerce.cart.cart-page/>
@endsection
