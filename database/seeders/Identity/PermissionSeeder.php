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
            'tag' => [
                'manage' => 'مدیریت برچسب ها',
            ],
            'category' => [
                'manage' => 'مدیریتF دسته بندی ها',
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
            'page-layouts' => [
                'home' => 'مدیریت چیدمان - صفحه اصلی',
            ],
            'setting' => [
                'general' => 'تنظیمات عمومی',
                'header' => 'تنظیمات هدر',
                'footer' => 'تنظیمات فوتر',
                'home' => 'صفحه اصلی',
                'about-us' => 'درباره ما',
                'ads' => 'تنظیمات تبلیغات',
                'sidebar-widgets' => 'تنظیمات ابزارک های سایدبار',
                'projects' => 'تنظیمات صفحه پروژه ها',
                'posts' => 'تنظیمات صفحه پست ها',
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
