@extends('admin.layout')

@section('title', 'ویرایش عضو تیم')

@section('content')
    <x-admin.form
        action="{{ route('admin.team-members.update', $teamMember) }}"
        method="put"
    >
        @include('admin.organization.team-members.partials.form-fields', [
            'teamMember' => $teamMember,
            'selectedPicture' => $selectedPicture,
        ])

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
