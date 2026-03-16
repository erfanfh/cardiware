@extends('admin.layout')

@section('title', 'مدیریت چیدمان ')

@section('content')
    @php
        $layoutRows = [
            [
                'key' => 'row_top',
                'type' => 'single',
                'areas' => [
                    [
                        'key' => 'top_full',
                        'title' => 'باکس تمام عرض بالا',
                        'description' => 'مناسب برای اسلایدرها و المنت‌های اصلی صفحه',
                        'span' => 'xl:col-span-12',
                        'elements' => [
                            [
                                'key' => 'hero_slider',
                                'title' => 'اسلایدر اصلی',
                                'type' => 'اسلایدر',
                                'type_color' => 'blue',
                                'is_active' => true,
                            ],
                            [
                                'key' => 'special_banner',
                                'title' => 'بنر ویژه',
                                'type' => 'بنر',
                                'type_color' => 'emerald',
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ],

            [
                'key' => 'row_middle',
                'type' => 'split',
                'areas' => [
                    [
                        'key' => 'middle_main',
                        'title' => 'باکس میانی',
                        'description' => 'ناحیه اصلی محتوای میانی صفحه',
                        'span' => 'xl:col-span-8',
                        'elements' => [
                            [
                                'key' => 'latest_posts',
                                'title' => 'آخرین مقالات',
                                'type' => 'لیست مقالات',
                                'type_color' => 'violet',
                                'is_active' => true,
                            ],
                            [
                                'key' => 'featured_products',
                                'title' => 'محصولات منتخب',
                                'type' => 'لیست محصولات',
                                'type_color' => 'amber',
                                'is_active' => false,
                            ],
                        ],
                    ],
                    [
                        'key' => 'middle_sidebar',
                        'title' => 'سایدبار میانی',
                        'description' => 'مناسب برای باکس‌های کمکی و مکمل',
                        'span' => 'xl:col-span-4',
                        'elements' => [
                            [
                                'key' => 'sidebar_ad',
                                'title' => 'تبلیغ سایدبار',
                                'type' => 'تبلیغات',
                                'type_color' => 'rose',
                                'is_active' => true,
                            ],
                            [
                                'key' => 'newsletter',
                                'title' => 'خبرنامه',
                                'type' => 'فرم',
                                'type_color' => 'cyan',
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ],

            [
                'key' => 'row_bottom',
                'type' => 'single',
                'areas' => [
                    [
                        'key' => 'bottom_full',
                        'title' => 'باکس تمام عرض پایین',
                        'description' => 'مناسب برای سکشن‌های پایانی صفحه',
                        'span' => 'xl:col-span-12',
                        'elements' => [],
                    ],
                ],
            ],
        ];
    @endphp

    <input type="hidden" name="layout_state" data-layout-state-input>

    <div class="space-y-6">
        @foreach($layoutRows as $row)
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 items-stretch">
                @foreach($row['areas'] as $area)
                    <x-admin.layout.area-box
                        :areaKey="$area['key']"
                        :title="$area['title']"
                        :description="$area['description']"
                        :count="count($area['elements'])"
                        :empty="empty($area['elements'])"
                        :span="$area['span']"
                    >
                        @foreach($area['elements'] as $element)
                            <x-admin.layout.element-card
                                :elementKey="$element['key']"
                                :title="$element['title']"
                                :type="$element['type']"
                                :typeColor="$element['type_color']"
                                :active="$element['is_active']"
                                :inputName="'elements[' . $element['key'] . '][is_active]'"
                            />
                        @endforeach
                    </x-admin.layout.area-box>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/page-layout.js'])

    <style>
        .layout-sortable-ghost {
            opacity: 0.45;
        }

        .layout-sortable-chosen {
            transform: rotate(1deg);
        }

        .layout-sortable-drag {
            cursor: grabbing !important;
        }

        .layout-is-sorting [data-layout-area] {
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .layout-is-sorting [data-layout-area] {
            border-color: rgb(203 213 225);
            background-color: rgb(248 250 252 / 0.95);
        }
    </style>
@endpush
