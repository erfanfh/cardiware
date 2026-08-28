@extends('admin.layout')
@section('title', 'ویرایش قرعه کشی')
@section('content')
    @include('admin.raffle.campaigns.partials.nav')
    @include('admin.raffle.campaigns.partials.management-overview')
    @include('admin.raffle.campaigns.partials.operational-health')
    @include('admin.raffle.campaigns.partials.ticket-acquisition')
    @include('admin.raffle.campaigns.partials.audit-log')

    <x-admin.form action="{{ route('admin.draw-campaigns.update', $drawCampaign) }}" method="put">
        @include('admin.raffle.campaigns.partials.form-fields')
        <x-admin.form.save-button label="ذخیره تغییرات"/>
    </x-admin.form>
@endsection
