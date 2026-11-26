<?php

namespace Tests\Feature\Content;

use App\Domain\Content\Models\LandingPage;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * لندینگ‌ساز: ساخت از پنل با تم و بلوک‌ها، نمایش عمومی، پیش‌نویس/پیش‌نمایش، بازه‌ی نمایش.
 */
class LandingPageBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_builds_a_themed_landing_with_every_block_type(): void
    {
        $this->actingAs($this->admin())->withoutMiddleware(EnsureUserIsAdmin::class);

        $this->post(route('admin.landing-pages.store'), [
            'title' => 'جشنواره یلدا',
            'slug' => 'yalda-1405',
            'status' => 'published',
            'theme' => ['preset' => 'custom', 'primary' => '#123456', 'accent' => '#abcdef', 'mode' => 'light', 'pattern' => 'dots'],
            'seo' => ['meta_title' => 'یلدا با Cardiware', 'meta_description' => 'تخفیف ویژه', 'noindex' => '0'],
            'blocks' => [
                ['type' => 'hero', 'title' => 'یلدای Cardiware', 'subtitle' => 'فقط سه روز', 'body' => 'تا ۵۰٪ تخفیف', 'button_text' => 'خرید', 'button_link' => '/products/'],
                ['type' => 'features', 'title' => 'چرا ما', 'items_text' => "🚀 | نصب سریع | با لانچر\n🛡️ | پشتیبانی | همیشه"],
                ['type' => 'video', 'title' => 'تریلر', 'video_url' => 'https://www.aparat.com/v/abc123'],
                ['type' => 'coupon', 'title' => 'کد شما', 'coupon_code' => 'YALDA50'],
                ['type' => 'faq', 'title' => 'سوالات', 'items_text' => 'چطور نصب کنم؟ | با لانچر'],
                ['type' => 'countdown', 'title' => 'تا پایان', 'countdown_at' => now()->addDays(2)->format('Y-m-d\TH:i')],
                ['type' => 'cta', 'title' => 'همین حالا', 'button_text' => 'بزن بریم', 'button_link' => 'javascript:alert(1)'],
                ['type' => 'gallery', 'items_text' => "https://example.test/1.jpg | عکس ۱\njavascript:alert(1)"],
                ['type' => 'products', 'title' => 'محصولات', 'product_ids' => '999'],
                ['type' => 'text', 'title' => 'متن', 'body' => '<script>alert(1)</script>'],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $page = LandingPage::query()->sole();
        $this->assertSame('yalda-1405', $page->slug);
        $this->assertSame('#123456', $page->resolvedTheme()['primary']);
        $this->assertSame('light', $page->resolvedTheme()['mode']);
        $this->assertCount(10, $page->blocks);
        // لینک ناامن حذف می‌شود؛ دکمه بدون لینک نمایش داده نمی‌شود.
        $this->assertArrayNotHasKey('button_link', $page->blocks[6]);

        $html = $this->get(route('landing-pages.show', 'yalda-1405'))
            ->assertSuccessful()
            ->assertSee('یلدای Cardiware')
            ->assertSee('--lp-primary: #123456', false)
            ->assertSee('YALDA50')
            ->assertSee('https://www.aparat.com/video/video/embed/videohash/abc123/vt/frame', false)
            ->assertSee('نصب سریع')
            ->assertSee('چطور نصب کنم؟')
            ->assertSee('data-lp-countdown', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:alert(1)', false)
            ->getContent();

        $this->assertStringContainsString('<meta name="description" content="تخفیف ویژه">', $html);
        $this->assertSame(1, (int) $page->fresh()->views_count);
    }

    public function test_draft_is_hidden_from_visitors_but_previewable_by_admin(): void
    {
        LandingPage::query()->create(['title' => 'پیش‌نویس', 'slug' => 'draft-page', 'status' => 'draft', 'theme' => ['preset' => 'pgl'], 'blocks' => [['type' => 'hero', 'title' => 'سلام']]]);

        $this->get(route('landing-pages.show', 'draft-page'))->assertNotFound();
        $this->get(route('landing-pages.show', ['landingSlug' => 'draft-page', 'preview' => 1]))->assertNotFound();

        $this->actingAs($this->admin())
            ->get(route('landing-pages.show', ['landingSlug' => 'draft-page', 'preview' => 1]))
            ->assertSuccessful()
            ->assertSee('پیش‌نمایش مدیر')
            ->assertSee('noindex', false);
    }

    public function test_published_page_is_only_visible_inside_its_schedule(): void
    {
        LandingPage::query()->create(['title' => 'گذشته', 'slug' => 'ended', 'status' => 'published', 'ends_at' => now()->subDay(), 'blocks' => []]);
        LandingPage::query()->create(['title' => 'آینده', 'slug' => 'upcoming', 'status' => 'published', 'starts_at' => now()->addDay(), 'blocks' => []]);

        $this->get(route('landing-pages.show', 'ended'))->assertNotFound();
        $this->get(route('landing-pages.show', 'upcoming'))->assertNotFound();
    }

    public function test_duplicate_makes_a_draft_copy_with_a_free_slug(): void
    {
        $page = LandingPage::query()->create(['title' => 'اصلی', 'slug' => 'main', 'status' => 'published', 'blocks' => [['type' => 'hero', 'title' => 'x']]]);
        $this->actingAs($this->admin())->withoutMiddleware(EnsureUserIsAdmin::class);

        $this->post(route('admin.landing-pages.duplicate', $page))->assertRedirect();

        $copy = LandingPage::query()->whereKeyNot($page->id)->sole();
        $this->assertSame('main-copy', $copy->slug);
        $this->assertSame('draft', $copy->status);
        $this->get(route('admin.landing-pages.edit', $copy))->assertSuccessful()->assertSee('اصلی (کپی)');
        $this->get(route('admin.landing-pages.index'))->assertSuccessful()->assertSee('/landing/main-copy');
    }

    private function admin(): User
    {
        $roleId = DB::table('roles')->insertGetId(['title' => 'Admin ' . uniqid(), 'is_super_admin' => true, 'created_at' => now(), 'updated_at' => now()]);

        return User::factory()->create(['role_id' => $roleId]);
    }
}
