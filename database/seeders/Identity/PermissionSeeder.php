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
            'media' => [
                'list' => 'مشاهده کتابخانه رسانه',
                'create' => 'آپلود رسانه',
                'edit' => 'ویرایش اطلاعات رسانه',
            ],
            'poll' => [
                'list' => 'لیست نظرسنجی‌ها',
                'create' => 'ایجاد نظرسنجی',
                'edit' => 'ویرایش نظرسنجی',
                'delete' => 'حذف نظرسنجی',
                'analytics' => 'مشاهده آمار نظرسنجی',
                'export' => 'دریافت خروجی پاسخ‌های نظرسنجی',
            ],
            'video' => [
                'list' => 'لیست ویدیوها',
                'create' => 'ایجاد ویدیو',
                'edit' => 'ویرایش ویدیو',
                'delete' => 'حذف ویدیو',
                'category' => 'مدیریت دسته‌بندی ویدیوها',
                'tag' => 'مدیریت برچسب ویدیوها',
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
                'withdrawals' => 'بررسی درخواست‌های وجه فروشگاه‌ها',
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
                'medal' => 'مدیریت مدال‌های محصولات',
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
            'redirect' => [
                'manage' => 'مدیریت ریدایرکت ها',
            ],
            'setting' => [
                'general' => 'تنظیمات عمومی',
                'seo' => 'تنظیمات سئو',
                'header' => 'تنظیمات هدر',
                'footer' => 'تنظیمات فوتر',
                'home' => 'صفحه اصلی',
                'about-us' => 'درباره ما',
                'team' => 'صفحه تیم',
                'ads' => 'تنظیمات تبلیغات',
                'sidebar-widgets' => 'تنظیمات ابزارک های سایدبار',
                'auth' => 'ورود/ثبت نام',
                'projects' => 'تنظیمات صفحه پروژه ها',
                'posts' => 'تنظیمات صفحه نوشته ها',
                'products' => 'تنظیمات صفحه محصولات',
                'ticket' => 'تنظیمات تیکت',
                'backup' => 'تنظیمات بک آپ',
            ],
            'order' => [
                'list' => 'لیست سفارش ها',
                'edit' => 'ویرایش سفارش',
            ],
            'cart' => [
                'abandoned' => 'مدیریت سبدهای خرید رها شده',
            ],
            'purchase-access' => [
                'reset-downloads' => 'ریست محدودیت دانلود خرید کاربران',
            ],
            'launcher-device' => [
                'manage' => 'مشاهده و Reset دستگاه‌های لانچر کاربران',
            ],
            'analytics' => [
                'view' => 'مشاهده تحلیل فروش و درآمد',
            ],
            'report' => [
                'view' => 'مشاهده گزارش‌های عمومی سامانه',
            ],
            'raffle-campaign' => [
                'list' => 'لیست قرعه کشی ها',
                'show' => 'مشاهده و مدیریت داشبورد قرعه کشی',
                'create' => 'ایجاد قرعه کشی',
                'edit' => 'ویرایش قرعه کشی',
                'delete' => 'حذف قرعه کشی پیش نویس',
                'transition' => 'تغییر وضعیت قرعه کشی',
            ],
            'raffle-ticket' => [
                'list' => 'مشاهده بلیط های قرعه کشی',
                'issue' => 'صدور دستی بلیط قرعه کشی',
                'invalidate' => 'ابطال بلیط قرعه کشی',
            ],
            'raffle-participant' => [
                'list' => 'مشاهده شرکت کنندگان قرعه کشی',
            ],
            'raffle-draw' => [
                'list' => 'مشاهده اجرای قرعه کشی و نتایج',
                'execute' => 'شروع اجرای قرعه کشی',
                'retry' => 'اجرای مجدد DrawRun ناموفق با همان Seed',
            ],
            'raffle-winner' => [
                'list' => 'مشاهده و مدیریت برندگان قرعه کشی',
                'confirm' => 'تایید برنده قرعه کشی',
                'reject' => 'رد صلاحیت برنده قرعه کشی',
                'promote' => 'جایگزینی برنده ذخیره',
                'deliver' => 'ثبت تحویل جایزه',
            ],
            'coupon' => [
                'list' => 'لیست کدهای تخفیف',
                'create' => 'ایجاد کدتخفیف',
                'edit' => 'ویرایش کدتخفیف',
                'delete' => 'حذف کدتخفیف',
            ],
            'loyalty-rule' => [
                'list' => 'لیست قوانین امتیاز',
                'create' => 'ایجاد قانون امتیاز',
                'edit' => 'ویرایش قانون امتیاز',
                'delete' => 'حذف قانون امتیاز',
            ],
            'loyalty-account' => [
                'list' => 'لیست کاربران باشگاه مخاطبان',
                'show' => 'مشاهده تاریخچه امتیاز کاربران',
                'adjust' => 'افزایش و کاهش دستی امتیاز کاربران',
            ],
            'wallet' => [
                'adjust' => 'افزایش و کاهش دستی موجودی کیف پول کاربران',
                'withdrawals' => 'بررسی و ثبت واریز درخواست‌های برداشت کیف پول',
                'settings' => 'فعال و غیرفعال کردن درخواست برداشت کیف پول',
            ],
            'loyalty-reward' => [
                'list' => 'لیست پاداش‌های باشگاه',
                'create' => 'ایجاد پاداش باشگاه',
                'edit' => 'ویرایش پاداش باشگاه',
                'delete' => 'حذف پاداش باشگاه',
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
