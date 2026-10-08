@extends('admin.layout')

@section('title', 'ویرایش موقعیت شغلی')

@section('content')
    <x-admin.form action="{{ route('admin.career-positions.update', $position) }}" method="put">
        @include('admin.career.positions.partials.form-fields')
        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
