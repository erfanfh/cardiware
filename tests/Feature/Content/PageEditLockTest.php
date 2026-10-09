<?php

namespace Tests\Feature\Content;

use App\Application\Content\Services\PageTemplateService;
use App\Application\Content\Support\PageEditLock;
use App\Domain\Content\Models\Page;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PageEditLockTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    private User $other;

    private Page $page;

    private PageEditLock $locks;

    private bool $allowEditing = true;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn (User $user, string $ability) => $ability === 'admin.display' || $this->allowEditing);
        $this->editor = User::factory()->create(['display_name' => 'مدیر اول']);
        $this->other = User::factory()->create(['display_name' => 'مدیر دوم']);
        $this->page = Page::withoutEvents(fn () => Page::create([
            'title' => 'برگه آزمایشی', 'slug' => 'locked-page', 'body' => 'متن اصلی',
            'excerpt' => 'خلاصه', 'status' => 'active', 'author_id' => $this->editor->id,
            'published_at' => now()->subDay(),
        ]));
        $this->locks = app(PageEditLock::class);
    }

    public function test_edit_page_acquires_lock_and_blocks_another_admin(): void
    {
        $this->actingAs($this->editor)->get(route('admin.pages.edit', $this->page))
            ->assertOk()->assertSee('name="edit_lock_token"', false)
            ->assertSee('data-edit-lock', false)
            ->assertSee(route('admin.pages.edit-lock.renew', $this->page), false)
            ->assertSee(route('admin.pages.edit-lock.release', $this->page), false);

        $locked = $this->page->fresh();
        self::assertSame($this->editor->id, (int) $locked->edit_lock_user_id);
        self::assertSame(64, strlen($locked->edit_lock_token));

        $this->actingAs($this->other)->get(route('admin.pages.edit', $this->page))
            ->assertStatus(423)->assertSee('مدیر اول')->assertDontSee('name="edit_lock_token"', false);
        self::assertSame($locked->edit_lock_token, $this->page->fresh()->edit_lock_token);
    }

    public function test_list_shows_who_is_editing(): void
    {
        $this->locks->acquire($this->page, $this->editor);
        $this->actingAs($this->other)->get(route('admin.pages.index'))
            ->assertOk()->assertSee('در حال ویرایش توسط مدیر اول');
    }

    public function test_heartbeat_extends_only_the_current_owners_lock_without_changing_content_timestamp(): void
    {
        $updatedAt = $this->page->updated_at;
        $token = $this->locks->acquire($this->page, $this->editor);
        $expiry = $this->page->fresh()->edit_lock_expires_at;
        $this->travel(20)->seconds();

        $this->actingAs($this->other)->postJson(route('admin.pages.edit-lock.renew', $this->page), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        self::assertTrue($expiry->equalTo($this->page->fresh()->edit_lock_expires_at));

        $this->actingAs($this->editor)->postJson(route('admin.pages.edit-lock.renew', $this->page), [
            'edit_lock_token' => $token,
        ])->assertNoContent();
        self::assertTrue($this->page->fresh()->edit_lock_expires_at->greaterThan($expiry));
        self::assertTrue($updatedAt->equalTo($this->page->fresh()->updated_at));
        self::assertArrayNotHasKey('edit_lock_token', $this->page->fresh()->toArray());
    }

    public function test_expired_lock_is_available_and_old_heartbeat_cannot_resurrect_it(): void
    {
        $this->travelTo(now()->startOfSecond());
        $token = $this->locks->acquire($this->page, $this->editor);
        $this->travel(59)->seconds();
        self::assertTrue($this->page->fresh()->isEditLocked());
        $this->travel(1)->seconds();
        self::assertFalse($this->page->fresh()->isEditLocked());

        $this->actingAs($this->editor)->postJson(route('admin.pages.edit-lock.renew', $this->page), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        $newToken = $this->locks->acquire($this->page, $this->other);
        self::assertNotSame($token, $newToken);
        self::assertSame($this->other->id, (int) $this->page->fresh()->edit_lock_user_id);
    }

    public function test_release_requires_current_owner_and_token(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);
        $releaseUrl = route('admin.pages.edit-lock.release', $this->page);
        $this->actingAs($this->other)->postJson($releaseUrl, ['edit_lock_token' => $token])->assertNoContent();
        self::assertTrue($this->page->fresh()->isEditLocked());

        $newToken = $this->locks->acquire($this->page, $this->editor);
        $this->actingAs($this->editor)->postJson($releaseUrl, ['edit_lock_token' => $token])->assertNoContent();
        self::assertSame($newToken, $this->page->fresh()->edit_lock_token);

        $this->postJson($releaseUrl, ['edit_lock_token' => $newToken])->assertNoContent();
        self::assertFalse($this->page->fresh()->isEditLocked());
        $this->locks->acquire($this->page, $this->other);
    }

    public function test_current_owner_can_save_and_redirect_keeps_the_same_token(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);

        $this->actingAs($this->editor)->put(route('admin.pages.update', $this->page), $this->payload($token))
            ->assertRedirect(route('admin.pages.edit', $this->page));
        $this->get(route('admin.pages.edit', $this->page))->assertOk();
        self::assertSame($token, $this->page->fresh()->edit_lock_token);
        self::assertSame('متن تازه', $this->page->fresh()->title);
    }

    public function test_foreign_missing_expired_and_previous_tab_tokens_cannot_save(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);
        $url = route('admin.pages.update', $this->page);

        $this->actingAs($this->other)->putJson($url, $this->payload($token))->assertStatus(423);
        $this->actingAs($this->editor)->putJson($url, $this->payload(''))->assertStatus(423);
        $newToken = $this->locks->acquire($this->page, $this->editor);
        $this->putJson($url, $this->payload($token))->assertStatus(423);
        $this->travel(PageEditLock::LIFETIME_SECONDS)->seconds();
        $this->putJson($url, $this->payload($newToken))->assertStatus(423);
        self::assertSame('برگه آزمایشی', $this->page->fresh()->title);
    }

    public function test_real_page_update_still_saves_content_and_can_change_its_slug(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);
        $data = $this->payload($token);
        $data['slug'] = 'renamed-page';
        $data['excerpt'] = 'خلاصه تازه';
        $data['template'] = array_key_first(app(PageTemplateService::class)->all());
        $this->actingAs($this->editor)->put(route('admin.pages.update', $this->page), $data)
            ->assertRedirect();

        $saved = Page::withoutGlobalScopes()->findOrFail($this->page->id);
        self::assertSame('متن تازه', $saved->body);
        self::assertSame('renamed-page', $saved->slug);
        self::assertSame('خلاصه تازه', $saved->excerpt);
        self::assertSame($data['template'], $saved->template);
        self::assertSame($token, $saved->edit_lock_token);
        $this->get(route('admin.pages.edit', $saved))->assertOk();
        $this->postJson(route('admin.pages.edit-lock.renew', $saved), [
            'edit_lock_token' => $token,
        ])->assertNoContent();
    }

    public function test_failed_save_rolls_back_content_and_leaves_lock_with_original_owner(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);
        try {
            $this->locks->save($this->page, $this->editor, $token, function (): void {
                DB::table('pages')->where('id', $this->page->id)->update(['title' => 'تغییر ناموفق']);
                throw new \RuntimeException('Save failed');
            });
            self::fail('The simulated save failure must be propagated.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Save failed', $exception->getMessage());
            self::assertSame('برگه آزمایشی', $this->page->fresh()->title);
            self::assertSame($token, $this->page->fresh()->edit_lock_token);
        }
    }

    public function test_delete_works_after_editor_releases_the_page(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);
        $this->locks->release($this->page, $this->editor, $token);
        $this->actingAs($this->other)->delete(route('admin.pages.destroy', $this->page))->assertRedirect();
        $this->assertSoftDeleted($this->page);
    }

    public function test_validation_redirect_keeps_owner_token_and_unsaved_input(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);
        $url = route('admin.pages.edit', $this->page);
        $this->actingAs($this->editor)->from($url)
            ->put(route('admin.pages.update', $this->page), [
                'title' => 'متن ذخیره نشده', 'body' => 'تغییرات ذخیره نشده', 'edit_lock_token' => $token,
            ])->assertRedirect($url)->assertSessionHasErrors('status');

        $this->get($url)->assertOk()->assertSee('تغییرات ذخیره نشده');
        self::assertSame($token, $this->page->fresh()->edit_lock_token);
    }

    public function test_quick_update_and_delete_cannot_bypass_an_active_editor_even_for_same_admin(): void
    {
        $this->locks->acquire($this->page, $this->editor);
        foreach ([$this->editor, $this->other] as $user) {
            $this->actingAs($user)->patchJson(route('admin.pages.quick-update', $this->page), [
                'title' => 'تداخل', 'slug' => 'locked-page', 'status' => 'inactive',
            ])->assertStatus(423);
            $this->deleteJson(route('admin.pages.destroy', $this->page))->assertStatus(423);
        }
        self::assertSame('برگه آزمایشی', $this->page->fresh()->title);
        self::assertNull($this->page->fresh()->deleted_at);
    }

    public function test_bulk_action_changes_nothing_if_one_selected_page_is_locked(): void
    {
        $unlocked = Page::withoutEvents(fn () => Page::create([
            'title' => 'آزاد', 'slug' => 'unlocked-page',
            'body' => '', 'excerpt' => '', 'status' => 'active', 'author_id' => $this->editor->id,
            'published_at' => now()->subDay(),
        ]));
        $this->locks->acquire($this->page, $this->editor);

        foreach (['deactivate', 'delete'] as $action) {
            $this->actingAs($this->other)->postJson(route('admin.pages.bulk-action'), [
                'bulk_action' => $action, 'ids' => [$unlocked->id, $this->page->id],
            ])->assertStatus(423);
            self::assertSame('active', DB::table('pages')->where('id', $unlocked->id)->value('status'));
            self::assertNull(DB::table('pages')->where('id', $unlocked->id)->value('deleted_at'));
        }
    }

    public function test_denied_permission_cannot_acquire_or_renew_a_lock(): void
    {
        $this->allowEditing = false;
        $this->actingAs($this->editor)->get(route('admin.pages.edit', $this->page))
            ->assertForbidden();
        self::assertFalse($this->page->fresh()->isEditLocked());

        $this->postJson(route('admin.pages.edit-lock.renew', $this->page), [
            'edit_lock_token' => str_repeat('a', 64),
        ])->assertForbidden();
    }

    public function test_bulk_action_works_after_editor_releases_the_page(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);
        $this->locks->release($this->page, $this->editor, $token);
        $this->actingAs($this->other)->post(route('admin.pages.bulk-action'), [
            'bulk_action' => 'deactivate', 'ids' => [$this->page->id],
        ])->assertRedirect(route('admin.pages.index'));
        self::assertSame('inactive', DB::table('pages')->where('id', $this->page->id)->value('status'));
    }

    public function test_quick_update_works_after_editor_releases_the_page(): void
    {
        $token = $this->locks->acquire($this->page, $this->editor);
        $this->locks->release($this->page, $this->editor, $token);
        $this->actingAs($this->other)->patchJson(route('admin.pages.quick-update', $this->page), [
            'title' => 'ویرایش سریع مجاز', 'slug' => 'quickly-renamed-page', 'status' => 'inactive',
        ])->assertOk()->assertJsonPath('updatedItem.slug', 'quickly-renamed-page');
        $saved = Page::withoutGlobalScopes()->findOrFail($this->page->id);
        self::assertSame('ویرایش سریع مجاز', $saved->title);
        self::assertSame('متن اصلی', $saved->body);
        self::assertNull($saved->edit_lock_token);
    }

    public function test_lock_endpoints_reject_invalid_token_input(): void
    {
        $this->actingAs($this->editor)->postJson(route('admin.pages.edit-lock.renew', $this->page), [
            'edit_lock_token' => ['invalid'],
        ])->assertUnprocessable();
    }

    private function payload(string $token): array
    {
        return [
            'title' => 'متن تازه', 'body' => 'متن تازه', 'slug' => 'locked-page',
            'status' => 'active', 'edit_lock_token' => $token,
        ];
    }
}
