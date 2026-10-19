<?php

namespace Tests\Feature\Notification;

use App\Application\Notification\GetAccountAnnouncementsAction;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Notification\Models\AccountAnnouncement;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountAnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->freezeTime();
        BaseSettings::clearResolvedGroups();
    }

    public function test_admin_can_render_create_edit_disable_and_delete_announcements(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.account-announcements.index'))
            ->assertOk()->assertViewHas('hasActiveFilters', false)->assertSee('اطلاعیه‌ای پیدا نشد')->assertSee('اطلاعیه‌های حساب کاربری');
        $this->get(route('admin.account-announcements.create'))->assertOk()->assertSee('متن اطلاعیه');
        $this->get(route('admin.settings.notifications'))->assertOk()->assertSee('مدیریت اطلاعیه‌های حساب کاربری');
        $this->post(route('admin.account-announcements.store'), $this->payload())
            ->assertRedirect(route('admin.account-announcements.index'))->assertSessionHasNoErrors();
        $announcement = AccountAnnouncement::query()->sole();
        $this->assertSame('اطلاعیه انتشار', $announcement->title);
        $this->assertTrue($announcement->is_active);
        $this->get(route('admin.account-announcements.edit', $announcement))->assertOk()->assertSee('اطلاعیه انتشار');
        $this->put(route('admin.account-announcements.update', $announcement), $this->payload([
            'title' => 'عنوان ویرایش‌شده', 'body' => "خط اول\nخط دوم", 'is_active' => '0', 'type' => 'warning',
        ]))->assertRedirect(route('admin.account-announcements.index'))->assertSessionHasNoErrors();
        $this->assertFalse($announcement->fresh()->is_active);
        $this->assertSame('warning', $announcement->fresh()->type);
        $this->assertSame("خط اول\nخط دوم", $announcement->fresh()->body);
        $this->get(route('admin.account-announcements.index', ['title' => 'ویرایش‌شده', 'is_active' => '0']))
            ->assertOk()->assertViewHas('hasActiveFilters', true)->assertSee('عنوان ویرایش‌شده')->assertSee('غیرفعال');
        $this->delete(route('admin.account-announcements.destroy', $announcement))
            ->assertRedirect(route('admin.account-announcements.index'));
        $this->assertDatabaseMissing('account_announcements', ['id' => $announcement->id]);
    }

    public function test_all_existing_and_new_users_see_the_shared_notice_on_account_pages(): void
    {
        $user = User::factory()->create();
        $this->announcement();
        $newUser = User::factory()->create();
        foreach ([$user, $newUser] as $viewer) {
            $this->actingAs($viewer)->get(route('account.orders'))->assertOk()->assertSee('اطلاعیه انتشار')->assertSee('متن اطلاعیه برای همه');
            $this->get(route('account.notifications.index'))->assertOk()->assertSee('اطلاعیه انتشار');
        }
        $this->assertDatabaseCount('account_announcements', 1);
        $this->assertDatabaseCount('user_notifications', 0);
    }

    public function test_guest_cannot_read_account_notices_or_use_management_routes(): void
    {
        $this->announcement();
        $this->get(route('account.orders'))->assertRedirect();
        $this->get(route('admin.account-announcements.index'))->assertRedirect();
        $this->post(route('admin.account-announcements.store'), $this->payload())->assertRedirect();
        $this->assertSame('', trim(view('components.front.account-announcements')->render()));
        $this->assertDatabaseCount('account_announcements', 1);
    }

    #[DataProvider('hiddenNotices')]
    public function test_inactive_future_and_expired_notices_are_hidden(string $reason): void
    {
        $data = match ($reason) {
            'inactive' => ['is_active' => false],
            'future' => ['starts_at' => now()->addMinute()],
            'expired' => ['ends_at' => now()->subMinute()],
            'ending-now' => ['ends_at' => now()],
        };
        $announcement = $this->announcement($data);
        $this->actingAs(User::factory()->create());
        $this->assertCount(0, app(GetAccountAnnouncementsAction::class)->execute());
        $this->assertStringNotContainsString($announcement->title, view('components.front.account-announcements')->render());
    }

    public static function hiddenNotices(): array
    {
        return [['inactive'], ['future'], ['expired'], ['ending-now']];
    }

    public function test_schedule_boundaries_update_without_a_worker_or_stale_cache(): void
    {
        $announcement = $this->announcement(['starts_at' => now()->addMinute(), 'ends_at' => now()->addMinutes(2)]);
        $feed = app(GetAccountAnnouncementsAction::class);
        $this->assertCount(0, $feed->execute());
        $this->assertSame('زمان‌بندی‌شده', $announcement->statusLabel());
        $this->travel(1)->minutes();
        $this->assertCount(1, $feed->execute());
        $this->assertSame('در حال نمایش', $announcement->statusLabel());
        $this->travel(1)->minutes();
        $this->assertCount(0, $feed->execute());
        $this->assertSame('پایان‌یافته', $announcement->statusLabel());
    }

    public function test_notice_updates_disable_and_delete_are_visible_on_the_next_page_load(): void
    {
        $this->actingAs(User::factory()->create());
        $announcement = $this->announcement();
        $this->assertStringContainsString('اطلاعیه انتشار', view('components.front.account-announcements')->render());
        $announcement->update(['title' => 'متن تازه']);
        $this->assertStringContainsString('متن تازه', view('components.front.account-announcements')->render());
        $announcement->update(['is_active' => false]);
        $this->assertStringNotContainsString('متن تازه', view('components.front.account-announcements')->render());
        $announcement->update(['is_active' => true]);
        $announcement->delete();
        $this->assertSame('', trim(view('components.front.account-announcements')->render()));
    }

    public function test_title_and_multiline_body_are_escaped_in_both_account_and_admin_views(): void
    {
        $announcement = $this->announcement(['title' => '<script>alert(1)</script>', 'body' => "<img src=x onerror=alert(1)>\nخط دوم"]);
        $this->actingAs(User::factory()->create());
        $html = view('components.front.account-announcements')->render();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        $this->assertStringContainsString('whitespace-pre-line', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->actingAs($this->admin())->get(route('admin.account-announcements.edit', $announcement))
            ->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_every_management_operation_requires_the_dedicated_permission(): void
    {
        $announcement = $this->announcement();
        Gate::define('admin.display', fn () => true);
        Gate::define('account-announcement.manage', fn () => false);
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.account-announcements.index'))->assertForbidden();
        $this->get(route('admin.account-announcements.create'))->assertForbidden();
        $this->get(route('admin.account-announcements.edit', $announcement))->assertForbidden();
        $this->post(route('admin.account-announcements.store'), $this->payload())->assertForbidden();
        $this->put(route('admin.account-announcements.update', $announcement), $this->payload(['title' => 'غیرمجاز']))->assertForbidden();
        $this->delete(route('admin.account-announcements.destroy', $announcement))->assertForbidden();
        $this->assertSame('اطلاعیه انتشار', $announcement->fresh()->title);
        $this->assertDatabaseCount('account_announcements', 1);
    }

    public function test_migration_permission_can_be_assigned_to_a_non_super_admin_role(): void
    {
        $permission = Permission::query()->where('group', 'account-announcement')->where('ability', 'manage')->sole();
        $role = Role::factory()->create(['is_super_admin' => false, 'is_default' => false]);
        $role->permissions()->attach($permission);
        Gate::define('admin.display', fn () => true);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user)->get(route('admin.account-announcements.index'))->assertOk();
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_payloads_do_not_create_notices(array $overrides, string $field): void
    {
        $this->actingAs($this->admin())->post(route('admin.account-announcements.store'), $this->payload($overrides))
            ->assertSessionHasErrors($field);
        $this->assertDatabaseCount('account_announcements', 0);
    }

    public static function invalidPayloads(): array
    {
        return [
            [['title' => ''], 'title'], [['body' => ''], 'body'],
            [['body' => str_repeat('a', 5001)], 'body'], [['type' => 'unknown'], 'type'],
            [['is_active' => 'yes'], 'is_active'], [['starts_at' => 'invalid'], 'starts_at'],
            [['starts_at' => '1405/07/28 12:00', 'ends_at' => '1405/07/28 11:00'], 'ends_at'],
            [['starts_at' => '1405/07/28 12:00', 'ends_at' => '1405/07/28 12:00'], 'ends_at'],
            [['starts_at' => '1405/13/01 12:00'], 'starts_at'],
            [['starts_at' => '1405/07/31 12:00'], 'starts_at'],
            [['ends_at' => '1405/07/28 24:00'], 'ends_at'],
            [['starts_at' => ['1405/07/28 12:00']], 'starts_at'],
        ];
    }

    public function test_jalali_schedule_is_saved_as_gregorian_and_can_be_edited_with_persian_digits(): void
    {
        $this->actingAs($this->admin())->post(route('admin.account-announcements.store'), $this->payload([
            'starts_at' => '1403/01/01 12:34', 'ends_at' => '1403/01/02 13:45',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.account-announcements.index'));
        $announcement = AccountAnnouncement::query()->sole();
        $this->assertSame('2024-03-20 12:34:00', $announcement->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('2024-03-21 13:45:00', $announcement->ends_at->format('Y-m-d H:i:s'));
        $this->get(route('admin.account-announcements.edit', $announcement))
            ->assertOk()->assertSee('1403/01/01 12:34')->assertSee('1403/01/02 13:45');

        $this->put(route('admin.account-announcements.update', $announcement), $this->payload([
            'starts_at' => '۱۴۰۳/۰۱/۰۳ ۱۴:۵۶', 'ends_at' => null,
        ]))->assertSessionHasNoErrors();
        $this->assertSame('2024-03-22 14:56:00', $announcement->fresh()->starts_at->format('Y-m-d H:i:s'));
        $this->assertNull($announcement->fresh()->ends_at);
    }

    public function test_failed_validation_preserves_original_jalali_inputs(): void
    {
        $this->actingAs($this->admin())->from(route('admin.account-announcements.create'))
            ->post(route('admin.account-announcements.store'), $this->payload([
                'body' => '', 'starts_at' => '1403/01/01 12:34', 'ends_at' => '1403/01/02 13:45',
            ]))->assertSessionHasErrors('body')
            ->assertSessionHasInput('starts_at', '1403/01/01 12:34')
            ->assertSessionHasInput('ends_at', '1403/01/02 13:45');
        $this->get(route('admin.account-announcements.create'))->assertOk()
            ->assertSee('1403/01/01 12:34')->assertSee('1403/01/02 13:45');
        $this->assertDatabaseCount('account_announcements', 0);
    }

    public function test_create_and_edit_forms_have_jalali_pickers_and_no_placeholders(): void
    {
        $this->actingAs($this->admin());
        $announcement = $this->announcement();
        $forms = [
            [route('admin.account-announcements.create'), route('admin.account-announcements.store')],
            [route('admin.account-announcements.edit', $announcement), route('admin.account-announcements.update', $announcement)],
        ];
        foreach ($forms as [$url, $action]) {
            $response = $this->get($url)->assertOk();
            $document = new \DOMDocument;
            @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
            $xpath = new \DOMXPath($document);
            $form = '//form[@action="'.$action.'"]';
            $this->assertSame(2, $xpath->query($form.'//input[@data-jdp]')->length);
            $this->assertSame(0, $xpath->query($form.'//*[@placeholder]')->length);
        }
    }

    public function test_inactive_only_filter_stays_open_and_excludes_active_announcements(): void
    {
        $this->announcement(['title' => 'فعال']);
        $inactive = $this->announcement(['title' => 'غیرفعال', 'is_active' => false]);
        $this->actingAs($this->admin())->get(route('admin.account-announcements.index', ['is_active' => '0']))
            ->assertOk()->assertViewHas('hasActiveFilters', true)
            ->assertViewHas('announcements', fn ($items) => $items->modelKeys() === [$inactive->id]);
    }

    public function test_end_only_schedule_is_allowed_and_newest_notices_appear_first(): void
    {
        $this->actingAs($this->admin())->post(route('admin.account-announcements.store'), $this->payload([
            'ends_at' => jalali_date(now()->addDay()),
        ]))->assertSessionHasNoErrors();
        $newest = $this->announcement(['type' => 'success']);
        $this->assertSame($newest->id, app(GetAccountAnnouncementsAction::class)->execute()->first()->id);
    }

    private function admin(): User
    {
        return User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'اطلاعیه انتشار', 'body' => 'متن اطلاعیه برای همه', 'type' => 'info',
            'is_active' => '1', 'starts_at' => null, 'ends_at' => null,
        ], $overrides);
    }

    private function announcement(array $overrides = []): AccountAnnouncement
    {
        return AccountAnnouncement::query()->create($this->payload($overrides));
    }
}
