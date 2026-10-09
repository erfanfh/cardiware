<?php

namespace Tests\Feature\Career;

use App\Domain\Career\Models\CareerApplication;
use App\Domain\Career\Models\CareerPosition;
use App\Domain\Content\Models\Page;
use App\Domain\Icon\Models\Icon;
use App\Domain\Icon\Models\IconSet;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\ForceTrailingSlash;
use App\Infrastructure\Services\Setting\Groups\CareersSettings;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerPublicApplicationTest extends TestCase
{
    use RefreshDatabase;

    private ?string $careerStorageRoot = null;
    private ?Page $careerPage = null;

    protected function tearDown(): void
    {
        if ($this->careerStorageRoot && str_starts_with($this->careerStorageRoot, sys_get_temp_dir())) {
            (new Filesystem)->deleteDirectory($this->careerStorageRoot);
        }

        parent::tearDown();
    }

    private function fakeResumeStorage(): void
    {
        $this->careerStorageRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moddingway-career-'.bin2hex(random_bytes(8));
        Storage::set('local', Storage::build([
            'driver' => 'local',
            'root' => $this->careerStorageRoot,
            'throw' => true,
        ]));
    }

    private function position(string $title = 'تولید محتوا', string $status = 'active'): CareerPosition
    {
        return CareerPosition::create([
            'title' => $title, 'slug' => 'job-'.uniqid(),
            'summary' => 'همکاری در تیم مودینگ وی', 'status' => $status,
            'department' => 'محتوا',
        ]);
    }

    private function page(): Page
    {
        if ($this->careerPage) {
            return $this->careerPage;
        }

        $this->careerPage = Page::create([
            'author_id' => User::factory()->create()->id,
            'title' => 'همکاری با مودینگ وی',
            'slug' => 'join-moddingway',
            'body' => '',
            'status' => 'active',
            'template' => 'careers',
            'published_at' => now()->subMinute(),
        ]);
        $this->careerPage->slugs()->create(['slug' => $this->careerPage->slug, 'is_current' => true]);

        return $this->careerPage;
    }

    private function payload(CareerPosition $position): array
    {
        return [
            'full_name' => 'علی رضایی',
            'career_page_id' => (string) $this->page()->id,
            'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
            'email' => 'ali@example.com',
            'career_position_id' => (string) $position->id,
            'preferred_cooperation_type' => 'پاره‌وقت',
            'portfolio_url' => 'https://example.com/portfolio',
            'skills_and_experience' => 'بیش از سه سال تجربه تولید محتوای بازی و پشتیبانی کاربران دارم.',
            'consent' => '1',
        ];
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role_id' => Role::factory()->create(['is_super_admin' => true])->id,
        ]);
    }

    public function test_page_uses_settings_and_only_available_positions(): void
    {
        $page = $this->page();
        $active = $this->position('موقعیت فعال');
        $this->position('موقعیت بسته', 'closed');
        $this->position('موقعیت آینده')->update(['published_at' => now()->addDay()]);
        app(CareersSettings::class)->set('hero_title', 'عنوان ویژه همکاری');
        $this->withoutMiddleware(ForceTrailingSlash::class);

        $this->assertSame('صفحه استخدام و همکاری', app(\App\Application\Content\Services\PageTemplateService::class)->all()['careers']);
        $this->get(route('contents.detail', $page->slug))->assertOk()
            ->assertSee('عنوان ویژه همکاری')
            ->assertSee($active->title)
            ->assertDontSee('موقعیت بسته')
            ->assertDontSee('موقعیت آینده')
            ->assertSee('name="career_page_id" value="'.$page->id.'"', false)
            ->assertSee($page->url.'?position='.$active->id.'#apply', false)
            ->assertSee('name="resume"', false)
            ->assertDontSee('minlength="20"', false)
            ->assertDontSee('maxlength="10000"', false);
        $this->get('/careers')->assertNotFound();
    }

    public function test_selected_benefit_icon_can_be_saved_and_rendered(): void
    {
        $set = IconSet::create(['key' => 'career-test', 'label' => 'Career test']);
        Icon::create([
            'icon_set_id' => $set->id,
            'name' => 'game',
            'full_name' => 'career-test:game',
            'search_text' => 'game',
            'svg' => '<svg data-career-test-icon="1" viewBox="0 0 24 24"></svg>',
            'import_batch' => '00000000-0000-4000-8000-000000000001',
        ]);

        $this->actingAs($this->admin())->withoutMiddleware(ForceTrailingSlash::class);
        $this->get(route('admin.settings.careers'))->assertOk()
            ->assertSee('mwRepeaterIconPicker', false);

        $values = CareersSettings::defaults();
        $values['benefits_items'][0]['icon'] = 'career-test:game';
        $this->put(route('admin.settings.careers.store'), $values)->assertRedirect();

        $this->get(route('contents.detail', $this->page()->slug))->assertOk()
            ->assertSee('data-career-test-icon="1"', false)
            ->assertSee('🚀');
    }

    public function test_guest_can_send_private_resume_and_admin_reading_clears_unseen_count(): void
    {
        $this->fakeResumeStorage();
        $position = $this->position();
        $payload = $this->payload($position);
        $payload['resume'] = UploadedFile::fake()->create('رزومه.pdf', 80, 'application/pdf');
        $this->withoutMiddleware(ForceTrailingSlash::class);

        $this->post(route('careers.apply'), $payload)
            ->assertRedirect($this->page()->url.'#apply')
            ->assertSessionHas('career_application_success');
        $this->get(route('contents.detail', $this->page()->slug))->assertOk()
            ->assertSee('data-career-success-modal', false)
            ->assertSee('درخواست شما ثبت شد')
            ->assertSee('درخواست همکاری شما با موفقیت ثبت شد');
        $this->get(route('contents.detail', $this->page()->slug))->assertOk()
            ->assertDontSee('data-career-success-modal', false);
        $application = CareerApplication::firstOrFail();
        $this->assertSame('09123456789', $application->phone);
        $this->assertSame($position->title, $application->position_title);
        $this->assertSame('new', $application->status);
        $this->assertNull($application->viewed_at);
        Storage::disk('local')->assertExists($application->resume_path);
        $this->assertStringStartsWith('careers/resumes/', $application->resume_path);
        $this->assertSame('رزومه.pdf', $application->resume_original_name);
        $this->get(route('admin.career-applications.resume', $application))->assertRedirect();

        $this->actingAs($this->admin());
        $unseenList = $this->get(route('admin.career-applications.index', ['viewed' => 'unseen']))->assertOk()->assertSee('علی رضایی');
        $this->assertMatchesRegularExpression('/درخواست‌های همکاری<\/span>\s*<span[^>]*>\s*1\s*<\/span>/u', $unseenList->getContent());
        $this->get(route('admin.career-applications.resume', $application))->assertOk();
        $this->assertNotNull($application->fresh()->viewed_at);
        $this->get(route('admin.career-applications.show', $application))->assertOk()->assertSee('علی رضایی');
        $this->assertNotNull($application->fresh()->viewed_at);
        $seenList = $this->get(route('admin.career-applications.index', ['viewed' => 'unseen']))->assertOk()->assertDontSee('علی رضایی');
        $this->assertDoesNotMatchRegularExpression('/درخواست‌های همکاری<\/span>\s*<span[^>]*>\s*1\s*<\/span>/u', $seenList->getContent());
    }

    public function test_closed_position_and_invalid_file_are_rejected_without_saving(): void
    {
        $this->fakeResumeStorage();
        $closed = $this->position('بسته', 'closed');
        $this->post(route('careers.apply'), $this->payload($closed))
            ->assertSessionHasErrors('career_position_id');
        $active = $this->position();
        $payload = $this->payload($active);
        $payload['resume'] = UploadedFile::fake()->create('script.php', 4, 'application/x-php');
        $this->post(route('careers.apply'), $payload)->assertSessionHasErrors('resume');
        $this->assertSame(0, CareerApplication::count());
    }

    public function test_application_cannot_use_a_page_with_another_template(): void
    {
        $position = $this->position();
        $otherPage = Page::create([
            'author_id' => User::factory()->create()->id,
            'title' => 'برگه دیگر',
            'slug' => 'another-page',
            'body' => '',
            'status' => 'active',
            'template' => 'team',
            'published_at' => now()->subMinute(),
        ]);
        $payload = $this->payload($position);
        $payload['career_page_id'] = (string) $otherPage->id;

        $this->post(route('careers.apply'), $payload)->assertSessionHasErrors('career_page_id');
        $this->assertSame(0, CareerApplication::count());
    }

    public function test_general_application_can_be_sent_without_a_file(): void
    {
        $position = $this->position();
        $payload = $this->payload($position);
        $payload['career_position_id'] = 'general';
        $payload['phone'] = '09123456789';
        $this->post(route('careers.apply'), $payload)->assertRedirect()->assertSessionHas('career_application_success');

        $application = CareerApplication::firstOrFail();
        $this->assertNull($application->career_position_id);
        $this->assertNull($application->resume_path);
        $this->assertSame('new', $application->status);
    }
}
