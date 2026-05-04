@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.posts.partials.detail.title')

        <div data-smart-sidebar-layout class="grid grid-cols-1 gap-6 lg:grid-cols-10 lg:items-start">
            <div data-smart-sidebar-content class="lg:col-span-7 space-y-6">
                @include('front.render-layout', [
                    'rows' => $layoutData['rows'],
                    'gapSize' => 6
                ])
            </div>

            <div data-smart-sidebar-column class="lg:col-span-3">
                <div data-smart-sidebar-box>
                    @include('front.sidebar.index')
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @vite(['resources/js/front/post-detail/index.js'])
@endpush
