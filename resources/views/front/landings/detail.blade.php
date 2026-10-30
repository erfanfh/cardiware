{{--
    صفحه جزئیات محصول — /{landing}/{page}
    نوع‌های صفحه: leagues | stadiums | trophies | faces
--}}
@extends('front.landings.master')

@section('body')
    @include('front.landings.partials.navbar')

    <main class="pt-16">
        @include('front.landings.partials.detail.hero')

        {{-- محتوای اختصاصی هر نوع صفحه --}}
        <div class="section-pad !pt-4">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_320px] lg:px-8">
                <div class="min-w-0 space-y-16">
                    @includeIf('front.landings.partials.detail.type-'.$pageSlug)
                </div>

                {{-- سایدبار چسبان --}}
                <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                    @include('front.landings.partials.detail.sidebar')
                </aside>
            </div>
        </div>

        {{-- ببینید همچنین --}}
        @include('front.landings.partials.detail.see-also')
    </main>

    @include('front.landings.partials.footer')
@endsection
