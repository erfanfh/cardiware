<?php

namespace Tests\Feature\Content;

use App\Application\Content\Actions\DeletePostAction;
use App\Application\Content\Actions\HandlePostBulkAction;
use App\Application\Content\Actions\UpdatePostAction;
use App\Application\Content\Exceptions\PostEditLockedException;
use App\Application\Content\Support\PostEditLock;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use Database\Seeders\Feature\FeatureDefinitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery\MockInterface;
use Tests\TestCase;

class PostEditLockTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    private User $other;

    private Post $post;

    private PostEditLock $locks;

    private bool $allowEditing = true;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn (User $user, string $ability) => $ability === 'admin.display' || $this->allowEditing);
        $this->editor = User::factory()->create(['display_name' => 'مدیر اول']);
        $this->other = User::factory()->create(['display_name' => 'مدیر دوم']);
        $this->post = Post::withoutEvents(fn () => Post::create([
            'author_id' => $this->editor->id,
            'title' => 'نوشته آزمایشی', 'slug' => 'locked-post', 'body' => 'متن اصلی',
            'excerpt' => 'خلاصه', 'status' => 'active', 'published_at' => now()->subMinute(),
        ]));
        $this->locks = app(PostEditLock::class);
    }

    public function test_edit_page_acquires_lock_and_blocks_another_admin(): void
    {
        $this->actingAs($this->editor)->get(route('admin.posts.edit', $this->post))
            ->assertOk()->assertSee('name="edit_lock_token"', false);

        $locked = $this->post->fresh();
        self::assertSame($this->editor->id, (int) $locked->edit_lock_user_id);
        self::assertSame(64, strlen($locked->edit_lock_token));

        $this->actingAs($this->other)->get(route('admin.posts.edit', $this->post))
            ->assertStatus(423)->assertSee('مدیر اول')->assertDontSee('name="edit_lock_token"', false);
        self::assertSame($locked->edit_lock_token, $this->post->fresh()->edit_lock_token);
    }

    public function test_list_shows_who_is_editing(): void
    {
        $this->locks->acquire($this->post, $this->editor);
        $this->actingAs($this->other)->get(route('admin.posts.index'))
            ->assertOk()->assertSee('در حال ویرایش توسط مدیر اول');
    }

    public function test_heartbeat_extends_only_the_current_owners_lock_without_changing_content_timestamp(): void
    {
        $updatedAt = $this->post->updated_at;
        $token = $this->locks->acquire($this->post, $this->editor);
        $expiry = $this->post->fresh()->edit_lock_expires_at;
        $this->travel(60)->seconds();

        $this->actingAs($this->other)->postJson(route('admin.posts.edit-lock.renew', $this->post), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        self::assertTrue($expiry->equalTo($this->post->fresh()->edit_lock_expires_at));

        $this->actingAs($this->editor)->postJson(route('admin.posts.edit-lock.renew', $this->post), [
            'edit_lock_token' => $token,
        ])->assertNoContent();
        self::assertTrue($this->post->fresh()->edit_lock_expires_at->greaterThan($expiry));
        self::assertTrue($updatedAt->equalTo($this->post->fresh()->updated_at));
        self::assertArrayNotHasKey('edit_lock_token', $this->post->fresh()->toArray());
    }

    public function test_expired_lock_is_available_and_old_heartbeat_cannot_resurrect_it(): void
    {
        $token = $this->locks->acquire($this->post, $this->editor);
        $this->travel(PostEditLock::LIFETIME_SECONDS)->seconds();

        $this->actingAs($this->editor)->postJson(route('admin.posts.edit-lock.renew', $this->post), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        $newToken = $this->locks->acquire($this->post, $this->other);
        self::assertNotSame($token, $newToken);
        self::assertSame($this->other->id, (int) $this->post->fresh()->edit_lock_user_id);
    }

    public function test_release_requires_current_owner_and_token(): void
    {
        $token = $this->locks->acquire($this->post, $this->editor);
        $releaseUrl = route('admin.posts.edit-lock.release', $this->post);
        $this->actingAs($this->other)->postJson($releaseUrl, ['edit_lock_token' => $token])->assertNoContent();
        self::assertTrue($this->post->fresh()->isEditLocked());

        $newToken = $this->locks->acquire($this->post, $this->editor);
        $this->actingAs($this->editor)->postJson($releaseUrl, ['edit_lock_token' => $token])->assertNoContent();
        self::assertSame($newToken, $this->post->fresh()->edit_lock_token);

        $this->postJson($releaseUrl, ['edit_lock_token' => $newToken])->assertNoContent();
        self::assertFalse($this->post->fresh()->isEditLocked());
        $this->locks->acquire($this->post, $this->other);
    }

    public function test_current_owner_can_save_and_redirect_keeps_the_same_token(): void
    {
        $token = $this->locks->acquire($this->post, $this->editor);
        $this->mock(UpdatePostAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->andReturnUsing(function (Post $post, array $data): Post {
                $post->update(['title' => $data['title']]);

                return $post;
            });
        });

        $this->actingAs($this->editor)->put(route('admin.posts.update', $this->post), $this->payload($token))
            ->assertRedirect(route('admin.posts.edit', $this->post));
        $this->get(route('admin.posts.edit', $this->post))->assertOk();
        self::assertSame($token, $this->post->fresh()->edit_lock_token);
        self::assertSame('متن تازه', $this->post->fresh()->title);
    }

    public function test_foreign_missing_expired_and_previous_tab_tokens_cannot_save(): void
    {
        $token = $this->locks->acquire($this->post, $this->editor);
        $this->mock(UpdatePostAction::class, fn (MockInterface $mock) => $mock->shouldNotReceive('execute'));
        $url = route('admin.posts.update', $this->post);

        $this->actingAs($this->other)->putJson($url, $this->payload($token))->assertStatus(423);
        $this->actingAs($this->editor)->putJson($url, $this->payload(''))->assertStatus(423);
        $newToken = $this->locks->acquire($this->post, $this->editor);
        $this->putJson($url, $this->payload($token))->assertStatus(423);
        $this->travel(PostEditLock::LIFETIME_SECONDS)->seconds();
        $this->putJson($url, $this->payload($newToken))->assertStatus(423);
        self::assertSame('نوشته آزمایشی', $this->post->fresh()->title);
    }

    public function test_real_post_update_still_saves_content_and_can_change_its_slug(): void
    {
        $this->seed(FeatureDefinitionSeeder::class);
        $token = $this->locks->acquire($this->post, $this->editor);
        $data = $this->payload($token);
        $data['slug'] = 'renamed-post';
        $this->actingAs($this->editor)->put(route('admin.posts.update', $this->post), $data)
            ->assertRedirect();

        $saved = Post::withoutGlobalScopes()->findOrFail($this->post->id);
        self::assertSame('متن تازه', $saved->body);
        self::assertSame($token, $saved->edit_lock_token);
        $this->get(route('admin.posts.edit', $saved))->assertOk();
        $this->postJson(route('admin.posts.edit-lock.renew', $saved), [
            'edit_lock_token' => $token,
        ])->assertNoContent();
    }

    public function test_failed_save_rolls_back_content_and_leaves_lock_with_original_owner(): void
    {
        $token = $this->locks->acquire($this->post, $this->editor);
        try {
            $this->locks->save($this->post, $this->editor, $token, function (): void {
                DB::table('posts')->where('id', $this->post->id)->update(['title' => 'تغییر ناموفق']);
                throw new \RuntimeException('Save failed');
            });
            self::fail('The simulated save failure must be propagated.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Save failed', $exception->getMessage());
            self::assertSame('نوشته آزمایشی', $this->post->fresh()->title);
            self::assertSame($token, $this->post->fresh()->edit_lock_token);
        }
    }

    public function test_quick_update_works_after_editor_releases_the_post(): void
    {
        $token = $this->locks->acquire($this->post, $this->editor);
        $this->locks->release($this->post, $this->editor, $token);
        $this->actingAs($this->other)->patchJson(route('admin.posts.quick-update', $this->post), [
            'title' => 'ویرایش سریع مجاز', 'slug' => 'locked-post', 'status' => 'active',
        ])->assertOk();
        self::assertSame('ویرایش سریع مجاز', $this->post->fresh()->title);
    }

    public function test_validation_redirect_keeps_owner_token_and_unsaved_input(): void
    {
        $token = $this->locks->acquire($this->post, $this->editor);
        $url = route('admin.posts.edit', $this->post);
        $this->actingAs($this->editor)->from($url)
            ->put(route('admin.posts.update', $this->post), [
                'title' => 'متن ذخیره نشده', 'body' => 'تغییرات ذخیره نشده', 'edit_lock_token' => $token,
            ])->assertRedirect($url)->assertSessionHasErrors('status');

        $this->get($url)->assertOk()->assertSee('تغییرات ذخیره نشده');
        self::assertSame($token, $this->post->fresh()->edit_lock_token);
    }

    public function test_quick_update_and_delete_cannot_bypass_an_active_editor_even_for_same_admin(): void
    {
        $this->locks->acquire($this->post, $this->editor);
        $this->mock(DeletePostAction::class, fn (MockInterface $mock) => $mock->shouldNotReceive('execute'));
        foreach ([$this->editor, $this->other] as $user) {
            $this->actingAs($user)->patchJson(route('admin.posts.quick-update', $this->post), [
                'title' => 'تداخل', 'slug' => 'locked-post', 'status' => 'inactive',
            ])->assertStatus(423);
            $this->deleteJson(route('admin.posts.destroy', $this->post))->assertStatus(423);
        }
        self::assertSame('نوشته آزمایشی', $this->post->fresh()->title);
        self::assertNull($this->post->fresh()->deleted_at);
    }

    public function test_bulk_action_changes_nothing_if_one_selected_post_is_locked(): void
    {
        $unlocked = Post::withoutEvents(fn () => Post::create([
            'author_id' => $this->editor->id, 'title' => 'آزاد', 'slug' => 'unlocked-post',
            'body' => '', 'excerpt' => '', 'status' => 'active', 'published_at' => now()->subMinute(),
        ]));
        $this->locks->acquire($this->post, $this->editor);

        foreach (['deactivate', 'delete'] as $action) {
            try {
                app(HandlePostBulkAction::class)->execute($action, [$unlocked->id, $this->post->id]);
                self::fail('A bulk action must not bypass an editor.');
            } catch (PostEditLockedException) {
                self::assertSame('active', DB::table('posts')->where('id', $unlocked->id)->value('status'));
                self::assertNull(DB::table('posts')->where('id', $unlocked->id)->value('deleted_at'));
            }
        }
    }

    public function test_denied_permission_cannot_acquire_or_renew_a_lock(): void
    {
        $this->allowEditing = false;
        $this->actingAs($this->editor)->get(route('admin.posts.edit', $this->post))
            ->assertForbidden();
        self::assertFalse($this->post->fresh()->isEditLocked());

        $this->postJson(route('admin.posts.edit-lock.renew', $this->post), [
            'edit_lock_token' => str_repeat('a', 64),
        ])->assertForbidden();
    }

    public function test_lock_endpoints_reject_invalid_token_input(): void
    {
        $this->actingAs($this->editor)->postJson(route('admin.posts.edit-lock.renew', $this->post), [
            'edit_lock_token' => ['invalid'],
        ])->assertUnprocessable();
    }

    private function payload(string $token): array
    {
        return [
            'title' => 'متن تازه', 'body' => 'متن تازه', 'slug' => 'locked-post',
            'status' => 'active', 'display_views_offset' => 0, 'edit_lock_token' => $token,
        ];
    }
}
