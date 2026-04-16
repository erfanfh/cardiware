@extends('admin.layout')

@section('title', 'تنظیمات صفحه اصلی')

@section('content')
    <x-admin.form action="{{ route('admin.settings.home.store') }}" method="put">
        <x-admin.page>
            <x-admin.tabs
                :tabs="[
                    'slider' => 'اسلایدر',
                    'download-products' => 'محصولات دانلودی',
                    'special-posts' => 'نوشته های ویژه',
                    'subscription' => 'اشتراک ویژه',
                    'posts' => 'نوشته های رایگان',
                    'services' => 'سرویس ها',
                    'products' => 'محصولات فیزیکی',
                    'projects' => 'پروژه‌ها',
                    'videos' => 'ویدیوهای آموزشی',
                    'testimonials' => 'تجربه کاربران',
                    'ads' => 'تبلیغات',
                ]"
                active="{{ request()->input('tab') ?: 'slider' }}"
            >
                <x-admin.tabs.panel name="slider">
                    @include('admin.system.settings.partials.home-slider')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="download-products">
                    @include('admin.system.settings.partials.home-download-products')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="special-posts">
                    @include('admin.system.settings.partials.home-special-posts')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="subscription">
                    @include('admin.system.settings.partials.home-subscription')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="products">
                    @include('admin.system.settings.partials.home-products')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="videos">
                    @include('admin.system.settings.partials.home-videos')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="testimonials">
                    @include('admin.system.settings.partials.home-testimonials')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="projects">
                    @include('admin.system.settings.partials.home-projects')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="posts">
                    @include('admin.system.settings.partials.home-posts')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="ads">
                    @include('admin.system.settings.partials.home-ads')
                </x-admin.tabs.panel>
            </x-admin.tabs>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
