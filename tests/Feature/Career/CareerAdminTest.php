<?php

namespace Tests\Feature\Career;

use App\Domain\Career\Models\CareerApplication;
use App\Domain\Career\Models\CareerPosition;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\ForceTrailingSlash;
use App\Infrastructure\Services\Setting\Groups\CareersSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::factory()->create(['is_super_admin' => true]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_requirements_textarea_renders_on_create_and_edit(): void
    {
        $this->actingAs($this->admin())->withoutMiddleware(ForceTrailingSlash::class);

        $createHtml = $this->get(route('admin.career-positions.create'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<textarea\b[^>]*name="requirements_text"[^>]*><\/textarea>/s', $createHtml);
        $this->assertStringNotContainsString('?? requirements', $createHtml);

        $position = CareerPosition::create([
            'title' => 'طراح', 'slug' => 'designer', 'summary' => 'شرح آزمایشی',
            'requirements' => ['فیگما', 'نمونه‌کار'], 'status' => 'draft',
        ]);
        $editHtml = $this->get(route('admin.career-positions.edit', $position))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<textarea\b[^>]*name="requirements_text"[^>]*>فیگما\Rنمونه‌کار<\/textarea>/u', $editHtml);
    }

    public function test_position_title_and_slug_use_the_post_editor_layout_and_check_current_positions(): void
    {
        $this->actingAs($this->admin())->withoutMiddleware(ForceTrailingSlash::class);

        $createHtml = $this->get(route('admin.career-positions.create'))->assertOk()->getContent();
        $this->assertStringContainsString('mwWpSlugEditor', $createHtml);
        $this->assertStringContainsString('پیوند یکتا:', $createHtml);
        $this->assertStringContainsString('name="slug" x-model="slug"', $createHtml);
        $this->assertStringNotContainsString('career-position-slug', $createHtml);

        $position = CareerPosition::create([
            'title' => 'طراح', 'slug' => 'designer', 'summary' => 'شرح آزمایشی',
            'status' => 'draft',
        ]);

        $this->get(route('admin.career-positions.check-slug', ['slug' => 'designer']))
            ->assertOk()->assertJsonPath('available', false)
            ->assertJsonPath('suggested_slug', 'designer-2');
        $this->get(route('admin.career-positions.check-slug', ['slug' => 'designer', 'ignore' => $position->id]))
            ->assertOk()->assertJsonPath('available', true);

        $editHtml = $this->get(route('admin.career-positions.edit', $position))->assertOk()->getContent();
        $this->assertStringContainsString('mwWpSlugEditor', $editHtml);
        $this->assertStringContainsString('پیوند یکتا:', $editHtml);
    }

    public function test_blank_slug_keeps_persian_title_characters_like_the_post_editor(): void
    {
        $this->actingAs($this->admin())->withoutMiddleware(ForceTrailingSlash::class);

        $payload = [
            'title' => 'طراح رابط کاربری ۲',
            'slug' => '',
            'summary' => 'شرح آزمایشی',
            'status' => 'draft',
        ];

        $this->post(route('admin.career-positions.store'), $payload)->assertRedirect();
        $this->assertDatabaseHas('career_positions', ['slug' => 'طراح-رابط-کاربری-2']);

        $this->post(route('admin.career-positions.store'), $payload)->assertRedirect();
        $this->assertDatabaseHas('career_positions', ['slug' => 'طراح-رابط-کاربری-2-2']);
    }

    public function test_positions_can_be_created_filtered_updated_and_closed(): void
    {
        $this->actingAs($this->admin())->withoutMiddleware(ForceTrailingSlash::class);

        $this->get(route('admin.career-positions.create'))->assertOk()
            ->assertSee('name="published_at"', false)
            ->assertDontSee('name="sort_order"', false)
            ->assertDontSee('name="closes_at"', false);
        $publicationDate = now()->addDay()->startOfMinute();
        $payload = [
            'title' => 'طراح رابط کاربری', 'slug' => 'ui-designer',
            'department' => 'طراحی', 'employment_type' => 'پروژه‌ای',
            'cooperation_type' => 'ریموت', 'location' => 'دورکاری',
            'level' => 'میدلول', 'summary' => 'طراحی تجربه کاربری محصولات',
            'requirements_text' => "فیگما\nنمونه کار\n", 'status' => 'active',
            'published_at' => jalali_date($publicationDate),
        ];

        $this->post(route('admin.career-positions.store'), $payload)->assertRedirect();
        $position = CareerPosition::firstOrFail();
        $this->assertSame(['فیگما', 'نمونه کار'], $position->requirements);
        $this->assertSame($publicationDate->format('Y-m-d H:i'), $position->published_at->format('Y-m-d H:i'));
        $this->assertFalse(CareerPosition::publiclyAvailable()->whereKey($position->id)->exists());
        $this->post(route('admin.career-positions.store'), array_replace($payload, ['slug' => null]))->assertRedirect();
        $this->assertSame(2, CareerPosition::query()->count());
        $this->assertNotSame($position->slug, CareerPosition::query()->latest('id')->firstOrFail()->slug);
        $this->get(route('admin.career-positions.index', ['department' => 'طراحی']))->assertOk()->assertSee('طراح رابط کاربری');
        $this->get(route('admin.career-positions.edit', $position))->assertOk()
            ->assertSee('name="published_at"', false)
            ->assertDontSee('name="sort_order"', false)
            ->assertDontSee('name="closes_at"', false);

        $this->put(route('admin.career-positions.update', $position), array_replace($payload, ['status' => 'closed']))->assertRedirect();
        $this->assertSame('closed', $position->fresh()->status);
        $this->assertFalse(CareerPosition::publiclyAvailable()->whereKey($position->id)->exists());

        CareerApplication::create([
            'career_position_id' => $position->id, 'position_title' => $position->title,
            'full_name' => 'متقاضی', 'phone' => '09120000000',
            'skills_and_experience' => 'آزمایش',
        ]);
        $this->delete(route('admin.career-positions.destroy', $position))->assertRedirect();
        $this->assertSame(1, CareerApplication::query()->count());
    }

    public function test_career_settings_and_application_review_are_managed_separately(): void
    {
        $this->actingAs($this->admin())->withoutMiddleware(ForceTrailingSlash::class);
        $this->get(route('admin.settings.careers'))->assertOk()->assertSee('تنظیمات صفحه استخدام');
        $values = CareersSettings::defaults();
        $values['hero_title'] = 'عنوان تازه استخدام';
        $this->put(route('admin.settings.careers.store'), $values)->assertRedirect();
        $this->assertSame('عنوان تازه استخدام', app(CareersSettings::class)->get('hero_title'));

        $application = CareerApplication::create([
            'full_name' => 'کاربر آزمایشی', 'phone' => '09120000000',
            'skills_and_experience' => 'تجربه تولید محتوا', 'status' => 'new',
        ]);
        $this->get(route('admin.career-applications.index'))->assertOk()->assertSee('کاربر آزمایشی');
        $this->get(route('admin.career-applications.show', $application))->assertOk()->assertSee('تجربه تولید محتوا');
        $this->assertNotNull($application->fresh()->viewed_at);
        $this->put(route('admin.career-applications.update', $application), [
            'status' => 'reviewing', 'internal_notes' => 'برای مصاحبه پیگیری شود',
        ])->assertRedirect();
        $this->assertSame('reviewing', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->reviewed_at);
    }

    public function test_application_access_requires_its_own_permission(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::query()->create([
            'group' => 'admin', 'ability' => 'display', 'label' => 'پنل',
        ]);
        $role->permissions()->attach($permission);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]))
            ->withoutMiddleware(ForceTrailingSlash::class);

        $this->get(route('admin.career-applications.index'))->assertForbidden();
        $this->get(route('admin.career-positions.index'))->assertForbidden();
        $this->get(route('admin.career-positions.check-slug', ['slug' => 'designer']))->assertForbidden();
        $this->get(route('admin.settings.careers'))->assertForbidden();
    }
}
