@extends('admin.layout')

@section('title', 'ویرایش پاداش باشگاه')

@section('content')
    <x-admin.form action="{{ route('admin.reward-offers.update', $rewardOffer) }}" method="put">
        @include('admin.loyalty.rewards.partials.form-fields', ['rewardOffer' => $rewardOffer])
        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
