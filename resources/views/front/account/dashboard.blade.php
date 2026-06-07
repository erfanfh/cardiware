@extends('front.master')

@section('head')
    <title>حساب کاربری | {{ get_setting('general', 'title') }}</title>
@endsection

@section('content')
    <section
        class="mx-auto max-w-7xl px-4 py-8"
        x-data="{
            activeTab: 'dashboard',
            setTab(tab) {
                this.activeTab = tab;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }">
        @include('front.account.partials.hero')

        @include('front.account.partials.mobile-tabs')

        <section class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
            @include('front.account.partials.sidebar')

            @include('front.account.partials.content')
        </section>
    </section>
@endsection
