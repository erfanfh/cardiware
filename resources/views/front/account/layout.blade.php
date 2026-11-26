@extends('front.master')

@section('head')
    <title>Account | {{ get_setting('general', 'title') }}</title>
@endsection

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        @include('front.account.partials.hero')

        <section class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
            @include('front.account.partials.sidebar')

            <div class="min-w-0 lg:col-span-8 xl:col-span-9">
                <x-front.account-announcements/>
                @yield('page-content')
            </div>
        </section>
    </section>
@endsection
