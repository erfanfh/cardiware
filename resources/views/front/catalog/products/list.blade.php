@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        <x-front.breadcrumbs />
        @include('front.catalog.products.partials.list.page')
    </main>
@endsection
