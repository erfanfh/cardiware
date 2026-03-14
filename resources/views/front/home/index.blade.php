@extends('front.master')

@php
    $title = get_setting('general', 'title');
    $subTitle = get_setting('general', 'subtitle');
    if($subTitle) $title = "$title | $subTitle";
@endphp

@section('title', $title)

@section('content')
    @include('front.home.partials.story')

    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.home.partials.hero-slider')

        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <aside class="lg:col-span-4 flex flex-col gap-4">
                @include('front.home.partials.vip-box')

                @include('front.home.partials.poll')
            </aside>

            <div class="lg:col-span-8">
                @include('front.home.partials.digital-products')

                @include('front.home.partials.special-posts')
            </div>
        </section>

        <!-- Ad slot (in content) -->
        <section class="mt-8">
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-500 flex items-center justify-between">
                <div class="font-extrabold text-slate-700">جایگاه تبلیغات (داخل محتوا)</div>
                <div class="text-xs">970×250 / 728×90</div>
            </div>
        </section>

        @include('front.home.partials.free-posts')

        @include('front.home.partials.options-box')

        @include('front.home.partials.physical-products')

        @include('front.home.partials.projects')

        @include('front.home.partials.videos')

        @include('front.home.partials.comments')
    </main>
@endsection

@push('styles')
    @vite(['resources/css/front/home.css'])
@endpush

@push('scripts')
    @vite(['resources/js/front/home.js'])
@endpush
