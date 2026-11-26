{{--
    Product landing page: sections rendered in order from config are rendered.
    Each section = One partial in front.landings.partials.{type}
    (The engine structure is exactly the same as the main project Cardiware is transferred)
--}}
@extends('front.landings.master')
@php($locale = 'en')

@section('body')
    @include('front.landings.partials.navbar')

    <main>
        @foreach($data['sections'] as $section)
            @includeIf('front.landings.partials.'.$section['type'], ['section' => $section])
        @endforeach
    </main>

    @include('front.landings.partials.footer')
@endsection
