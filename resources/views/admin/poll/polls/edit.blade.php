@extends('admin.layout')
@section('title', 'ویرایش نظرسنجی')
@section('content')
    <x-admin.form action="{{ route('admin.polls.update', $poll) }}" method="put">
        @include('admin.poll.polls.partials.form-fields', ['poll' => $poll])
        <x-admin.form.save-button label="ذخیره تغییرات"/>
    </x-admin.form>
@endsection
