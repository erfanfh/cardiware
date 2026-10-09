<?php

namespace Tests\Feature\Video;

use App\Application\Video\Support\VideoEditLock;
use App\Domain\Identity\Models\User;
use App\Domain\Video\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class VideoEditLockTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    private User $other;

    private Video $video;

    private VideoEditLock $locks;

    private bool $allowEditing = true;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn (User $user, string $ability) => $ability === 'admin.display' || $this->allowEditing);
        $this->editor = User::factory()->create(['display_name' => 'مدیر اول']);
        $this->other = User::factory()->create(['display_name' => 'مدیر دوم']);
        $this->video = Video::withoutEvents(fn () => Video::create([
            'title' => 'ویدیو آزمایشی', 'slug' => 'locked-video', 'body' => 'متن اصلی',
            'excerpt' => 'خلاصه', 'status' => 'active', 'source_type' => 'direct',
            'video_url' => 'https://example.com/original.mp4', 'published_at' => now()->subDay(),
        ]));
        $this->locks = app(VideoEditLock::class);
    }

    public function test_edit_page_acquires_lock_and_blocks_another_admin(): void
    {
        $this->actingAs($this->editor)->get(route('admin.videos.edit', $this->video))
            ->assertOk()->assertSee('name="edit_lock_token"', false)
            ->assertSee('data-edit-lock', false)
            ->assertSee(route('admin.videos.edit-lock.renew', $this->video), false)
            ->assertSee(route('admin.videos.edit-lock.release', $this->video), false);

        $locked = $this->video->fresh();
        self::assertSame($this->editor->id, (int) $locked->edit_lock_user_id);
        self::assertSame(64, strlen($locked->edit_lock_token));

        $this->actingAs($this->other)->get(route('admin.videos.edit', $this->video))
            ->assertStatus(423)->assertSee('مدیر اول')->assertDontSee('name="edit_lock_token"', false);
        self::assertSame($locked->edit_lock_token, $this->video->fresh()->edit_lock_token);
    }

    public function test_list_shows_who_is_editing(): void
    {
        $this->locks->acquire($this->video, $this->editor);
        $this->actingAs($this->other)->get(route('admin.videos.index'))
            ->assertOk()->assertSee('در حال ویرایش توسط مدیر اول');
    }

    public function test_heartbeat_extends_only_the_current_owners_lock_without_changing_content_timestamp(): void
    {
        $updatedAt = $this->video->updated_at;
        $token = $this->locks->acquire($this->video, $this->editor);
        $expiry = $this->video->fresh()->edit_lock_expires_at;
        $this->travel(20)->seconds();

        $this->actingAs($this->other)->postJson(route('admin.videos.edit-lock.renew', $this->video), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        self::assertTrue($expiry->equalTo($this->video->fresh()->edit_lock_expires_at));

        $this->actingAs($this->editor)->postJson(route('admin.videos.edit-lock.renew', $this->video), [
            'edit_lock_token' => $token,
        ])->assertNoContent();
        self::assertTrue($this->video->fresh()->edit_lock_expires_at->greaterThan($expiry));
        self::assertTrue($updatedAt->equalTo($this->video->fresh()->updated_at));
        self::assertArrayNotHasKey('edit_lock_token', $this->video->fresh()->toArray());
    }

    public function test_expired_lock_is_available_and_old_heartbeat_cannot_resurrect_it(): void
    {
        $this->travelTo(now()->startOfSecond());
        $token = $this->locks->acquire($this->video, $this->editor);
        $this->travel(59)->seconds();
        self::assertTrue($this->video->fresh()->isEditLocked());
        $this->travel(1)->seconds();
        self::assertFalse($this->video->fresh()->isEditLocked());

        $this->actingAs($this->editor)->postJson(route('admin.videos.edit-lock.renew', $this->video), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        $newToken = $this->locks->acquire($this->video, $this->other);
        self::assertNotSame($token, $newToken);
        self::assertSame($this->other->id, (int) $this->video->fresh()->edit_lock_user_id);
    }

    public function test_release_requires_current_owner_and_token(): void
    {
        $token = $this->locks->acquire($this->video, $this->editor);
        $releaseUrl = route('admin.videos.edit-lock.release', $this->video);
        $this->actingAs($this->other)->postJson($releaseUrl, ['edit_lock_token' => $token])->assertNoContent();
        self::assertTrue($this->video->fresh()->isEditLocked());

        $newToken = $this->locks->acquire($this->video, $this->editor);
        $this->actingAs($this->editor)->postJson($releaseUrl, ['edit_lock_token' => $token])->assertNoContent();
        self::assertSame($newToken, $this->video->fresh()->edit_lock_token);

        $this->postJson($releaseUrl, ['edit_lock_token' => $newToken])->assertNoContent();
        self::assertFalse($this->video->fresh()->isEditLocked());
        $this->locks->acquire($this->video, $this->other);
    }

    public function test_current_owner_can_save_and_redirect_keeps_the_same_token(): void
    {
        $token = $this->locks->acquire($this->video, $this->editor);

        $this->actingAs($this->editor)->put(route('admin.videos.update', $this->video), $this->payload($token))
            ->assertRedirect(route('admin.videos.edit', $this->video));
        $this->get(route('admin.videos.edit', $this->video))->assertOk();
        self::assertSame($token, $this->video->fresh()->edit_lock_token);
        self::assertSame('متن تازه', $this->video->fresh()->title);
    }

    public function test_foreign_missing_expired_and_previous_tab_tokens_cannot_save(): void
    {
        $token = $this->locks->acquire($this->video, $this->editor);
        $url = route('admin.videos.update', $this->video);

        $this->actingAs($this->other)->putJson($url, $this->payload($token))->assertStatus(423);
        $this->actingAs($this->editor)->putJson($url, $this->payload(''))->assertStatus(423);
        $newToken = $this->locks->acquire($this->video, $this->editor);
        $this->putJson($url, $this->payload($token))->assertStatus(423);
        $this->travel(VideoEditLock::LIFETIME_SECONDS)->seconds();
        $this->putJson($url, $this->payload($newToken))->assertStatus(423);
        self::assertSame('ویدیو آزمایشی', $this->video->fresh()->title);
    }

    public function test_real_video_update_still_saves_content_and_can_change_its_slug(): void
    {
        $token = $this->locks->acquire($this->video, $this->editor);
        $data = $this->payload($token);
        $data['slug'] = 'renamed-video';
        $data['tags'] = ['new' => ['برچسب ویدیو']];
        $data['duration_seconds'] = 125;
        $data['is_featured'] = true;
        $this->actingAs($this->editor)->put(route('admin.videos.update', $this->video), $data)
            ->assertRedirect();

        $saved = Video::withoutGlobalScopes()->findOrFail($this->video->id);
        self::assertSame('متن تازه', $saved->body);
        self::assertSame('renamed-video', $saved->slug);
        self::assertSame('https://example.com/updated.mp4', $saved->video_url);
        self::assertSame('برچسب ویدیو', $saved->tags->sole()->title);
        self::assertSame(125, $saved->duration_seconds);
        self::assertTrue($saved->is_featured);
        self::assertSame($token, $saved->edit_lock_token);
        $this->get(route('admin.videos.edit', $saved))->assertOk();
        $this->postJson(route('admin.videos.edit-lock.renew', $saved), [
            'edit_lock_token' => $token,
        ])->assertNoContent();
    }

    public function test_failed_save_rolls_back_content_and_leaves_lock_with_original_owner(): void
    {
        $token = $this->locks->acquire($this->video, $this->editor);
        try {
            $this->locks->save($this->video, $this->editor, $token, function (): void {
                DB::table('videos')->where('id', $this->video->id)->update(['title' => 'تغییر ناموفق']);
                throw new \RuntimeException('Save failed');
            });
            self::fail('The simulated save failure must be propagated.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Save failed', $exception->getMessage());
            self::assertSame('ویدیو آزمایشی', $this->video->fresh()->title);
            self::assertSame($token, $this->video->fresh()->edit_lock_token);
        }
    }

    public function test_delete_works_after_editor_releases_the_video(): void
    {
        $token = $this->locks->acquire($this->video, $this->editor);
        $this->locks->release($this->video, $this->editor, $token);
        $this->actingAs($this->other)->delete(route('admin.videos.destroy', $this->video))->assertRedirect();
        $this->assertSoftDeleted($this->video);
    }

    public function test_validation_redirect_keeps_owner_token_and_unsaved_input(): void
    {
        $token = $this->locks->acquire($this->video, $this->editor);
        $url = route('admin.videos.edit', $this->video);
        $this->actingAs($this->editor)->from($url)
            ->put(route('admin.videos.update', $this->video), [
                'title' => 'متن ذخیره نشده', 'body' => 'تغییرات ذخیره نشده', 'edit_lock_token' => $token,
                'display_views_offset' => -1,
            ])->assertRedirect($url)->assertSessionHasErrors('display_views_offset');

        $this->get($url)->assertOk()->assertSee('تغییرات ذخیره نشده');
        self::assertSame($token, $this->video->fresh()->edit_lock_token);
    }

    public function test_delete_cannot_bypass_an_active_editor_even_for_same_admin(): void
    {
        $this->locks->acquire($this->video, $this->editor);
        foreach ([$this->editor, $this->other] as $user) {
            $this->actingAs($user)->deleteJson(route('admin.videos.destroy', $this->video))->assertStatus(423);
        }
        self::assertSame('ویدیو آزمایشی', $this->video->fresh()->title);
        self::assertNull($this->video->fresh()->deleted_at);
    }

    public function test_bulk_action_changes_nothing_if_one_selected_video_is_locked(): void
    {
        $unlocked = Video::withoutEvents(fn () => Video::create([
            'title' => 'آزاد', 'slug' => 'unlocked-video',
            'body' => '', 'excerpt' => '', 'status' => 'active', 'source_type' => 'direct',
            'video_url' => 'https://example.com/unlocked.mp4', 'published_at' => now()->subDay(),
        ]));
        $this->locks->acquire($this->video, $this->editor);

        foreach (['deactivate', 'delete'] as $action) {
            $this->actingAs($this->other)->postJson(route('admin.videos.bulk-action'), [
                'bulk_action' => $action, 'ids' => [$unlocked->id, $this->video->id],
            ])->assertStatus(423);
            self::assertSame('active', DB::table('videos')->where('id', $unlocked->id)->value('status'));
            self::assertNull(DB::table('videos')->where('id', $unlocked->id)->value('deleted_at'));
        }
    }

    public function test_denied_permission_cannot_acquire_or_renew_a_lock(): void
    {
        $this->allowEditing = false;
        $this->actingAs($this->editor)->get(route('admin.videos.edit', $this->video))
            ->assertForbidden();
        self::assertFalse($this->video->fresh()->isEditLocked());

        $this->postJson(route('admin.videos.edit-lock.renew', $this->video), [
            'edit_lock_token' => str_repeat('a', 64),
        ])->assertForbidden();
    }

    public function test_bulk_action_works_after_editor_releases_the_video(): void
    {
        $token = $this->locks->acquire($this->video, $this->editor);
        $this->locks->release($this->video, $this->editor, $token);
        $this->actingAs($this->other)->post(route('admin.videos.bulk-action'), [
            'bulk_action' => 'deactivate', 'ids' => [$this->video->id],
        ])->assertRedirect(route('admin.videos.index'));
        self::assertSame('inactive', DB::table('videos')->where('id', $this->video->id)->value('status'));
    }

    public function test_lock_endpoints_reject_invalid_token_input(): void
    {
        $this->actingAs($this->editor)->postJson(route('admin.videos.edit-lock.renew', $this->video), [
            'edit_lock_token' => ['invalid'],
        ])->assertUnprocessable();
    }

    private function payload(string $token): array
    {
        return [
            'title' => 'متن تازه', 'body' => 'متن تازه', 'slug' => 'locked-video',
            'status' => 'active', 'display_views_offset' => 0, 'edit_lock_token' => $token,
            'source_type' => 'direct', 'video_url' => 'https://example.com/updated.mp4',
        ];
    }
}
