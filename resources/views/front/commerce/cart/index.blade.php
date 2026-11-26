@extends('front.master')

@section('head')
    <title>Shopping cart | {{ get_setting('general', 'title') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description"
          content="View and manage selected products in your cart {{ get_setting('general', 'title') }}">
@endsection

@section('content')
    @foreach(['success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200', 'error' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200'] as $flashKey => $flashClass)
        @if(session()->has($flashKey))
            <div class="mx-auto max-w-7xl px-3 pt-4 sm:px-4 lg:px-8">
                <div role="{{ $flashKey === 'error' ? 'alert' : 'status' }}" class="rounded-2xl border px-4 py-3 text-sm font-semibold {{ $flashClass }}">
                    {{ session($flashKey) }}
                </div>
            </div>
        @endif
    @endforeach
    <livewire:front.commerce.cart.cart-page/>
@endsection
