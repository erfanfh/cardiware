@extends('admin.layout')

@section('title', 'ویرایش پلن VIP')

@section('content')
    <x-admin.form action="{{ route('admin.vip-plans.update', $plan) }}" method="put">
        @include('admin.vip.plans.partials.form-fields', ['plan' => $plan])
        <x-admin.form.save-button label="ذخیره تغییرات"/>
    </x-admin.form>
@endsection
