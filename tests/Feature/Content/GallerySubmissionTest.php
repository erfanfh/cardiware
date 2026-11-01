<?php

namespace Tests\Feature\Content;

use App\Domain\Content\Models\UserMediaSubmission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Infrastructure\Services\Setting\BaseSettings;
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
        foreach (['approved', 'pending', 'approved'] as $status) {
            $this->patch(route('admin.gallery.status', $submission), ['status' => $status])
                ->assertRedirect();
        }

        $this->assertSame(150, $submission->refresh()->points_awarded);
        $this->assertSame(150, PointAccount::query()->where('user_id', $user->id)->value('balance'));
        $this->assertSame(1, PointLedgerEntry::query()->where('reason', 'gallery_submission')->count());
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
}
