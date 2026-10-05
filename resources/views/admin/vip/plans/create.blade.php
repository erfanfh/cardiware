@extends('admin.layout')

@section('title', 'ایجاد پلن VIP')

@section('content')
    <x-admin.form action="{{ route('admin.vip-plans.store') }}" method="post">
        @include('admin.vip.plans.partials.form-fields', ['plan' => null])
        <x-admin.form.save-button label="ایجاد پلن"/>
    </x-admin.form>
@endsection
