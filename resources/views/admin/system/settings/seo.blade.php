@extends('admin.layout')

@section('title', 'تنظیمات سئو')

@section('content')
    <x-admin.form action="{{ route('admin.settings.seo.store') }}" method="put">
        <x-admin.page>
            <x-admin.tabs
                :tabs="[
                    'main' => 'اصلی',
                    'schema' => 'اسکیما',
                    'home' => 'صفحه اصلی',
                    'post' => 'صفحه نوشته',
                    'page' => 'صفحه برگه',
                    'project' => 'صفحه پروژه',
                    'post-category' => 'صفحه دسته‌بندی نوشته',
                    'post-tag' => 'صفحه برچسب نوشته',
                    'post-list' => 'صفحه لیست نوشته‌ها',
                    'project-list' => 'صفحه لیست پروژه‌ها',
                    'search' => 'صفحه جستجو',
                ]"
                active="{{ request()->input('tab') ?: 'main' }}"
            >
                <x-admin.tabs.panel name="main">
                    @include('admin.system.settings.partials.seo.main')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="schema">
                    @include('admin.system.settings.partials.seo.schema')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="home">
                    @include('admin.system.settings.partials.seo.home')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="post">
                    @include('admin.system.settings.partials.seo.post')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="page">
                    @include('admin.system.settings.partials.seo.page')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="project">
                    @include('admin.system.settings.partials.seo.project')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="post-category">
                    @include('admin.system.settings.partials.seo.post-category')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="post-tag">
                    @include('admin.system.settings.partials.seo.post-tag')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="post-list">
                    @include('admin.system.settings.partials.seo.post-list')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="project-list">
                    @include('admin.system.settings.partials.seo.project-list')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="search">
                    @include('admin.system.settings.partials.seo.search')
                </x-admin.tabs.panel>
            </x-admin.tabs>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
