@extends('admin.layout')

@section('title', 'گزارش فروش محصول')

@section('content')
    <livewire:admin.commerce.analytics.product-analytics-page :product="$product"/>
@endsection
