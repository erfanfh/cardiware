@extends('admin.layout')
@section('title', 'ایجاد قرعه کشی')
@section('content')
    <x-admin.form action="{{ route('admin.draw-campaigns.store') }}" method="post">
        @include('admin.raffle.campaigns.partials.form-fields', ['drawCampaign' => null])
        <x-admin.form.save-button label="ایجاد قرعه کشی"/>
    </x-admin.form>
@endsection
