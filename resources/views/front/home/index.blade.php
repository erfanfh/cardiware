@extends('front.master')

@php
    $title = get_setting('general', 'title');
    $subTitle = get_setting('general', 'subtitle');
    if($subTitle) $title = "$title | $subTitle";
@endphp

@section('title', $title)

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8 flex flex-col gap-8">
        @include('front.home.render-layout', [
            'rows' => $layoutData['rows'],
        ])
    </main>
@endsection

@push('styles')
    @vite(['resources/css/front/home.css'])
@endpush

@push('scripts')
    @vite(['resources/js/front/home/index.js'])
@endpush
