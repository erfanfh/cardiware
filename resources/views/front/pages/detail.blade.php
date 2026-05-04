@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.pages.partials.detail.title')

        <section data-smart-sidebar-layout class="grid grid-cols-1 gap-6 lg:grid-cols-10 lg:items-start">
            <div data-smart-sidebar-content class="lg:col-span-7 space-y-6">
                @include('front.pages.partials.detail.content')
            </div>

            <div data-smart-sidebar-column class="lg:col-span-3">
                <div data-smart-sidebar-box>
                    @include('front.sidebar.index')
                </div>
            </div>
        </section>
    </main>
@endsection
