@extends('admin.layout')

@section('title', 'ایجاد موقعیت شغلی')

@section('content')
    <x-admin.form action="{{ route('admin.career-positions.store') }}" method="post">
        @include('admin.career.positions.partials.form-fields', ['position' => null])
        <x-admin.form.save-button label="ایجاد موقعیت"/>
    </x-admin.form>
@endsection
