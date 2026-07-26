<?php

namespace Database\Seeders\Identity;

use App\Domain\Identity\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'admin' => [
                'display' => 'مشاهده پنل ادمین',
                'api-docs' => 'مشاهده مستندات API',
            ],
            'post' => [
                'list' => 'لیست نوشته ها',
                'create' => 'ایجاد نوشته',
                'edit' => 'ویرایش نوشته',
                'delete' => 'حذف نوشته',
            ],
            'project' => [
                'list' => 'لیست پروژه ها',
                'create' => 'ایجاد پروژه',
                'edit' => 'ویرایش پروژه',
                'delete' => 'حذف پروژه',
            ],
            'page' => [
                'list' => 'لیست برگه ها',
                'create' => 'ایجاد برگه',
                'edit' => 'ویرایش برگه',
                'delete' => 'حذف برگه',
            ],
            'store' => [
                'list' => 'لیست فروشنده ها',
                'create' => 'ایجاد فروشنده',
                'edit' => 'ویرایش فروشنده',
                'delete' => 'حذف فروشنده',
            ],
            'product' => [
                'list' => 'لیست محصولات',
                'create' => 'ایجاد محصول',
                'edit' => 'ویرایش محصول',
                'delete' => 'حذف محصول',
                'category' => 'مدیریت دسته های محصولات',
                'tag' => 'مدیریت برچسب های محصولات',
                'attribute' => 'مدیریت ویژگی های محصولات',
                'information' => 'مدیریت فیلدهای اطلاعات محصولات',
            ],
            'tag' => [
                'manage' => 'مدیریت برچسب ها',
            ],
            'category' => [
                'manage' => 'مدیریت دسته بندی ها',
            ],
            'user' => [
                'list' => 'لیست کاربر ها',
                'create' => 'ایجاد کاربر',
                'edit' => 'ویرایش کاربر',
                'delete' => 'حذف کاربر',
            ],
            'role' => [
                'list' => 'لیست نقش ها',
                'create' => 'ایجاد نقش',
                'edit' => 'ویرایش نقش',
                'delete' => 'حذف نقش',
            ],
            'comment' => [
                'list' => 'لیست دیدگاه ها',
                'edit' => 'ویرایش دیدگاه',
                'delete' => 'حذف دیدگاه',
                'auto_approve' => 'تایید اتوماتیک دیدگاه',
            ],
            'page-layout' => [
                'home' => 'مدیریت چیدمان صفحه اصلی',
                'post-content' => 'مدیریت چیدمان صفحه نوشته',
                'sidebar' => 'مدیریت چیدمان سایدبار',
            ],
            'navigation-menu' => [
                'manage' => 'مدیریت فهرست ها',
            ],
            'setting' => [
                'general' => 'تنظیمات عمومی',
                'seo' => 'تنظیمات سئو',
                'header' => 'تنظیمات هدر',
                'footer' => 'تنظیمات فوتر',
                'home' => 'صفحه اصلی',
                'about-us' => 'درباره ما',
                'ads' => 'تنظیمات تبلیغات',
                'sidebar-widgets' => 'تنظیمات ابزارک های سایدبار',
                'auth' => 'ورود/ثبت نام',
                'projects' => 'تنظیمات صفحه پروژه ها',
                'posts' => 'تنظیمات صفحه نوشته ها',
                'products' => 'تنظیمات صفحه محصولات',
                'ticket' => 'تنظیمات تیکت',
            ],
            'order' => [
                'list' => 'لیست سفارش ها',
                'edit' => 'ویرایش سفارش',
            ],
            'coupon' => [
                'list' => 'لیست کدهای تخفیف',
                'create' => 'ایجاد کدتخفیف',
                'edit' => 'ویرایش کدتخفیف',
                'delete' => 'حذف کدتخفیف',
            ],
            'ticket' => [
                'list' => 'لیست تیکت‌ها',
                'show' => 'مشاهده و پاسخ تیکت',
                'change-status' => 'تغییر وضعیت تیکت',
                'change-department' => 'تغییر دپارتمان تیکت',
                'manage-departments' => 'مدیریت دپارتمان ها',
                'delete' => 'حذف تیکت',
            ],
            'team-member' => [
                'list' => 'لیست اعضای تیم',
                'create' => 'ایجاد عضو تیم',
                'edit' => 'ویرایش عضو تیم',
                'delete' => 'حذف عضو تیم',
            ],
        ];

        $permissionIds = [];

        foreach ($permissions as $group => $items) {
            foreach ($items as $ability => $label) {
                $permission = Permission::updateOrCreate([
                    'group' => $group,
                    'ability' => $ability,
                ], [
                    'label' => $label,
                ]);

                $permissionIds[] = $permission->id;
            }
        }

        Permission::whereNotIn('id', $permissionIds)->delete();
    }
}
