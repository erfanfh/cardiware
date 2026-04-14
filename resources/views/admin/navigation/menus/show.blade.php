@extends('admin.layout')

@section('title', 'فهرست ها - ' . $navigationMenu->title)

@php
    $navigationManagerConfig = [
        'pickerConfigs' => [
            'category' => [
                'label' => 'جستجوی دسته‌بندی',
                'emptyText' => 'دسته‌بندی‌ای پیدا نشد.',
                'topText' => 'دسته‌بندی‌های پیشنهادی',
                'searchUrl' => route('admin.categories.ajax-search'),
                'topItems' => $topCategories ?? [],
            ],
            'tag' => [
                'label' => 'جستجوی برچسب',
                'emptyText' => 'برچسبی پیدا نشد.',
                'topText' => 'برچسب‌های پیشنهادی',
                'searchUrl' => route('admin.tags.ajax-search'),
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

        'menuItems' => [
            [
                'uid' => 'menu-item-1',
                'type' => 'custom_link',
                'typeLabel' => 'لینک اختصاصی',
                'title' => 'خانه',
                'url' => '/',
                'sourceId' => null,
                'isOpen' => false,
                'isActive' => true,
                'target' => '_self',
                'icon' => 'home-03',
                'badge' => '',
                'cssClass' => '',
                'isMegaMenu' => false,
                'megaMenuTitle' => '',
                'megaMenuColumns' => '4',
                'megaMenuStyle' => 'default',
                'children' => [],
            ],
            [
                'uid' => 'menu-item-2',
                'type' => 'category',
                'typeLabel' => 'Category',
                'title' => 'بازی‌ها',
                'url' => '/categories/games',
                'sourceId' => 10,
                'isOpen' => true,
                'isActive' => true,
                'target' => '_self',
                'icon' => '',
                'badge' => 'محبوب',
                'cssClass' => '',
                'isMegaMenu' => true,
                'megaMenuTitle' => 'دسترسی سریع بازی‌ها',
                'megaMenuColumns' => '4',
                'megaMenuStyle' => 'default',
                'children' => [
                    [
                        'uid' => 'menu-item-3',
                        'type' => 'category',
                        'typeLabel' => 'Category',
                        'title' => 'PES',
                        'url' => '/categories/pes',
                        'sourceId' => 1,
                        'isOpen' => false,
                        'isActive' => true,
                        'target' => '_self',
                        'icon' => '',
                        'badge' => '',
                        'cssClass' => '',
                        'isMegaMenu' => false,
                        'megaMenuTitle' => '',
                        'megaMenuColumns' => '4',
                        'megaMenuStyle' => 'default',
                        'children' => [],
                    ],
                    [
                        'uid' => 'menu-item-4',
                        'type' => 'category',
                        'typeLabel' => 'Category',
                        'title' => 'FIFA',
                        'url' => '/categories/fifa',
                        'sourceId' => 2,
                        'isOpen' => false,
                        'isActive' => true,
                        'target' => '_self',
                        'icon' => '',
                        'badge' => '',
                        'cssClass' => '',
                        'isMegaMenu' => false,
                        'megaMenuTitle' => '',
                        'megaMenuColumns' => '4',
                        'megaMenuStyle' => 'default',
                        'children' => [],
                    ],
                ],
            ],
            [
                'uid' => 'menu-item-5',
                'type' => 'page',
                'typeLabel' => 'Page',
                'title' => 'تماس با ما',
                'url' => '/contact-us',
                'sourceId' => 2,
                'isOpen' => false,
                'isActive' => true,
                'target' => '_self',
                'icon' => '',
                'badge' => '',
                'cssClass' => '',
                'isMegaMenu' => false,
                'megaMenuTitle' => '',
                'megaMenuColumns' => '4',
                'megaMenuStyle' => 'default',
                'children' => [],
            ],
        ],
    ];
@endphp

@section('content')
    <x-admin.form action="{{ route('admin.navigation-menus.update', $navigationMenu) }}" method="put">
        <div x-data='navigationManager(@json($navigationManagerConfig))' x-init="init()" class="space-y-6">
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
