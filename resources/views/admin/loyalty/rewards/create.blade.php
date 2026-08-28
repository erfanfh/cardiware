@extends('admin.layout')

@section('title', 'ایجاد پاداش باشگاه')

@section('content')
    <x-admin.form action="{{ route('admin.reward-offers.store') }}" method="post">
        @include('admin.loyalty.rewards.partials.form-fields', ['rewardOffer' => null])
        <x-admin.form.save-button label="ایجاد پاداش"/>
    </x-admin.form>
@endsection
