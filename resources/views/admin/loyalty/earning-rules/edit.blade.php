@extends('admin.layout')

@section('title', 'ویرایش قانون امتیاز')

@section('content')
    <x-admin.form action="{{ route('admin.earning-rules.update', $earningRule) }}" method="put">
        @include('admin.loyalty.earning-rules.partials.form-fields', [
            'earningRule' => $earningRule,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
