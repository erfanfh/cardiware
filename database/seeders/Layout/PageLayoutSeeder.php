<?php

namespace Database\Seeders\Layout;

use App\Domain\Layout\Models\PageLayout;
use App\Domain\Layout\Models\PageLayoutArea;
use App\Domain\Layout\Models\PageLayoutElement;
use App\Domain\Layout\Models\PageLayoutRow;
use Illuminate\Database\Seeder;

class PageLayoutSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        foreach ($this->layouts() as $layoutData) {
            $layout = PageLayout::query()->firstOrNew([
                'key' => $layoutData['key'],
            ]);

            $layout->title = $layoutData['title'];
            $layout->save();

            $this->syncRows($layout, $layoutData['rows']);
        }
    }

    /**
     * @return array
     */
    private function layouts(): array
    {
        return [
            [
                'key' => 'home',
                'title' => 'چیدمان صفحه اصلی',
                'rows' => [
                    [
                        'key' => 'header',
                        'layout_type' => 'single',
                        'sort_order' => 1,
                        'areas' => [
                            [
                                'key' => 'header_primary',
                                'title' => 'باکس تمام عرض بالا',
                                'description' => 'ناحیه اصلی بالای صفحه',
                                'width' => 12,
                                'sort_order' => 1,
                                'elements' => [
                                    [
                                        'key' => 'story',
                                        'type' => 'story',
                                        'title' => 'استوری‌ها',
                                        'description' => 'نمایش استوری‌ها',
                                        'is_active' => true,
                                        'sort_order' => 1,
                                    ],
                                    [
                                        'key' => 'hero-slider',
                                        'type' => 'hero_slider',
                                        'title' => 'اسلایدر اصلی',
                                        'description' => 'نمایش اسلایدر اصلی',
                                        'is_active' => true,
                                        'sort_order' => 2,
                                    ],
                                    [
                                        'key' => 'advertisement_1',
                                        'type' => 'advertisement',
                                        'title' => 'جایگاه تبلیغات 1',
                                        'description' => 'جایگاه اول تبلیغات',
                                        'is_active' => false,
                                        'sort_order' => 3,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'key' => 'content',
                        'layout_type' => 'split',
                        'sort_order' => 2,
                        'areas' => [
                            [
                                'key' => 'content_main',
                                'title' => 'باکس میانی',
                                'description' => 'ناحیه اصلی محتوای میانی',
                                'width' => 8,
                                'sort_order' => 1,
                                'elements' => [
                                    [
                                        'key' => 'digital-products',
                                        'type' => 'digital_products',
                                        'title' => 'باکس محصولات دانلودی',
                                        'description' => 'نمایش محصولات دانلودی',
                                        'is_active' => true,
                                        'sort_order' => 1,
                                    ],
                                    [
                                        'key' => 'special-posts',
                                        'type' => 'special_posts',
                                        'title' => 'مطالب ویژه',
                                        'description' => 'نمایش مطالب ویژه',
                                        'is_active' => true,
                                        'sort_order' => 2,
                                    ],
                                ],
                            ],
                            [
                                'key' => 'content_sidebar',
                                'title' => 'سایدبار میانی',
                                'description' => 'ناحیه کناری محتوای میانی',
                                'width' => 4,
                                'sort_order' => 2,
                                'elements' => [
                                    [
                                        'key' => 'vip-button',
                                        'type' => 'vip_button',
                                        'title' => 'دکمه اشتراک ویژه',
                                        'description' => 'نمایش دکمه اشتراک ویژه',
                                        'is_active' => true,
                                        'sort_order' => 1,
                                    ],
                                    [
                                        'key' => 'poll',
                                        'type' => 'poll',
                                        'title' => 'نظر سنجی',
                                        'description' => 'نمایش نظر سنجی',
                                        'is_active' => true,
                                        'sort_order' => 2,
                                    ],
                                    [
                                        'key' => 'advertisement_2',
                                        'type' => 'advertisement',
                                        'title' => 'جایگاه تبلیغات 2',
                                        'description' => 'جایگاه دوم تبلیغات',
                                        'is_active' => false,
                                        'sort_order' => 3,
                                    ],
                                    [
                                        'key' => 'advertisement_3',
                                        'type' => 'advertisement',
                                        'title' => 'جایگاه تبلیغات 3',
                                        'description' => 'جایگاه سوم تبلیغات',
                                        'is_active' => false,
                                        'sort_order' => 4,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'key' => 'footer',
                        'layout_type' => 'single',
                        'sort_order' => 3,
                        'areas' => [
                            [
                                'key' => 'footer_primary',
                                'title' => 'باکس تمام عرض پایین',
                                'description' => 'ناحیه پایانی صفحه',
                                'width' => 12,
                                'sort_order' => 1,
                                'elements' => [
                                    [
                                        'key' => 'posts',
                                        'type' => 'posts',
                                        'title' => 'مطالب رایگان',
                                        'description' => 'نمایش مطالب رایگان',
                                        'is_active' => true,
                                        'sort_order' => 1,
                                    ],
                                    [
                                        'key' => 'services',
                                        'type' => 'services',
                                        'title' => 'سرویس‌ها و خدمات',
                                        'description' => 'نمایش سرویس‌ها و خدمات',
                                        'is_active' => true,
                                        'sort_order' => 2,
                                    ],
                                    [
                                        'key' => 'physical-products',
                                        'type' => 'physical_products',
                                        'title' => 'محصولات فیزیکی',
                                        'description' => 'نمایش محصولات فیزیکی',
                                        'is_active' => true,
                                        'sort_order' => 3,
                                    ],
                                    [
                                        'key' => 'projects',
                                        'type' => 'projects',
                                        'title' => 'پروژه‌ها',
                                        'description' => 'نمایش پروژه‌ها',
                                        'is_active' => true,
                                        'sort_order' => 4,
                                    ],
                                    [
                                        'key' => 'videos',
                                        'type' => 'videos',
                                        'title' => 'ویدیوها',
                                        'description' => 'نمایش ویدیوها',
                                        'is_active' => true,
                                        'sort_order' => 5,
                                    ],
                                    [
                                        'key' => 'testimonials',
                                        'type' => 'testimonials',
                                        'title' => 'نظرات کاربران',
                                        'description' => 'نمایش نظرات کاربران',
                                        'is_active' => true,
                                        'sort_order' => 6,
                                    ],
                                    [
                                        'key' => 'advertisement_4',
                                        'type' => 'advertisement',
                                        'title' => 'جایگاه تبلیغات 4',
                                        'description' => 'جایگاه چهارم تبلیغات',
                                        'is_active' => false,
                                        'sort_order' => 7,
                                    ],
                                    [
                                        'key' => 'advertisement_5',
                                        'type' => 'advertisement',
                                        'title' => 'جایگاه تبلیغات 5',
                                        'description' => 'جایگاه پنجم تبلیغات',
                                        'is_active' => false,
                                        'sort_order' => 8,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * فقط row جدید می‌سازد.
     * برای row موجود، تنظیمات قابل شخصی‌سازی مثل sort_order و layout_type را overwrite نمی‌کند.
     *
     * @param PageLayout $layout
     * @param array $rows
     * @return void
     */
    private function syncRows(PageLayout $layout, array $rows): void
    {
        foreach ($rows as $rowData) {
            $row = PageLayoutRow::query()->firstOrNew([
                'page_layout_id' => $layout->id,
                'key' => $rowData['key'],
            ]);

            if (!$row->exists) {
                $row->layout_type = $rowData['layout_type'];
                $row->sort_order = $rowData['sort_order'];
            }

            $row->save();

            $this->syncAreas($layout, $row, $rowData['areas']);
        }
    }

    /**
     * area را با key در سطح layout پیدا می‌کند تا اگر قبلاً جابه‌جا شده بود،
     * دوباره به row پیش‌فرض seed برنگردد.
     *
     * title و description همیشه sync می‌شوند.
     * width و sort_order و row placement فقط هنگام ایجاد اولیه ست می‌شوند.
     *
     * @param PageLayout $layout
     * @param PageLayoutRow $defaultRow
     * @param array $areas
     * @return void
     */
    private function syncAreas(PageLayout $layout, PageLayoutRow $defaultRow, array $areas): void
    {
        $layoutRowIds = PageLayoutRow::query()
            ->where('page_layout_id', $layout->id)
            ->pluck('id');

        foreach ($areas as $areaData) {
            $area = PageLayoutArea::query()
                ->where('key', $areaData['key'])
                ->whereIn('page_layout_row_id', $layoutRowIds)
                ->first();

            if (!$area) {
                $area = new PageLayoutArea([
                    'page_layout_row_id' => $defaultRow->id,
                    'key' => $areaData['key'],
                    'width' => $areaData['width'] ?? null,
                    'sort_order' => $areaData['sort_order'],
                ]);
            }

            $area->title = $areaData['title'];
            $area->description = $areaData['description'] ?? null;

            $area->save();

            $this->syncElements($layout, $area, $areaData['elements']);
        }
    }

    /**
     * element را با key در سطح layout پیدا می‌کند تا اگر قبلاً جابه‌جا شده بود،
     * دوباره به area پیش‌فرض seed برنگردد.
     *
     * title و description و type همیشه sync می‌شوند.
     * is_active و sort_order و area placement فقط هنگام ایجاد اولیه ست می‌شوند.
     *
     * @param PageLayout $layout
     * @param PageLayoutArea $defaultArea
     * @param array $elements
     * @return void
     */
    private function syncElements(PageLayout $layout, PageLayoutArea $defaultArea, array $elements): void
    {
        $layoutRowIds = PageLayoutRow::query()
            ->where('page_layout_id', $layout->id)
            ->pluck('id');

        $layoutAreaIds = PageLayoutArea::query()
            ->whereIn('page_layout_row_id', $layoutRowIds)
            ->pluck('id');

        foreach ($elements as $elementData) {
            $element = PageLayoutElement::query()
                ->where('key', $elementData['key'])
                ->whereIn('page_layout_area_id', $layoutAreaIds)
                ->first();

            if (!$element) {
                $element = new PageLayoutElement([
                    'page_layout_area_id' => $defaultArea->id,
                    'key' => $elementData['key'],
                    'is_active' => $elementData['is_active'],
                    'sort_order' => $elementData['sort_order'],
                ]);
            }

            $element->type = $elementData['type'];
            $element->title = $elementData['title'];
            $element->description = $elementData['description'] ?? null;

            $element->save();
        }
    }
}
