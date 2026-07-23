@extends('admin.layout')

@section('title', 'ایجاد عضو تیم جدید')

@section('content')
    <x-admin.form
        action="{{ route('admin.team-members.store') }}"
        method="post"
    >
        @include('admin.organization.team-members.partials.form-fields', [
            'teamMember' => null,
            'selectedPicture' => [],
        ])

        <x-admin.form.save-button label="ایجاد عضو تیم"/>
    </x-admin.form>
@endsection
