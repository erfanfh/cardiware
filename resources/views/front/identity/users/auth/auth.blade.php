@extends('front.master')

@section('title', 'ورود / ثبت‌نام')

@section('content')
    <main class="mx-auto max-w-6xl px-4 pt-6 pb-12">
        <section class="mx-auto w-full max-w-xl">
            <livewire:front.identity.user.auth.auth-flow />
        </section>
    </main>
@endsection
