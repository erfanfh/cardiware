<?php

namespace Tests\Feature\Project;

use App\Application\Project\Actions\DeleteProjectAction;
use App\Application\Project\Actions\HandleProjectBulkAction;
use App\Application\Project\Actions\UpdateProjectAction;
use App\Application\Project\Exceptions\ProjectEditLockedException;
use App\Application\Project\Support\ProjectEditLock;
use App\Domain\Identity\Models\User;
use App\Domain\Project\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery\MockInterface;
use Tests\TestCase;

class ProjectEditLockTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    private User $other;

    private Project $project;

    private ProjectEditLock $locks;

    private bool $allowEditing = true;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn (User $user, string $ability) => $ability === 'admin.display' || $this->allowEditing);
        $this->editor = User::factory()->create(['display_name' => 'مدیر اول']);
        $this->other = User::factory()->create(['display_name' => 'مدیر دوم']);
        $this->project = Project::withoutEvents(fn () => Project::create([
            'title' => 'پروژه آزمایشی', 'slug' => 'locked-project', 'body' => 'متن اصلی',
            'excerpt' => 'خلاصه', 'status' => 'active', 'progress_percent' => 0,
        ]));
        $this->locks = app(ProjectEditLock::class);
    }

    public function test_edit_page_acquires_lock_and_blocks_another_admin(): void
    {
        $this->actingAs($this->editor)->get(route('admin.projects.edit', $this->project))
            ->assertOk()->assertSee('name="edit_lock_token"', false);

        $locked = $this->project->fresh();
        self::assertSame($this->editor->id, (int) $locked->edit_lock_user_id);
        self::assertSame(64, strlen($locked->edit_lock_token));

        $this->actingAs($this->other)->get(route('admin.projects.edit', $this->project))
            ->assertStatus(423)->assertSee('مدیر اول')->assertDontSee('name="edit_lock_token"', false);
        self::assertSame($locked->edit_lock_token, $this->project->fresh()->edit_lock_token);
    }

    public function test_list_shows_who_is_editing(): void
    {
        $this->locks->acquire($this->project, $this->editor);
        $this->actingAs($this->other)->get(route('admin.projects.index'))
            ->assertOk()->assertSee('در حال ویرایش توسط مدیر اول');
    }

    public function test_heartbeat_extends_only_the_current_owners_lock_without_changing_content_timestamp(): void
    {
        $updatedAt = $this->project->updated_at;
        $token = $this->locks->acquire($this->project, $this->editor);
        $expiry = $this->project->fresh()->edit_lock_expires_at;
        $this->travel(20)->seconds();

        $this->actingAs($this->other)->postJson(route('admin.projects.edit-lock.renew', $this->project), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        self::assertTrue($expiry->equalTo($this->project->fresh()->edit_lock_expires_at));

        $this->actingAs($this->editor)->postJson(route('admin.projects.edit-lock.renew', $this->project), [
            'edit_lock_token' => $token,
        ])->assertNoContent();
        self::assertTrue($this->project->fresh()->edit_lock_expires_at->greaterThan($expiry));
        self::assertTrue($updatedAt->equalTo($this->project->fresh()->updated_at));
        self::assertArrayNotHasKey('edit_lock_token', $this->project->fresh()->toArray());
    }

    public function test_expired_lock_is_available_and_old_heartbeat_cannot_resurrect_it(): void
    {
        $this->travelTo(now()->startOfSecond());
        $token = $this->locks->acquire($this->project, $this->editor);
        $this->travel(59)->seconds();
        self::assertTrue($this->project->fresh()->isEditLocked());
        $this->travel(1)->seconds();
        self::assertFalse($this->project->fresh()->isEditLocked());

        $this->actingAs($this->editor)->postJson(route('admin.projects.edit-lock.renew', $this->project), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        $newToken = $this->locks->acquire($this->project, $this->other);
        self::assertNotSame($token, $newToken);
        self::assertSame($this->other->id, (int) $this->project->fresh()->edit_lock_user_id);
    }

    public function test_release_requires_current_owner_and_token(): void
    {
        $token = $this->locks->acquire($this->project, $this->editor);
        $releaseUrl = route('admin.projects.edit-lock.release', $this->project);
        $this->actingAs($this->other)->postJson($releaseUrl, ['edit_lock_token' => $token])->assertNoContent();
        self::assertTrue($this->project->fresh()->isEditLocked());

        $newToken = $this->locks->acquire($this->project, $this->editor);
        $this->actingAs($this->editor)->postJson($releaseUrl, ['edit_lock_token' => $token])->assertNoContent();
        self::assertSame($newToken, $this->project->fresh()->edit_lock_token);

        $this->postJson($releaseUrl, ['edit_lock_token' => $newToken])->assertNoContent();
        self::assertFalse($this->project->fresh()->isEditLocked());
        $this->locks->acquire($this->project, $this->other);
    }

    public function test_current_owner_can_save_and_redirect_keeps_the_same_token(): void
    {
        $token = $this->locks->acquire($this->project, $this->editor);
        $this->mock(UpdateProjectAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->andReturnUsing(function (Project $project, array $data): Project {
                $project->update(['title' => $data['title']]);

                return $project;
            });
        });

        $this->actingAs($this->editor)->put(route('admin.projects.update', $this->project), $this->payload($token))
            ->assertRedirect(route('admin.projects.edit', $this->project));
        $this->get(route('admin.projects.edit', $this->project))->assertOk();
        self::assertSame($token, $this->project->fresh()->edit_lock_token);
        self::assertSame('متن تازه', $this->project->fresh()->title);
    }

    public function test_foreign_missing_expired_and_previous_tab_tokens_cannot_save(): void
    {
        $token = $this->locks->acquire($this->project, $this->editor);
        $this->mock(UpdateProjectAction::class, fn (MockInterface $mock) => $mock->shouldNotReceive('execute'));
        $url = route('admin.projects.update', $this->project);

        $this->actingAs($this->other)->putJson($url, $this->payload($token))->assertStatus(423);
        $this->actingAs($this->editor)->putJson($url, $this->payload(''))->assertStatus(423);
        $newToken = $this->locks->acquire($this->project, $this->editor);
        $this->putJson($url, $this->payload($token))->assertStatus(423);
        $this->travel(ProjectEditLock::LIFETIME_SECONDS)->seconds();
        $this->putJson($url, $this->payload($newToken))->assertStatus(423);
        self::assertSame('پروژه آزمایشی', $this->project->fresh()->title);
    }

    public function test_real_project_update_still_saves_content_and_can_change_its_slug(): void
    {
        $token = $this->locks->acquire($this->project, $this->editor);
        $data = $this->payload($token);
        $data['slug'] = 'renamed-project';
        $data['features'] = [['title' => 'قابلیت تازه', 'subtitle' => 'توضیح قابلیت']];
        $data['roadmap_items'] = [['title' => 'مرحله تازه', 'status' => 'inprogress', 'progress_percent' => 25]];
        $data['changelogs'] = [['title' => 'نسخه تازه', 'body' => 'توضیح تغییرات']];
        $this->actingAs($this->editor)->put(route('admin.projects.update', $this->project), $data)
            ->assertRedirect();

        $saved = Project::withoutGlobalScopes()->findOrFail($this->project->id);
        self::assertSame('متن تازه', $saved->body);
        self::assertSame('قابلیت تازه', $saved->features->sole()->title);
        self::assertSame('مرحله تازه', $saved->roadmapItems->sole()->title);
        self::assertSame('نسخه تازه', $saved->changelogs->sole()->title);
        self::assertSame($token, $saved->edit_lock_token);
        $this->get(route('admin.projects.edit', $saved))->assertOk();
        $this->postJson(route('admin.projects.edit-lock.renew', $saved), [
            'edit_lock_token' => $token,
        ])->assertNoContent();
    }

    public function test_failed_save_rolls_back_content_and_leaves_lock_with_original_owner(): void
    {
        $token = $this->locks->acquire($this->project, $this->editor);
        try {
            $this->locks->save($this->project, $this->editor, $token, function (): void {
                DB::table('projects')->where('id', $this->project->id)->update(['title' => 'تغییر ناموفق']);
                throw new \RuntimeException('Save failed');
            });
            self::fail('The simulated save failure must be propagated.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Save failed', $exception->getMessage());
            self::assertSame('پروژه آزمایشی', $this->project->fresh()->title);
            self::assertSame($token, $this->project->fresh()->edit_lock_token);
        }
    }

    public function test_quick_update_works_after_editor_releases_the_post(): void
    {
        $token = $this->locks->acquire($this->project, $this->editor);
        $this->locks->release($this->project, $this->editor, $token);
        $this->actingAs($this->other)->patchJson(route('admin.projects.quick-update', $this->project), [
            'title' => 'ویرایش سریع مجاز', 'slug' => 'locked-project', 'status' => 'active',
            'progress_percent' => 30,
        ])->assertOk();
        self::assertSame('ویرایش سریع مجاز', $this->project->fresh()->title);
    }

    public function test_validation_redirect_keeps_owner_token_and_unsaved_input(): void
    {
        $token = $this->locks->acquire($this->project, $this->editor);
        $url = route('admin.projects.edit', $this->project);
        $this->actingAs($this->editor)->from($url)
            ->put(route('admin.projects.update', $this->project), [
                'title' => 'متن ذخیره نشده', 'body' => 'تغییرات ذخیره نشده', 'edit_lock_token' => $token,
            ])->assertRedirect($url)->assertSessionHasErrors('status');

        $this->get($url)->assertOk()->assertSee('تغییرات ذخیره نشده');
        self::assertSame($token, $this->project->fresh()->edit_lock_token);
    }

    public function test_quick_update_and_delete_cannot_bypass_an_active_editor_even_for_same_admin(): void
    {
        $this->locks->acquire($this->project, $this->editor);
        $this->mock(DeleteProjectAction::class, fn (MockInterface $mock) => $mock->shouldNotReceive('execute'));
        foreach ([$this->editor, $this->other] as $user) {
            $this->actingAs($user)->patchJson(route('admin.projects.quick-update', $this->project), [
                'title' => 'تداخل', 'slug' => 'locked-project', 'status' => 'inactive',
                'progress_percent' => 30,
            ])->assertStatus(423);
            $this->deleteJson(route('admin.projects.destroy', $this->project))->assertStatus(423);
        }
        self::assertSame('پروژه آزمایشی', $this->project->fresh()->title);
        self::assertNull($this->project->fresh()->deleted_at);
    }

    public function test_bulk_action_changes_nothing_if_one_selected_project_is_locked(): void
    {
        $unlocked = Project::withoutEvents(fn () => Project::create([
            'title' => 'آزاد', 'slug' => 'unlocked-project',
            'body' => '', 'excerpt' => '', 'status' => 'active', 'progress_percent' => 0,
        ]));
        $this->locks->acquire($this->project, $this->editor);

        foreach (['deactivate', 'delete'] as $action) {
            try {
                app(HandleProjectBulkAction::class)->execute($action, [$unlocked->id, $this->project->id]);
                self::fail('A bulk action must not bypass an editor.');
            } catch (ProjectEditLockedException) {
                self::assertSame('active', DB::table('projects')->where('id', $unlocked->id)->value('status'));
                self::assertNull(DB::table('projects')->where('id', $unlocked->id)->value('deleted_at'));
            }
        }
    }

    public function test_denied_permission_cannot_acquire_or_renew_a_lock(): void
    {
        $this->allowEditing = false;
        $this->actingAs($this->editor)->get(route('admin.projects.edit', $this->project))
            ->assertForbidden();
        self::assertFalse($this->project->fresh()->isEditLocked());

        $this->postJson(route('admin.projects.edit-lock.renew', $this->project), [
            'edit_lock_token' => str_repeat('a', 64),
        ])->assertForbidden();
    }

    public function test_lock_endpoints_reject_invalid_token_input(): void
    {
        $this->actingAs($this->editor)->postJson(route('admin.projects.edit-lock.renew', $this->project), [
            'edit_lock_token' => ['invalid'],
        ])->assertUnprocessable();
    }

    private function payload(string $token): array
    {
        return [
            'title' => 'متن تازه', 'body' => 'متن تازه', 'slug' => 'locked-project',
            'status' => 'active', 'display_views_offset' => 0, 'edit_lock_token' => $token,
            'progress_percent' => 10,
        ];
    }
}
