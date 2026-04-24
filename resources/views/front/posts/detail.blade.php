@extends('front.master')
@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.posts.partials.detail.title')

        <div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div class="lg:col-span-7 space-y-6">
                @include('front.render-layout', [
                    'rows' => $layoutData['rows'],
                    'gapSize' => 6
                ])
            </div>
            <div class="lg:col-span-3 space-y-6">
                @include('front.sidebar.index')
            </div>
        </div>
    </main>
@endsection
@push('scripts')
    @vite(['resources/js/front/post-detail/index.js'])
@endpush
