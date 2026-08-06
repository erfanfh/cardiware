@extends('admin.layout')

@section('title', 'گزارش فروش فروشنده')

@section('content')
    <livewire:admin.commerce.analytics.store-analytics-page :store="$store"/>
@endsection
