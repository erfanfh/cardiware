@extends('admin.layout')

@section('title', 'ایجاد قانون امتیاز جدید')

@section('content')
    <x-admin.form action="{{ route('admin.earning-rules.store') }}" method="post">
        @include('admin.loyalty.earning-rules.partials.form-fields', [
            'earningRule' => null,
        ])

        <x-admin.form.save-button label="ایجاد قانون امتیاز"/>
    </x-admin.form>
@endsection
