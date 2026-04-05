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
                    'products' => 'محصولات فیزیکی',
                    'videos' => 'ویدیوهای آموزشی',
                    'testimonials' => 'تجربه کاربران',
                    'posts' => 'مطالب',
                    'shortcuts' => 'میانبرها',
                    'projects' => 'پروژه‌ها',
                ]"
                active="slider"
            >
                <x-admin.tabs.panel name="slider">
                    <div class="space-y-6">
                        <x-admin.metabox title="اسلایدر صفحه اصلی" :collapsible="false">
                            <div class="space-y-4">
                                <div class="text-xs text-slate-500">
                                    آیتم‌های اسلایدر اصلی صفحه را از این بخش مدیریت کنید.
                                </div>

                                @include('admin.system.settings.partials.home-slider')
                            </div>
                        </x-admin.metabox>
                    </div>
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
            </x-admin.tabs>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
