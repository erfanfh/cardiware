@extends('admin.layout')

@section('title', 'ویرایش نقش')

@section('content')
    <x-admin.form action="{{ route('admin.roles.update', $role) }}" method="put">
        @include('admin.identity.roles.partials.form-fields', [
            'role' => $role,
            'selectedPermissions' => $role->permissions()
            ->get(['id', 'title'])
            ->map(fn($i) => [
                    'value' => (string)$i->id,
                    'label' => $i->title,
            ])
            ->values()
            ->all(),
        ])

        <x-admin.save-button label="ایجاد نقش"/>
    </x-admin.form>
@endsection
