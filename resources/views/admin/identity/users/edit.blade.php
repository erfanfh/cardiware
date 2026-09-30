@extends('admin.layout')

@section('title', 'ویرایش کاربر')

@section('content')
    <x-admin.form action="{{ route('admin.users.update', $user) }}" method="put">
        @include('admin.identity.users.partials.form-fields', [
            'user' => $user,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
    @can('wallet.adjust')
        <div class="mt-5">
            <x-admin.form.button tag="a" href="{{ route('admin.users.wallet.show', $user) }}" variant="secondary">
                مدیریت موجودی کیف پول
            </x-admin.form.button>
        </div>
    @endcan
@endsection
