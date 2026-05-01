<?php

namespace App\Console\Commands;

use App\Application\System\Actions\UpdateSettingAction;
use Illuminate\Console\Command;

class SyncDefaultSettingsCommand extends Command
{
    protected $signature = 'settings:sync-defaults';

    protected $description = 'Sync default application settings.';

    public function handle(UpdateSettingAction $updateSettingAction): int
    {
        $this->info('Syncing default settings...');

        foreach ($this->settings() as $group => $items) {
            foreach ($items as $key => $value) {
                $updateSettingAction->execute($group, $key, $value);

                $this->line("Updated: {$group}.{$key}");
            }
        }

        $this->info('Default settings synced successfully.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function settings(): array
    {
        return [
            'general' => [
                'title' => 'مودینگ وی',
                'subtitle' => 'دانلود پچ و ماد بازی ها',
                'select_related_content_settings' => 'category',
            ],

            'seo' => [
                'site_name' => 'مودینگ وی',
                'site_alternate_name' => 'مرجع رسمی ویرایش بازیهای رایانه ای PES و FIFA در ایران',
                'site_description' => 'مرجع رسمی ویرایش بازیهای رایانه ای PES و FIFA در ایران',
                'separator' => '|',
                'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
                'og_locale' => 'fa_IR',
                'twitter_card' => 'summary_large_image',
                'google_site_verification' => 'umZbYry44M0JcTM-Hy1yEagRVouf4KiJur6wOILDw-A',
                'bing_site_verification' => '9EA065DF711ED89F1DA8619597ACCB8A',
                'schema_language' => 'fa-IR',
                'organization_name' => 'مودینگ وی',
                'organization_alternate_name' => 'ModdingWay Farsi',
                'organization_email' => 'moddingway.ir@gmail.com',
                'organization_phone' => '00983142772860',
                'organization_founding_date' => '2015-05-05',
                'organization_legal_name' => 'moddingway',
                'organization_number_of_employees' => '11-50',
                'organization_same_as' => 'https://instagram.com/moddingwayir/',
                'home_title' => 'مودینگ وی | مرجع دانلود PES, دانلود FIFA, دانلود پچ PES, پچ لیگ ایران',
                'home_description' => 'بروزترین سایت دانلود PES و دانلود FIFA برای کامپیوتر و اندروید و PS4 با پچ لیگ ایران، ادیت PES و ادیت FIFA, آموزش PES و FIFA',
                'post_seo_title' => '{post_title} {separator} {site_title}',
                'post_social_seo_title' => '{post_title}',
                'page_seo_title' => '{page_title} {separator} {site_title}',
                'page_social_seo_title' => '{page_title}',
                'project_seo_title' => '{project_title} {separator} {site_title}',
                'project_social_seo_title' => '{project_title}',
                'post_category_seo_title' => 'بایگانی‌های {taxonomy_title} {separator} {site_title}',
                'post_category_social_seo_title' => 'بایگانی‌های {taxonomy_title}',
                'post_tag_seo_title' => 'بایگانی‌ها {taxonomy_title} {separator} {site_title}',
                'post_tag_social_seo_title' => 'بایگانی‌های {taxonomy_title}',
                'post_list_seo_title' => 'همه نوشته ها {separator} {site_title}',
                'project_list_seo_title' => 'همه پروژه ها {separator} {site_title}',
                'search_seo_title' => 'شما برای {search_term} جستجو کردید {separator} {site_title}',
            ],

            'footer' => [
                'about' => 'ما در مجموعه بزرگ مودینگ وی از سال 1396 رسما فعالیت خود را در حوزه ادیت بازیهای FIFA و PES شروع کرده ایم و همچنان با قدرت در این حوزه فعالیت خواهیم کرد. در سالیان اخیر شما شاهد عرضه بهترین پچ های بروزرسانی تاریخ لیگ ایران با عنوان PGL Patch بوده و خواهید بود. محصولات مجموعه مودینگ وی در دو فروشگاه زیرمجموعه با اسم های ادیت سیتی(محصولات دانلودی) و مودینگ استور (محصولات فیزیکی) عرضه میشوند.',
                'copyright' => 'کلی حقوق مادی و معنوی متعلق به مجموعه مودینگ وی میباشد',
                'quality_title' => 'هدف و کیفیت',
                'quality_text' => 'تولید پچ‌های تخصصی لیگ برتر ایران برای FIFA و PES با تمام جزئیات کامل برای PC و کنسول‌های نسل جدید.',
                'commitment_title' => 'تعهد ما به شما',
                'commitment_text' => 'تمامی خدمات ما مطابق با نیاز کاربران و نهایت کیفیت ارائه می شود و هدف ما همیشه رضایت کامل کاربران بوده است.',
                'store_title' => 'مودینگ استور',
                'store_url' => 'https://moddingstore.ir/',
                'store_text' => 'محصولات فیزیکی و پکیج‌های گیمینگ',
            ],

            'posts' => [
                'first_box_title' => 'آخرین نوشته های مودینگ وی',
                'first_box_subtitle' => 'آخرین نوشته ها درباره پچ ها، پیشرفت پروژه ها و آموزش های مودینگ وی',
                'first_box_label' => 'صفحه آرشیو',
                'posts_per_page' => 12,
            ],

            'projects' => [
                'first_box_title' => 'لیست پروژه ها + درصد پیشرفت + وضعیت هر پروژه',
                'first_box_subtitle' => 'اینجا میتونی پروژه های فعال، در حال توسعه یا تکمیل شده رو ببینی، از جزئیاتشون رو دنبال کنی و از آخرین آپدیت ها باخبر بشی.',
                'first_box_label' => 'پروژه های مودینگ وی',
            ],
        ];
    }
}
