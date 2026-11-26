{{--
    صفحه لندینگ محصول — بخش‌ها به ترتیب از config رندر می‌شوند.
    هر بخش = یک partial در front.landings.partials.{type}
    (ساختار موتور عیناً همان است که به پروژه اصلی Cardiware منتقل می‌شود)
--}}
@extends('front.landings.master')

@section('body')
    @include('front.landings.partials.navbar')

    <main>
        @foreach($data['sections'] as $section)
            @includeIf('front.landings.partials.'.$section['type'], ['section' => $section])
        @endforeach
    </main>

    @include('front.landings.partials.footer')
@endsection
