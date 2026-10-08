<?php

namespace Tests\Feature\Career;

use App\Domain\Career\Models\CareerApplication;
use App\Domain\Career\Models\CareerPosition;
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
            'department' => 'محتوا', 'sort_order' => 1,
        ]);
    }

    private function payload(CareerPosition $position): array
    {
        return [
            'full_name' => 'علی رضایی',
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
        $active = $this->position('موقعیت فعال');
        $this->position('موقعیت بسته', 'closed');
        $this->position('موقعیت آینده')->update(['published_at' => now()->addDay()]);
        $this->position('موقعیت منقضی')->update(['closes_at' => now()->subDay()]);
        app(CareersSettings::class)->set('hero_title', 'عنوان ویژه همکاری');
        $this->withoutMiddleware(ForceTrailingSlash::class);

        $this->get(route('careers.index'))->assertOk()
            ->assertSee('عنوان ویژه همکاری')
            ->assertSee($active->title)
            ->assertDontSee('موقعیت بسته')
            ->assertDontSee('موقعیت آینده')
            ->assertDontSee('موقعیت منقضی')
            ->assertSee('name="resume"', false);
    }

    public function test_guest_can_send_private_resume_and_admin_reading_clears_unseen_count(): void
    {
        $this->fakeResumeStorage();
        $position = $this->position();
        $payload = $this->payload($position);
        $payload['resume'] = UploadedFile::fake()->create('رزومه.pdf', 80, 'application/pdf');
        $this->withoutMiddleware(ForceTrailingSlash::class);

        $this->post(route('careers.apply'), $payload)->assertRedirect()->assertSessionHas('career_application_success');
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
