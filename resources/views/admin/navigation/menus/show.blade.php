@extends('admin.layout')

@section('title', 'فهرست ها - ' . $navigationMenu->title)

@php
    $navigationManagerConfig = [
        'pickerConfigs' => [
            'category' => [
                'label' => 'جستجوی دسته‌بندی',
                'emptyText' => 'دسته‌بندی‌ای پیدا نشد.',
                'topText' => 'دسته‌بندی‌های پیشنهادی',
                'searchUrl' => route('admin.posts.categories.ajax-search'),
                'topItems' => $topCategories ?? [],
            ],
            'tag' => [
                'label' => 'جستجوی برچسب',
                'emptyText' => 'برچسبی پیدا نشد.',
                'topText' => 'برچسب‌های پیشنهادی',
                'searchUrl' => route('admin.posts.tags.ajax-search'),
                'topItems' => $topTags ?? [],
            ],
            'post' => [
                'label' => 'جستجوی نوشته',
                'emptyText' => 'نوشته‌ای پیدا نشد.',
                'topText' => 'نوشته‌های پیشنهادی',
                'searchUrl' => route('admin.posts.ajax-search'),
                'topItems' => $topPosts ?? [],
            ],
            'page' => [
                'label' => 'جستجوی برگه',
                'emptyText' => 'برگه‌ای پیدا نشد.',
                'topText' => 'برگه‌های پیشنهادی',
                'searchUrl' => route('admin.pages.ajax-search'),
                'topItems' => $topPages ?? [],
            ],
            'project' => [
                'label' => 'جستجوی پروژه',
                'emptyText' => 'پروژه‌ای پیدا نشد.',
                'topText' => 'پروژه‌های پیشنهادی',
                'searchUrl' => route('admin.projects.ajax-search'),
                'topItems' => $topProjects ?? [],
            ],
        ],

        'menuItems' => $menuItems ?? [],
    ];
@endphp

@section('content')
    <x-admin.form action="{{ route('admin.navigation-menus.update', $navigationMenu) }}" method="put">
        <div x-data='navigationManager(@json($navigationManagerConfig))' x-init="init()" class="space-y-6">
            <input type="hidden" name="items_payload" :value="serializedItemsPayload()">

            <x-admin.page :columns="2" :sidebarSpan="3" sidebarPosition="right">
                <x-slot:sidebar>
                    @include('admin.navigation.menus.partials.add-item-box')
                </x-slot:sidebar>

                @include('admin.navigation.menus.partials.items-box')
            </x-admin.page>
        </div>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection

@push('styles')
    @vite(['resources/css/admin/navigation-menu.css'])
@endpush

@push('scripts')
    @vite(['resources/js/admin/navigation-menu.js'])
@endpush
