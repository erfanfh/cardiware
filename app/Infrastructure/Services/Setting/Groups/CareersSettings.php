<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class CareersSettings extends BaseSettings
{
    protected string $group = 'careers';

    protected array $allowedKeys = [
        'hero_badge', 'hero_title', 'hero_description', 'hero_primary_button',
        'hero_secondary_button', 'stats_positions_title', 'stats_teams_title',
        'stats_cooperation_title', 'stats_cooperation_value', 'stats_review_title',
        'stats_review_value', 'why_title', 'why_subtitle', 'why_items',
        'benefits_title', 'benefits_subtitle', 'benefits_items',
        'jobs_title', 'jobs_subtitle', 'jobs_empty_text', 'process_title',
        'process_subtitle', 'process_items', 'skills_title', 'skills_items',
        'general_title', 'general_description', 'general_button',
        'form_title', 'form_subtitle', 'form_consent_text',
        'tips_title', 'tips_items', 'review_title', 'review_items',
        'general_cta_title', 'general_cta_description',
        'faq_title', 'faq_subtitle', 'faq_items',
    ];

    protected array $casts = [
        'why_items' => RepeaterArrayCast::class,
        'benefits_items' => RepeaterArrayCast::class,
        'process_items' => RepeaterArrayCast::class,
        'skills_items' => RepeaterArrayCast::class,
        'tips_items' => RepeaterArrayCast::class,
        'review_items' => RepeaterArrayCast::class,
        'faq_items' => RepeaterArrayCast::class,
    ];

    public static function defaults(): array
    {
        return [
            'hero_badge' => 'فرصت همکاری با مودینگ وی',
            'hero_title' => 'اگر به دنیای بازی، مادینگ، تولید محتوا و ساخت یک برند حرفه‌ای علاقه‌مندی، جای تو اینجاست.',
            'hero_description' => 'در این صفحه هم می‌توانی موقعیت‌های شغلی فعال مودینگ وی را ببینی و هم اگر فکر می‌کنی مهارت و انگیزه همکاری با ما را داری، درخواستت را ارسال کنی تا بررسی شود.',
            'hero_primary_button' => 'مشاهده فرصت‌های شغلی',
            'hero_secondary_button' => 'ارسال درخواست همکاری',
            'stats_positions_title' => 'فرصت فعال',
            'stats_teams_title' => 'تیم‌های درگیر',
            'stats_cooperation_title' => 'نوع همکاری',
            'stats_cooperation_value' => 'حضوری / ریموت',
            'stats_review_title' => 'بررسی رزومه',
            'stats_review_value' => 'سریع و مرحله‌ای',
            'why_title' => 'چرا همکاری با مودینگ وی؟',
            'why_subtitle' => 'محیط پویا، تخصصی و رو‌به‌رشد',
            'why_items' => [
                ['title' => 'کار روی پروژه‌های واقعی', 'description' => 'از تولید پچ و محصول گیمینگ تا فروشگاه، محتوا و تجربه کاربری.'],
                ['title' => 'فرصت رشد مهارتی', 'description' => 'مناسب برای افراد باانگیزه‌ای که می‌خواهند در یک مسیر تخصصی رشد کنند.'],
                ['title' => 'همکاری منعطف', 'description' => 'بسته به نقش، امکان همکاری پروژه‌ای، پاره‌وقت یا تمام‌وقت وجود دارد.'],
            ],
            'benefits_title' => 'آنچه در مودینگ وی تجربه می‌کنی',
            'benefits_subtitle' => 'فرهنگ کاری و مزیت‌های همکاری',
            'benefits_items' => [
                ['icon' => '🎮', 'title' => 'فضای تخصصی گیمینگ', 'description' => 'همکاری در اکوسیستمی که موضوع اصلی آن بازی، ماد و محصولات مرتبط است.'],
                ['icon' => '🚀', 'title' => 'فرصت رشد واقعی', 'description' => 'نقش‌هایی که می‌توانی روی خروجی نهایی اثر مستقیم داشته باشی.'],
                ['icon' => '🤝', 'title' => 'همکاری شفاف', 'description' => 'شرح وظایف روشن، تعامل مستمر و مسیر مشخص برای ارزیابی و ادامه همکاری.'],
                ['icon' => '🧠', 'title' => 'اهمیت به ایده‌ها', 'description' => 'اگر ایده خوب داشته باشی، شنیده می‌شود و می‌تواند وارد مسیر اجرا شود.'],
            ],
            'jobs_title' => 'موقعیت‌های شغلی فعال',
            'jobs_subtitle' => 'نقش‌هایی که همین حالا برایشان نیرو می‌پذیریم',
            'jobs_empty_text' => 'در حال حاضر موقعیت شغلی فعالی ثبت نشده است.',
            'process_title' => 'فرآیند همکاری',
            'process_subtitle' => 'مسیر بررسی درخواست‌ها',
            'process_items' => [
                ['title' => 'ارسال فرم همکاری', 'description' => 'اطلاعات، مهارت‌ها و نمونه‌کار خودت را ثبت کن.'],
                ['title' => 'بررسی اولیه', 'description' => 'تیم ما درخواستت را از نظر تناسب با موقعیت‌ها بررسی می‌کند.'],
                ['title' => 'مصاحبه یا تست', 'description' => 'بسته به نقش، ممکن است گفت‌وگو یا تست کوتاه داشته باشیم.'],
                ['title' => 'شروع همکاری', 'description' => 'در صورت تایید، همکاری به‌صورت مرحله‌ای آغاز می‌شود.'],
            ],
            'skills_title' => 'مهارت‌هایی که بیشتر به کار ما می‌آیند',
            'skills_items' => array_map(fn (string $title) => ['title' => $title], ['تولید محتوا', 'فتوشاپ', 'سئو', 'پشتیبانی', 'UI', 'ویدیو ادیت', 'فضای گیمینگ', 'ارتباط با کاربر']),
            'general_title' => 'همکاری حتی بدون موقعیت باز',
            'general_description' => 'اگر نقش متناسب با مهارتت را در لیست بالا پیدا نکردی، باز هم می‌توانی فرم همکاری را ارسال کنی.',
            'general_button' => 'ارسال درخواست عمومی',
            'form_title' => 'فرم ارسال درخواست همکاری',
            'form_subtitle' => 'فرم زیر را کامل کن تا درخواستت ثبت شود',
            'form_consent_text' => 'اطلاعات وارد شده صحیح است و با شرایط بررسی درخواست همکاری موافقم.',
            'tips_title' => 'نکات مهم قبل از ارسال فرم',
            'tips_items' => [
                ['title' => 'اگر نمونه‌کار داری، حتماً لینک آن را وارد کن.'],
                ['title' => 'توضیح کوتاه اما دقیق، شانس بررسی بهتر را افزایش می‌دهد.'],
                ['title' => 'درخواست‌های مرتبط‌تر زودتر بررسی می‌شوند.'],
            ],
            'review_title' => 'وضعیت بررسی رزومه',
            'review_items' => [
                ['title' => 'تکمیل فرم', 'description' => '۱۰۰٪'],
                ['title' => 'بررسی اولیه', 'description' => 'در صف'],
                ['title' => 'ارتباط با متقاضی', 'description' => 'پس از تایید'],
            ],
            'general_cta_title' => 'همکاری عمومی هم داریم',
            'general_cta_description' => 'حتی اگر موقعیت مرتبط پیدا نکردی اما فکر می‌کنی می‌توانی به مودینگ وی کمک کنی، فرم را برای ما بفرست.',
            'faq_title' => 'سوالات متداول همکاری',
            'faq_subtitle' => 'چند پاسخ سریع برای متقاضیان',
            'faq_items' => [
                ['title' => 'آیا امکان همکاری ریموت وجود دارد؟', 'answer' => 'بله، برای بعضی نقش‌ها همکاری ریموت یا منعطف در نظر گرفته می‌شود.'],
                ['title' => 'اگر سابقه زیاد نداشته باشم باز هم می‌توانم فرم بفرستم؟', 'answer' => 'بله، مهارت و انگیزه شما نیز بررسی می‌شود.'],
                ['title' => 'چقدر زمان می‌برد تا درخواست بررسی شود؟', 'answer' => 'پس از بررسی اولیه، در صورت تناسب با شما تماس خواهیم گرفت.'],
            ],
        ];
    }

    public function values(): array
    {
        $defaults = self::defaults();

        return collect($this->allowedKeys)->mapWithKeys(
            fn (string $key) => [$key => $this->get($key, $defaults[$key])]
        )->all();
    }
}
