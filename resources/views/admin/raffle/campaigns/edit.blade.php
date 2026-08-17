@extends('admin.layout')
@section('title', 'ویرایش قرعه کشی')
@section('content')
    <x-admin.form action="{{ route('admin.draw-campaigns.update', $drawCampaign) }}" method="put">
        @include('admin.raffle.campaigns.partials.form-fields')
        <x-admin.form.save-button label="ذخیره تغییرات"/>
    </x-admin.form>
@endsection
