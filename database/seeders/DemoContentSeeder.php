<?php

namespace Database\Seeders;

use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Post;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Infrastructure\Services\Setting\Groups\HomeSettings;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * ساخته‌شد تا صرفاً محیط توسعه آفلاین پر از داده نمایشی شود.
 * هرگز در نصب پروداکشن اجرا نشود (جزو DatabaseSeeder نیست).
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        // ارائه‌دهنده پیامک محیط توسعه باید از ابتدا فعال باشد تا مراحل بعدی (مثل قرعه‌کشی دمو) پیامک بفرستند.
        app(\App\Infrastructure\Services\Setting\Groups\GeneralSettings::class)
            ->set('default_sms_provider', \App\Infrastructure\Services\Sms\Providers\LogSmsProvider::class);

        $admin = User::query()->where('email', 'admin@moddingway.ir')->firstOrFail();
        $user = User::query()->where('email', 'user@moddingway.ir')->firstOrFail();

        $this->seedDemoMobiles($admin, $user);
        $this->call([
            \Database\Seeders\Loyalty\LoyaltyLevelSeeder::class,
            \Database\Seeders\Loyalty\LoyaltyGamificationSeeder::class,
        ]);
        $categories = $this->seedTaxonomy();
        $this->seedPosts($admin, $categories);
        $projects = $this->seedProjects();
        $this->seedHomeSettings($categories, $projects);
        $this->seedGeneralSettings();
        $this->seedLoyalty($user);
        $this->call(\Database\Seeders\Raffle\RaffleDemoSeeder::class);

        Artisan::call('cache:clear');
    }

    /**
     * ورود اصلی سایت با شماره موبایل است؛ حساب‌های دمو شماره تاییدشده می‌گیرند
     * تا مسیر «موبایل + رمز عبور» در محیط آفلاین قابل تست باشد.
     */
    private function seedDemoMobiles(User $admin, User $user): void
    {
        $admin->forceFill([
            'mobile' => '09120000001',
            'mobile_verified_at' => $admin->mobile_verified_at ?? now(),
        ])->save();

        $user->forceFill([
            'mobile' => '09120000002',
            'mobile_verified_at' => $user->mobile_verified_at ?? now(),
        ])->save();
    }

    /**
     * @return array<string, TaxonomyTerm>
     */
    private function seedTaxonomy(): array
    {
        $definitions = [
            'tutorials' => ['آموزش‌ها', 'tutorials'],
            'news' => ['اخبار', 'news'],
            'reviews' => ['نقد و بررسی', 'reviews'],
            'downloads' => ['دانلود مادها', 'downloads'],
            'tricks' => ['ترفندها', 'tricks'],
            'videos' => ['آموزش‌های تصویری', 'video-tutorials'],
        ];

        $terms = [];

        foreach ($definitions as $key => [$title, $slug]) {
            $terms[$key] = TaxonomyTerm::query()->firstOrCreate(
                ['taxonomy' => TaxonomyKeyEnum::POST_CATEGORY, 'slug' => $slug],
                ['title' => $title, 'path' => $slug],
            );
        }

        return $terms;
    }

    private function seedPosts(User $admin, array $categories): void
    {
        $posts = [
            [
                'title' => 'آموزش کامل نصب پچ FC26 روی سیستم ویندوز',
                'slug' => 'fc26-patch-install-guide',
                'cat' => 'tutorials',
                'img' => 'post-1',
                'excerpt' => 'در این آموزش قدم‌به‌قدم نصب پچ FC26، پیش‌نیازها و رفع خطاهای رایج را بررسی می‌کنیم.',
            ],
            [
                'title' => 'انتشار نسخه جدید پچ PGL V5 با لیگ ایران',
                'slug' => 'pgl-v5-released',
                'cat' => 'news',
                'img' => 'post-2',
                'excerpt' => 'نسخه جدید پچ PGL V5 با به‌روزرسانی ترکیب و چهره بازیکنان لیگ برتر منتشر شد.',
            ],
            [
                'title' => 'نقد و بررسی ماد آنلاکر FC26؛ ارزش خرید دارد؟',
                'slug' => 'fc26-unlocker-review',
                'cat' => 'reviews',
                'img' => 'post-3',
                'excerpt' => 'بررسی کامل امکانات، پایداری و تجربه کاربری ماد آنلاکر در نسخه جدید بازی.',
            ],
            [
                'title' => 'دانلود ماد گرافیکی بهبود چمن و استادیوم‌ها',
                'slug' => 'grass-stadium-mod-download',
                'cat' => 'downloads',
                'img' => 'post-4',
                'excerpt' => 'بسته ماد گرافیکی برای واقعی‌تر شدن چمن و استادیوم‌ها همراه با راهنمای نصب.',
            ],
            [
                'title' => 'ترفندهای افزایش سرعت پاس‌کاری در حالت حرفه‌ای',
                'slug' => 'passing-tricks-guide',
                'cat' => 'tricks',
                'img' => 'post-6',
                'excerpt' => 'با این ترفندها دقت پاس‌های خود را در حالت حرفه‌ای به‌شکل محسوسی بالا ببرید.',
            ],
            [
                'title' => 'ویدیو: تنظیمات بهینه گرافیک برای کارت‌های میان‌رده',
                'slug' => 'graphics-settings-video',
                'cat' => 'videos',
                'img' => 'post-5',
                'excerpt' => 'آموزش تصویری بهترین تنظیمات گرافیکی برای اجرای روان بازی روی کارت‌های میان‌رده.',
            ],
            [
                'title' => 'تقویم انتشار آپدیت‌های فصل جدید',
                'slug' => 'season-updates-calendar',
                'cat' => 'news',
                'img' => 'post-2',
                'excerpt' => 'همه آپدیت‌های برنامه‌ریزی‌شده فصل جدید را در یک تقویم یکجا ببینید.',
            ],
            [
                'title' => 'ویدیو: ساخت تورنمنت اختصاصی در MW Launcher',
                'slug' => 'launcher-tournament-video',
                'cat' => 'videos',
                'img' => 'post-5',
                'excerpt' => 'در این ویدیو ساخت و مدیریت تورنمنت اختصاصی با لانچر مودینگ‌وی را یاد می‌گیرید.',
            ],
        ];

        foreach ($posts as $index => $data) {
            $post = Post::query()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'author_id' => $admin->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'body' => '<p>' . $data['excerpt'] . '</p><p>این متن نمونه برای نمایش طراحی در محیط توسعه آفلاین است و محتوای واقعی نیست.</p>',
                    'status' => 'active',
                    'published_at' => CarbonImmutable::now()->subDays(count($posts) - $index)->setTime(10, 30),
                    'display_views_offset' => rand(120, 5400),
                ],
            );

            ContentSlug::query()->firstOrCreate(
                ['slug' => $data['slug'], 'sluggable_type' => Post::class, 'sluggable_id' => $post->id],
                ['is_current' => true],
            );

            $post->categories()->syncWithoutDetaching([$categories[$data['cat']]->id]);
        }
    }

    /**
     * @return array<int, Project>
     */
    private function seedProjects(): array
    {
        $definitions = [
            [
                'title' => 'پروژه PGL V6',
                'slug' => 'pgl-v6',
                'progress' => 68,
                'version' => '6.0.0-beta',
                'img' => 'project-1',
                'excerpt' => 'نسخه بعدی پچ محبوب PGL با موتور جدید مسابقات و گرافیک بازطراحی‌شده.',
            ],
            [
                'title' => 'لیگ‌های اروپا ۲۶–۲۰۲۵',
                'slug' => 'european-leagues-2026',
                'progress' => 41,
                'version' => '2.4.0',
                'img' => 'project-2',
                'excerpt' => 'به‌روزرسانی کامل لیگ‌های اروپا شامل ترکیب‌ها، کیت‌ها و چهره بازیکنان.',
            ],
        ];

        $projects = [];

        foreach ($definitions as $data) {
            $projects[] = Project::query()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'status' => 'active',
                    'progress_percent' => $data['progress'],
                    'current_version' => $data['version'],
                    'creator_name' => 'تیم مودینگ‌وی',
                    'platform' => 'PC (Windows)',
                    'excerpt' => $data['excerpt'],
                    'body' => '<p>' . $data['excerpt'] . '</p>',
                    'released_at' => CarbonImmutable::now()->subMonths(2),
                    'last_updated_at' => CarbonImmutable::now()->subDays(3),
                ],
            );
        }

        return $projects;
    }

    private function seedHomeSettings(array $categories, array $projects): void
    {
        $home = app(HomeSettings::class);

        $home->set('hero_slider', [
            [
                'image' => ['path' => 'demo/hero-1.svg'],
                'top_label_text' => 'جدیدترین نسخه',
                'square_label_text' => 'MW',
                'subtitle' => 'لانچر اختصاصی مودینگ‌وی',
                'title' => 'MW Launcher برای ویندوز منتشر شد',
                'description' => "همه پچ‌ها، مادها و آپدیت‌ها را یک‌جا مدیریت کنید.\nدانلود امن، نصب خودکار و به‌روزرسانی لحظه‌ای.",
                'primary_button_text' => 'دانلود لانچر',
                'primary_button_url' => '/launcher',
                'secondary_button_text' => 'راهنمای نصب',
                'secondary_button_url' => '#',
                'show_dark_overlay' => true,
                'url' => '/launcher',
            ],
            [
                'image' => ['path' => 'demo/hero-2.svg'],
                'top_label_text' => 'به‌روزرسانی پچ',
                'square_label_text' => 'V5',
                'subtitle' => 'پچ کامل لیگ ایران و اروپا',
                'title' => 'پچ PGL V5 با بیش از ۲۰ بهبود منتشر شد',
                'description' => "ترکیب‌به‌روز ۳۶ تیم لیگ برتر، چهره‌های بازسازی‌شده و کیت‌های فصل جدید.",
                'primary_button_text' => 'مشاهده جزئیات پچ',
                'primary_button_url' => '#',
                'secondary_button_text' => 'خرید پچ',
                'secondary_button_url' => '#',
                'show_dark_overlay' => true,
                'url' => '#',
            ],
            [
                'image' => ['path' => 'demo/hero-3.svg'],
                'top_label_text' => 'اشتراک ویژه',
                'square_label_text' => 'VIP',
                'subtitle' => 'دسترسی نامحدود',
                'title' => 'با اشتراک VIP همه محصولات را یک‌جا داشته باشید',
                'description' => 'دانلود نامحدود همه پچ‌ها و مادها، پشتیبانی اختصاصی و تخفیف‌های ویژه اعضا.',
                'primary_button_text' => 'مشاهده مزایای VIP',
                'primary_button_url' => '/vip',
                'secondary_button_text' => '',
                'secondary_button_url' => '',
                'show_dark_overlay' => true,
                'url' => '/vip',
            ],
        ]);

        $home->set('download_products_title', 'محصولات دانلودی');
        $home->set('download_products_all_url', '#');
        $home->set('download_products_items', [
            [
                'title' => 'پچ PGL V5 — نسخه کامل',
                'short_description' => 'شامل لیگ ایران، لیگ‌های اروپا و همه آپدیت‌های فصل جدید.',
                'link' => '#',
                'image' => ['path' => 'demo/product-1.svg'],
                'label' => 'پرفروش‌ترین',
                'progress_percent' => 100,
                'enable_progress_percent' => false,
            ],
            [
                'title' => 'ماد آنلاکر FC26',
                'short_description' => 'باز کردن همه آیتم‌های قفل‌شده بازی با یک نصب ساده.',
                'link' => '#',
                'image' => ['path' => 'demo/product-2.svg'],
                'label' => 'ویژه اعضا',
                'progress_percent' => 100,
                'enable_progress_percent' => false,
            ],
            [
                'title' => 'کیت‌پک لیگ برتر ایران',
                'short_description' => 'کیت‌های رسمی و دقیق همه تیم‌های لیگ برتر همراه با شماره‌ها.',
                'link' => '#',
                'image' => ['path' => 'demo/product-3.svg'],
                'label' => 'جدید',
                'progress_percent' => 100,
                'enable_progress_percent' => false,
            ],
            [
                'title' => 'پک چهره بازیکنان فصل جدید',
                'short_description' => 'بازسازی چهره بیش از ۴۰۰ بازیکن با کیفیت بالا.',
                'link' => '#',
                'image' => ['path' => 'demo/product-4.svg'],
                'label' => 'در حال ساخت',
                'progress_percent' => 75,
                'enable_progress_percent' => true,
            ],
        ]);

        $home->set('special_posts_title', 'مطالب ویژه');
        $home->set('special_posts_items', [
            [
                'title' => 'آموزش کامل نصب پچ FC26',
                'short_description' => 'قدم‌به‌قدم تا اجرای بی‌نقص پچ روی ویندوز.',
                'link' => '#',
                'image' => ['path' => 'demo/post-1.svg'],
                'badge_text' => 'آموزش',
            ],
            [
                'title' => 'نقد و بررسی ماد آنلاکر',
                'short_description' => 'آیا ارزش خرید دارد؟ پاسخ کامل اینجاست.',
                'link' => '#',
                'image' => ['path' => 'demo/post-3.svg'],
                'badge_text' => 'بررسی',
            ],
            [
                'title' => 'انتشار پچ PGL V5',
                'short_description' => 'بیش از ۲۰ بهبود و لیگ ایران به‌روز.',
                'link' => '#',
                'image' => ['path' => 'demo/post-2.svg'],
                'badge_text' => 'خبر',
            ],
            [
                'title' => 'ترفندهای پاس‌کاری حرفه‌ای',
                'short_description' => 'افزایش دقت پاس با تنظیمات و تکنیک‌های جدید.',
                'link' => '#',
                'image' => ['path' => 'demo/post-6.svg'],
                'badge_text' => 'ترفند',
            ],
        ]);

        $home->set('subscription_title', 'اشتراک ویژه مودینگ‌وی');
        $home->set('subscription_subtitle', 'دانلود نامحدود، پشتیبانی اختصاصی و تخفیف‌های اعضا');
        $home->set('subscription_button_title', 'عضویت در باشگاه VIP');
        $home->set('subscription_button_url', '/vip');

        $home->set('products_title', 'محصولات فیزیکی');
        $home->set('products_all_products_url', '#');
        $home->set('products_items', [
            [
                'title' => 'دسته بازی حرفه‌ای MW Edition',
                'short_description' => 'ساخت ارگونومیک، مناسب طولانی‌مدت',
                'url' => '#',
                'button_title' => 'مشاهده و خرید',
                'label' => 'پیشنهاد ویژه',
                'image' => ['path' => 'demo/product-2.svg'],
            ],
            [
                'title' => 'ماوس‌پد XL مودینگ‌وی',
                'short_description' => 'سطح کنترل‌شده، لبه دوخت‌خورده',
                'url' => '#',
                'button_title' => 'مشاهده و خرید',
                'label' => 'جدید',
                'image' => ['path' => 'demo/product-3.svg'],
            ],
            [
                'title' => 'پیراهن رسمی تیم مودینگ‌وی',
                'short_description' => 'طراحی اختصاصی، سایزبندی کامل',
                'url' => '#',
                'button_title' => 'مشاهده و خرید',
                'label' => null,
                'image' => ['path' => 'demo/product-4.svg'],
            ],
            [
                'title' => 'استیکر پک چهره بازیکنان',
                'short_description' => 'مجموعه ۲۴ عددی با کیفیت چاپ بالا',
                'url' => '#',
                'button_title' => 'مشاهده و خرید',
                'label' => 'محبوب',
                'image' => ['path' => 'demo/product-1.svg'],
            ],
        ]);

        $home->set('videos_title', 'آموزش‌های ویدیویی');
        $home->set('videos_description', 'با آموزش‌های تصویری، همه قابلیت‌های لانچر و پچ‌ها را یاد بگیرید.');
        $home->set('videos_button_title', 'مشاهده همه ویدیوها');
        $home->set('videos_button_url', '#');
        $home->set('videos_category_ids', [(string) $categories['videos']->id]);

        $home->set('testimonials_title', 'نظر کاربران');
        $home->set('testimonials_users_avg_scores', '4.8');
        $home->set('testimonials_items', [
            ['title' => 'امیر رضایی', 'subtitle' => 'عضو VIP', 'image' => ['path' => 'demo/avatar-1.svg'], 'text' => 'لانچر واقعا کارم رو راحت کرده؛ همه پچ‌ها یک‌جا و بدون دردسر آپدیت می‌شن.', 'tags' => 'MW Launcher, VIP', 'special_experience' => false],
            ['title' => 'سارا محمدی', 'subtitle' => 'خریدار پچ PGL', 'image' => ['path' => 'demo/avatar-2.svg'], 'text' => 'کیت‌پک لیگ ایران دقیق و باکیفیت بود. پشتیبانی هم خیلی سریع جواب داد.', 'tags' => 'پچ PGL, پشتیبانی', 'special_experience' => true],
            ['title' => 'رضا کریمی', 'subtitle' => 'عضو باشگاه مخاطبان', 'image' => ['path' => 'demo/avatar-3.svg'], 'text' => 'سیستم امتیاز و پاداش خیلی جذابه؛ با دیدگاه‌ها امتیاز جمع کردم و تخفیف گرفتم.', 'tags' => 'باشگاه مخاطبان', 'special_experience' => false],
            ['title' => 'نیما احمدی', 'subtitle' => 'کاربر لانچر', 'image' => ['path' => 'demo/avatar-4.svg'], 'text' => 'دانلودها پایدار و سریعه. نسخه جدید لانچر خیلی بهتر از قبل شده.', 'tags' => 'دانلود', 'special_experience' => false],
        ]);

        $home->set('projects_title', 'پروژه‌های در حال توسعه');
        $home->set('projects_items', [
            ['project_id' => (string) $projects[0]->id],
            ['project_id' => (string) $projects[1]->id],
        ]);

        $home->set('posts_items', [
            [
                'main_title' => 'تازه‌ترین مطالب',
                'main_count' => 4,
                'main_category_ids' => [(string) $categories['news']->id, (string) $categories['tutorials']->id],
                'list_box_1_title' => 'محبوب‌ترین آموزش‌ها',
                'list_box_1_count' => 5,
                'list_box_1_category_ids' => [(string) $categories['tutorials']->id, (string) $categories['tricks']->id],
                'list_box_2_title' => 'دانلودهای جدید',
                'list_box_2_count' => 5,
                'list_box_2_category_ids' => [(string) $categories['downloads']->id],
                'sort_order' => 0,
            ],
        ]);

        $home->set('services_options_items', [
            ['title' => 'دانلود مستقیم و پرسرعت', 'description' => 'همه فایل‌ها روی سرور داخلی با لینک اختصاصی.', 'icon' => 'download-04', 'link' => '#'],
            ['title' => 'پشتیبانی واقعی', 'description' => 'تیم پشتیبانی در تیکت‌ها میان‌روزی پاسخ می‌دهد.', 'icon' => 'life-buoy-01', 'link' => '#'],
            ['title' => 'خرید مطمئن', 'description' => 'پرداخت امن و امکان بازپرداخت طبق قوانین.', 'icon' => 'shield-01', 'link' => '#'],
            ['title' => 'به‌روزرسانی دائم', 'description' => 'محصولات خریداری‌شده همیشه به‌روزرسانی رایگان دارند.', 'icon' => 'alert-02', 'link' => '#'],
        ]);
    }

    private function seedGeneralSettings(): void
    {
        $general = app(GeneralSettings::class);
        $general->set('title', 'مودینگ‌وی');
        $general->set('subtitle', 'مرجع پچ و ماد بازی‌های فوتبال');
        $general->set('default_payment_gateway', 'wallet');
        // ارائه‌دهنده پیامکِ محیط توسعه؛ OTP در لاگ ثبت و در باکس «کد آزمایشی» نمایش داده می‌شود.
        $general->set('default_sms_provider', \App\Infrastructure\Services\Sms\Providers\LogSmsProvider::class);
    }

    private function seedLoyalty(User $user): void
    {
        // اقتصاد پایه باشگاه: ۱۰۰ امتیاز = ۲,۰۰۰ تومان، حداقل تبدیل ۵۰۰، سقف روزانه ۲۰,۰۰۰
        app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class)->set('point_conversion_enabled', true);
        app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class)->set('point_conversion_points_per_unit', 100);
        app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class)->set('point_conversion_wallet_amount_per_unit', 2000);
        app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class)->set('point_conversion_minimum_points', 500);
        app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class)->set('point_conversion_maximum_points_per_request', 10000);
        app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class)->set('point_conversion_daily_points_limit', 20000);
        app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class)->set('max_credit_percent_per_order', 20);

        $this->seedAffiliateDemo($user);

        // «ماندن در سایت» فارم می‌شود؛ در این اقتصاد حذف می‌شود.
        EarningRule::query()->where('code', 'demo_time_spent')->update(['is_active' => false]);

        $rules = [
            ['registration', 'هدیه خوش‌آمدگویی', ['points' => 300], 100],
            ['daily_visit', 'بازدید روزانه', ['points' => 20], 90],
            ['comment', 'ارسال دیدگاه مفید', ['points' => 30, 'daily_limit' => 2], 80],
            // اقتصاد خرید: هر ۱۰,۰۰۰ تومان = ۱۰ امتیاز پایه (ضریب سطح در پردازش اعمال می‌شود)
            ['purchase', 'امتیاز خرید', ['points' => 10, 'amount_step' => 10000], 70],
            ['membership_anniversary', 'سالگرد عضویت', ['points' => 500], 50],
        ];

        $ruleModels = [];

        foreach ($rules as [$type, $title, $config, $priority]) {
            $ruleModels[$type] = EarningRule::query()->firstOrCreate(
                ['code' => 'demo_' . $type],
                [
                    'title' => $title,
                    'type' => EarningRuleTypeEnum::from($type),
                    'is_active' => true,
                    'priority' => $priority,
                    'config' => $config,
                ],
            );
        }

        $offers = [
            ['demo_coupon_10', 'کد تخفیف ۱۰٪ محصولات دانلودی', 'تخفیف دائمی روی همه محصولات دانلودی فروشگاه.', 400, ['coupon_type' => 'percentage', 'coupon_value' => 10, 'expires_after_days' => 90], 1],
            ['demo_coupon_fixed', 'کد تخفیف ۵۰ هزار تومانی', 'تخفیف مبلغی روی سبد خرید بالای ۳۰۰ هزار تومان.', 700, ['coupon_type' => 'fixed_amount', 'coupon_value' => 50000, 'expires_after_days' => 60], 2],
            ['demo_coupon_vip', 'یک ماه اشتراک VIP', 'دسترسی کامل یک‌ماهه به همه مزایای باشگاه ویژه.', 1200, ['coupon_type' => 'fixed_amount', 'coupon_value' => 99000, 'expires_after_days' => 30], 3],
            ['demo_coupon_mystery', 'جعبه معمایی امتیاز', 'یک جایزه تصادفی بین ۱۰ تا ۱۰۰ هزار تومان اعتبار کیف پول.', 250, ['coupon_type' => 'fixed_amount', 'coupon_value' => 10000, 'expires_after_days' => 45], 4],
        ];

        foreach ($offers as [$code, $title, $description, $cost, $config, $sort]) {
            RewardOffer::query()->firstOrCreate(
                ['code' => $code],
                [
                    'title' => $title,
                    'description' => $description,
                    'type' => RewardTypeEnum::COUPON,
                    'points_cost' => $cost,
                    'is_active' => true,
                    'sort_order' => $sort,
                    'config' => $config,
                ],
            );
        }

        Wallet::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'active'],
        );

        $account = PointAccount::query()->firstOrCreate(
            ['user_id' => $user->id],
        );

        if ($account->entries()->count() > 0) {
            return;
        }

        $entries = [
            [$ruleModels['registration'], 'credit', 50, 'هدیه خوش‌آمدگویی', 40],
            [$ruleModels['daily_visit'], 'credit', 5, 'بازدید روزانه', 34],
            [$ruleModels['comment'], 'credit', 10, 'دیدگاه روی «آموزش نصب پچ FC26»', 28],
            [$ruleModels['comment'], 'credit', 10, 'دیدگاه روی «نقد ماد آنلاکر»', 25],
            [$ruleModels['purchase'], 'credit', 24, 'امتیاز خرید سفارش #1024', 20],
            [null, 'debit', -120, 'دریافت کد تخفیف ۱۰٪', 15],
            [$ruleModels['time_spent'] ?? null, 'credit', 15, '۳۰ دقیقه فعالیت در سایت', 10],
            [$ruleModels['daily_visit'], 'credit', 5, 'بازدید روزانه', 6],
            [$ruleModels['comment'], 'credit', 10, 'دیدگاه روی «ترفندهای پاس‌کاری»', 3],
            [$ruleModels['daily_visit'], 'credit', 5, 'بازدید روزانه', 1],
        ];

        $balance = 0;

        foreach ($entries as $index => [$rule, $direction, $amount, $description, $daysAgo]) {
            $balanceBefore = $balance;
            $balance += $amount;

            PointLedgerEntry::query()->create([
                'point_account_id' => $account->id,
                'earning_rule_id' => $rule?->id,
                'type' => $direction === 'credit' ? PointEntryTypeEnum::EARN : PointEntryTypeEnum::SPEND,
                'direction' => $direction === 'credit' ? PointEntryDirectionEnum::CREDIT : PointEntryDirectionEnum::DEBIT,
                'reason' => $rule?->code ?? 'reward_redeem',
                'amount' => abs($amount),
                'balance_before' => $balanceBefore,
                'balance_after' => $balance,
                'description' => $description,
                'idempotency_key' => 'demo-entry-' . $account->id . '-' . $index,
                'idempotency_fingerprint' => hash('sha256', 'demo-entry-' . $account->id . '-' . $index),
                'occurred_at' => CarbonImmutable::now()->subDays($daysAgo)->setTime(12, rand(0, 59)),
            ]);
        }

        $account->update([
            'balance' => $balance,
            'lifetime_earned' => 144,
            'lifetime_spent' => 120,
        ]);
    }

    /**
     * دموی همکاری در فروش: تنظیمات سراسری (کمیسیون ۲۵٪)، کد دمو برای کاربر نمونه
     * و کوپن هم‌نام با کد او تا مسیر «خرید با کد تخفیف همکار» هم قابل تست باشد.
     */
    private function seedAffiliateDemo(User $user): void
    {
        $affiliateSettings = app(\App\Infrastructure\Services\Setting\Groups\AffiliateSettings::class);
        $affiliateSettings->set('affiliate_enabled', true);
        $affiliateSettings->set('default_commission_type', 'percent');
        $affiliateSettings->set('default_commission_value', 11);
        $affiliateSettings->set('default_attribution_days', 90);
        $affiliateSettings->set('default_cookie_days', 30);
        $affiliateSettings->set('default_min_withdrawal_amount', 100000);
        $affiliateSettings->set('referral_bonus_enabled', true);
        $affiliateSettings->set('referral_bonus_referrer_points', 1000);
        $affiliateSettings->set('referral_bonus_referee_points', 500);
        $affiliateSettings->set('referral_bonus_min_order_amount', 100000);

        $affiliate = \App\Domain\Affiliate\Models\Affiliate::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'code' => 'MWDEMO25',
                'is_active' => true,
                'commission_type' => \App\Domain\Affiliate\Enums\AffiliateCommissionTypeEnum::PERCENT,
                'commission_value' => 25,
                'cookie_days' => 30,
                'attribution_days' => 90,
                'min_withdrawal_amount' => 100000,
                'notes' => 'همکار دمو — ساخته‌شده توسط DemoContentSeeder',
            ],
        );

        // کوپن هم‌نام با کد همکار؛ خرید با این کد به‌طور خودکار به همکار منتسب می‌شود.
        \App\Domain\Promotion\Models\Coupon::query()->firstOrCreate(
            ['code' => $affiliate->code],
            [
                'title' => 'کد تخفیف همکار دمو (۱۰٪)',
                'type' => \App\Domain\Promotion\Enums\CouponTypeEnum::PERCENTAGE,
                'value' => 10,
                'scope' => \App\Domain\Promotion\Enums\CouponScopeEnum::ORDER,
                'status' => \App\Domain\Promotion\Enums\CouponStatusEnum::ACTIVE,
                'applies_to_all_users' => true,
                'created_by_user_id' => User::query()->where('email', 'admin@moddingway.ir')->value('id'),
            ],
        );
    }
}
