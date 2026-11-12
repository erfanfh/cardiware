<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubBioModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editors_can_filter_review_and_return_to_the_bio_queue(): void
    {
        $this->withoutMiddleware(EnsureUserIsAdmin::class);
        $editor = $this->editor();
        $pending = User::factory()->create();
        $pending->forceFill(['club_bio' => 'معرفی منتظر تایید', 'club_bio_status' => 'pending'])->save();
        $approved = User::factory()->create();
        $approved->forceFill(['club_bio' => 'معرفی تاییدشده', 'club_bio_status' => 'approved'])->save();

        $this->actingAs($editor)->get(route('admin.users.club-bios.index'))
            ->assertOk()->assertSee($pending->club_bio)->assertDontSee($approved->club_bio)
            ->assertViewHas('counts', ['pending' => 1, 'approved' => 1, 'rejected' => 0, 'all' => 2]);

        $this->post(route('admin.users.club-bio.moderate', $pending), [
            'action' => 'approve', 'from' => 'club-bios', 'status' => 'pending',
        ])->assertRedirect(route('admin.users.club-bios.index', ['status' => 'pending']));
        $this->assertSame('approved', $pending->fresh()->club_bio_status);
        $this->get(route('admin.users.club-bios.index'))->assertOk()->assertSee('موردی برای نمایش وجود ندارد');

        $this->get(route('admin.users.club-bios.index', ['status' => 'all']))
            ->assertOk()->assertSee($pending->club_bio)->assertSee($approved->club_bio);
        $this->get(route('admin.users.club-bios.index', ['status' => ['invalid']]))
            ->assertOk()->assertViewHas('status', 'pending');

        $this->post(route('admin.users.club-bio.moderate', $pending), [
            'action' => 'reject', 'from' => 'club-bios', 'status' => 'all',
        ])->assertRedirect(route('admin.users.club-bios.index', ['status' => 'all']));
        $this->assertSame('rejected', $pending->fresh()->club_bio_status);
    }

    public function test_regular_users_cannot_access_the_queue_or_moderate_bios(): void
    {
        $this->withoutMiddleware(EnsureUserIsAdmin::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.users.club-bios.index'))->assertForbidden();
        $this->post(route('admin.users.club-bio.moderate', $user), ['action' => 'approve'])->assertForbidden();
    }

    private function editor(): User
    {
        $role = Role::factory()->create();
        $role->permissions()->attach(Permission::query()->firstOrCreate([
            'group' => 'user', 'ability' => 'edit',
        ], ['label' => 'ویرایش کاربر']));

        return User::factory()->create(['role_id' => $role->id]);
    }
}
