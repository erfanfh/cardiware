@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.pages.partials.detail.title')

        <section class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div class="lg:col-span-7 space-y-6">
                @include('front.pages.partials.detail.content')
            </div>

            <div class="lg:col-span-3">
                @include('front.sidebar.index')
            </div>
        </section>
    </main>
@endsection
