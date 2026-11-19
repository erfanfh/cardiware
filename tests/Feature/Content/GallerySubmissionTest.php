<?php

namespace Tests\Feature\Content;

use App\Domain\Content\Models\UserMediaSubmission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\MissionMetricEnum;
use App\Domain\Loyalty\Models\LoyaltyMission;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\GallerySettings;
use Database\Seeders\Loyalty\LoyaltyDiverseAchievementsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GallerySubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        $this->withoutVite();
    }

    public function test_admin_can_render_gallery_settings_and_submissions(): void
    {
        $role = Role::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($admin)->get(route('admin.gallery.index'))
            ->assertOk()
            ->assertSee('ذخیره تنظیمات برگه');
    }

    public function test_reapproving_a_submission_awards_points_only_once(): void
    {
        app(GallerySettings::class)->set('image_points', 150);
        $role = Role::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $user = User::factory()->create();
        $submission = UserMediaSubmission::query()->create([
            'user_id' => $user->id,
            'type' => 'image',
            'title' => 'تصویر بازی',
            'image_url' => 'https://example.com/game.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);
        foreach (['approved', 'approved'] as $status) {
            $this->patch(route('admin.gallery.status', $submission), ['status' => $status])
                ->assertRedirect();
        }

        $this->assertSame(150, $submission->refresh()->points_awarded);
        $this->assertSame(150, PointAccount::query()->where('user_id', $user->id)->value('balance'));
        $this->assertSame(1, PointLedgerEntry::query()->where('reason', 'gallery_submission')->count());
    }

    public function test_revoking_approval_reverses_points_and_reapproval_restores_them(): void
    {
        app(GallerySettings::class)->set('image_points', 150);
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $user = User::factory()->create();
        $submission = UserMediaSubmission::query()->create([
            'user_id' => $user->id, 'type' => 'image', 'title' => 'تصویر بازی',
            'image_url' => 'https://example.com/game.jpg', 'status' => 'pending',
        ]);
        $this->actingAs($admin);
        foreach (['approved' => 150, 'pending' => 0] as $status => $balance) {
            $this->patch(route('admin.gallery.status', $submission), ['status' => $status])->assertRedirect();
            $this->assertSame($balance, PointAccount::query()->where('user_id', $user->id)->value('balance'));
        }
        $this->patch(route('admin.gallery.status', $submission), ['status' => 'approved'])->assertRedirect();
        $this->assertSame(150, $submission->fresh()->points_awarded);
        $this->assertSame(150, PointAccount::query()->where('user_id', $user->id)->value('balance'));
        $this->assertSame(2, PointLedgerEntry::query()->where('type', 'earn')->count());
        $this->assertSame(1, PointLedgerEntry::query()->where('type', 'reversal')->count());
    }

    public function test_regular_user_cannot_approve_submissions(): void
    {
        $user = User::factory()->create();
        $submission = UserMediaSubmission::query()->create([
            'user_id' => $user->id,
            'type' => 'image',
            'title' => 'تصویر بازی',
            'image_url' => 'https://example.com/game.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.gallery.status', $submission), ['status' => 'approved'])
            ->assertRedirect(route('account.dashboard'));

        $this->assertSame('pending', $submission->refresh()->status->value);
        $this->assertSame(0, PointLedgerEntry::query()->where('reason', 'gallery_submission')->count());
    }

    public function test_approval_counts_for_the_gallery_badge_and_mission_without_media_points(): void
    {
        $this->seed(LoyaltyDiverseAchievementsSeeder::class);
        app(GallerySettings::class)->set('image_points', 0);
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $user = User::factory()->create();
        $mission = LoyaltyMission::query()->create([
            'code' => 'weekly-gallery',
            'title' => 'Weekly gallery',
            'metric' => MissionMetricEnum::APPROVED_MEDIA_SUBMISSIONS,
            'target_count' => 2,
            'reward_points' => 50,
            'is_active' => true,
        ]);
        $submission = UserMediaSubmission::query()->create([
            'user_id' => $user->id, 'type' => 'image', 'title' => 'تصویر بازی',
            'image_url' => 'https://example.com/game.jpg', 'status' => 'pending',
        ]);

        $this->actingAs($admin);
        foreach (['approved', 'pending', 'approved', 'approved'] as $status) {
            $this->patch(route('admin.gallery.status', $submission), ['status' => $status])->assertRedirect();
        }

        $this->assertSame(1, $mission->progressEntries()->where('user_id', $user->id)->value('progress_count'));
        $this->assertSame(0, PointLedgerEntry::query()->where('reason', 'gallery_submission')->count());
        $this->assertSame(100, $user->pointAccount()->value('balance'));
    }
}
