@extends('admin.layout')
@section('title', 'ایجاد نظرسنجی')
@section('content')
    <x-admin.form action="{{ route('admin.polls.store') }}" method="post">
        @include('admin.poll.polls.partials.form-fields', ['poll' => null])
        <x-admin.form.save-button label="ایجاد نظرسنجی"/>
    </x-admin.form>
@endsection
