<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Actions\DeleteProductAction;
use App\Application\Catalog\Actions\UpdateProductAction;
use App\Application\Catalog\Support\ProductEditLock;
use App\Application\Content\Support\PostEditLock;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductEditLockTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    private User $other;

    private Product $product;

    private ProductEditLock $locks;

    private bool $allowEditing = true;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn (User $user, string $ability) => $ability === 'admin.display' || $this->allowEditing);
        $this->editor = User::factory()->create(['display_name' => 'مدیر اول']);
        $this->other = User::factory()->create(['display_name' => 'مدیر دوم']);
        $store = Store::create(['title' => 'فروشگاه', 'slug' => 'lock-store', 'status' => 'active']);
        $this->product = Product::withoutEvents(fn () => Product::create([
            'store_id' => $store->id,
            'title' => 'محصول آزمایشی', 'slug' => 'locked-product', 'body' => 'متن اصلی',
            'excerpt' => 'خلاصه', 'status' => 'active', 'published_at' => now()->subMinute(),
            'type' => 'digital', 'structure' => 'simple',
        ]));
        $this->locks = app(ProductEditLock::class);
    }

    public function test_edit_page_acquires_lock_and_blocks_another_admin(): void
    {
        $response = $this->actingAs($this->editor)->get(route('admin.products.edit', $this->product))
            ->assertOk()->assertSee('name="edit_lock_token"', false)
            ->assertSee('data-edit-lock', false);
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));

        $locked = $this->product->fresh();
        self::assertSame($this->editor->id, (int) $locked->edit_lock_user_id);
        self::assertSame(64, strlen($locked->edit_lock_token));

        $this->actingAs($this->other)->get(route('admin.products.edit', $this->product))
            ->assertStatus(423)->assertSee('مدیر اول')->assertSee('یک دقیقه')
            ->assertDontSee('name="edit_lock_token"', false);
        self::assertSame($locked->edit_lock_token, $this->product->fresh()->edit_lock_token);
    }

    public function test_list_shows_who_is_editing(): void
    {
        $this->locks->acquire($this->product, $this->editor);
        $this->actingAs($this->other)->get(route('admin.products.index'))
            ->assertOk()->assertSee('در حال ویرایش توسط مدیر اول');
    }

    public function test_heartbeat_extends_only_the_current_lock_without_changing_content_timestamp(): void
    {
        $updatedAt = $this->product->updated_at;
        $token = $this->locks->acquire($this->product, $this->editor);
        $expiry = $this->product->fresh()->edit_lock_expires_at;
        $this->travel(20)->seconds();

        $url = route('admin.products.edit-lock.renew', $this->product);
        $this->actingAs($this->other)->postJson($url, ['edit_lock_token' => $token])->assertStatus(423);
        self::assertTrue($expiry->equalTo($this->product->fresh()->edit_lock_expires_at));
        $this->actingAs($this->editor)->postJson($url, ['edit_lock_token' => $token])->assertNoContent();
        self::assertTrue($this->product->fresh()->edit_lock_expires_at->greaterThan($expiry));
        self::assertTrue($updatedAt->equalTo($this->product->fresh()->updated_at));
        self::assertArrayNotHasKey('edit_lock_token', $this->product->fresh()->toArray());
    }

    public function test_lock_expires_after_exactly_one_minute_and_old_heartbeat_cannot_resurrect_it(): void
    {
        $this->travelTo(now()->startOfSecond());
        $token = $this->locks->acquire($this->product, $this->editor);
        $this->travel(59)->seconds();
        self::assertTrue($this->product->fresh()->isEditLocked());
        $this->travel(1)->seconds();
        self::assertFalse($this->product->fresh()->isEditLocked());

        $this->actingAs($this->editor)->postJson(route('admin.products.edit-lock.renew', $this->product), [
            'edit_lock_token' => $token,
        ])->assertStatus(423);
        $newToken = $this->locks->acquire($this->product, $this->other);
        self::assertNotSame($token, $newToken);
        self::assertSame($this->other->id, (int) $this->product->fresh()->edit_lock_user_id);
    }

    public function test_release_requires_current_owner_and_token(): void
    {
        $token = $this->locks->acquire($this->product, $this->editor);
        $url = route('admin.products.edit-lock.release', $this->product);
        $this->actingAs($this->other)->postJson($url, ['edit_lock_token' => $token])->assertNoContent();
        self::assertTrue($this->product->fresh()->isEditLocked());

        $newToken = $this->locks->acquire($this->product, $this->editor);
        $this->actingAs($this->editor)->postJson($url, ['edit_lock_token' => $token])->assertNoContent();
        self::assertSame($newToken, $this->product->fresh()->edit_lock_token);
        $this->postJson($url, ['edit_lock_token' => $newToken])->assertNoContent();
        self::assertFalse($this->product->fresh()->isEditLocked());
        $this->locks->acquire($this->product, $this->other);
    }

    public function test_current_owner_can_save_and_redirect_keeps_the_same_token(): void
    {
        $token = $this->locks->acquire($this->product, $this->editor);
        $this->mock(UpdateProductAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->andReturnUsing(function (Product $product, array $data): Product {
                $product->update(['title' => $data['title']]);

                return $product;
            });
        });
        $this->actingAs($this->editor)->put(route('admin.products.update', $this->product), $this->payload($token))
            ->assertRedirect(route('admin.products.edit', $this->product));
        $this->get(route('admin.products.edit', $this->product))->assertOk();
        self::assertSame($token, $this->product->fresh()->edit_lock_token);
        self::assertSame('متن تازه', $this->product->fresh()->title);
    }

    public function test_foreign_missing_expired_and_previous_tab_tokens_cannot_save(): void
    {
        $token = $this->locks->acquire($this->product, $this->editor);
        $this->mock(UpdateProductAction::class, fn (MockInterface $mock) => $mock->shouldNotReceive('execute'));
        $url = route('admin.products.update', $this->product);
        $this->actingAs($this->other)->putJson($url, $this->payload($token))->assertStatus(423);
        $this->actingAs($this->editor)->putJson($url, $this->payload(''))->assertStatus(423);
        $newToken = $this->locks->acquire($this->product, $this->editor);
        $this->putJson($url, $this->payload($token))->assertStatus(423);
        $this->travel(60)->seconds();
        $this->putJson($url, $this->payload($newToken))->assertStatus(423);
        self::assertSame('محصول آزمایشی', $this->product->fresh()->title);
    }

    public function test_real_update_can_save_product_relations_and_change_its_slug(): void
    {
        $token = $this->locks->acquire($this->product, $this->editor);
        $data = $this->payload($token);
        $data['slug'] = 'renamed-product';
        $data['update_progress'] = ['title' => 'پیشرفت', 'description' => 'توضیح تازه'];
        $data['addons'] = [['title' => 'نصب', 'price' => 1000]];
        $this->actingAs($this->editor)->put(route('admin.products.update', $this->product), $data)->assertRedirect();

        $saved = Product::withoutGlobalScopes()->findOrFail($this->product->id);
        self::assertSame('متن تازه', $saved->body);
        self::assertSame('پیشرفت', $saved->updateProgress->title);
        self::assertSame('نصب', $saved->addons->sole()->title);
        self::assertSame($token, $saved->edit_lock_token);
        $this->get(route('admin.products.edit', $saved))->assertOk();
        $this->postJson(route('admin.products.edit-lock.renew', $saved), ['edit_lock_token' => $token])->assertNoContent();
    }

    public function test_failed_save_rolls_back_content_and_preserves_the_lock(): void
    {
        $token = $this->locks->acquire($this->product, $this->editor);
        try {
            $this->locks->save($this->product, $this->editor, $token, function (): void {
                DB::table('products')->where('id', $this->product->id)->update(['title' => 'تغییر ناموفق']);
                throw new \RuntimeException('Save failed');
            });
            self::fail('A failed save must roll back.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Save failed', $exception->getMessage());
            self::assertSame('محصول آزمایشی', $this->product->fresh()->title);
            self::assertSame($token, $this->product->fresh()->edit_lock_token);
        }
    }

    public function test_validation_redirect_preserves_token_and_unsaved_input(): void
    {
        $token = $this->locks->acquire($this->product, $this->editor);
        $url = route('admin.products.edit', $this->product);
        $this->actingAs($this->editor)->from($url)->put(route('admin.products.update', $this->product), [
            'title' => 'متن ذخیره نشده', 'body' => 'تغییرات ذخیره نشده', 'edit_lock_token' => $token,
        ])->assertRedirect($url)->assertSessionHasErrors('status');
        $this->get($url)->assertOk()->assertSee('تغییرات ذخیره نشده');
        self::assertSame($token, $this->product->fresh()->edit_lock_token);
    }

    public function test_delete_cannot_bypass_editor_even_for_the_same_admin(): void
    {
        $this->locks->acquire($this->product, $this->editor);
        $this->mock(DeleteProductAction::class, fn (MockInterface $mock) => $mock->shouldNotReceive('execute'));
        foreach ([$this->editor, $this->other] as $user) {
            $this->actingAs($user)->deleteJson(route('admin.products.destroy', $this->product))->assertStatus(423);
        }
        self::assertNull($this->product->fresh()->deleted_at);
    }

    public function test_bulk_action_changes_nothing_if_any_selected_product_is_locked(): void
    {
        $unlocked = $this->product->replicate();
        $unlocked->slug = 'unlocked-product';
        $unlocked->save();
        $this->locks->acquire($this->product, $this->editor);
        foreach (['deactivate', 'delete'] as $action) {
            $this->actingAs($this->other)->postJson(route('admin.products.bulk-action'), [
                'bulk_action' => $action, 'ids' => [$unlocked->id, $this->product->id],
            ])->assertStatus(423);
            self::assertSame('active', DB::table('products')->where('id', $unlocked->id)->value('status'));
            self::assertNull(DB::table('products')->where('id', $unlocked->id)->value('deleted_at'));
        }
    }

    public function test_delete_and_bulk_action_work_after_release(): void
    {
        $token = $this->locks->acquire($this->product, $this->editor);
        $this->locks->release($this->product, $this->editor, $token);
        $this->actingAs($this->other)->post(route('admin.products.bulk-action'), [
            'bulk_action' => 'deactivate', 'ids' => [$this->product->id],
        ])->assertRedirect();
        self::assertSame('inactive', DB::table('products')->where('id', $this->product->id)->value('status'));
        $this->delete(route('admin.products.destroy', $this->product))->assertRedirect();
        self::assertNotNull(DB::table('products')->where('id', $this->product->id)->value('deleted_at'));
    }

    public function test_denied_permission_cannot_acquire_or_renew(): void
    {
        $this->allowEditing = false;
        $this->actingAs($this->editor)->get(route('admin.products.edit', $this->product))->assertForbidden();
        self::assertFalse($this->product->fresh()->isEditLocked());
        $this->postJson(route('admin.products.edit-lock.renew', $this->product), [
            'edit_lock_token' => str_repeat('a', 64),
        ])->assertForbidden();
    }

    public function test_lock_endpoints_reject_invalid_tokens(): void
    {
        $this->actingAs($this->editor)->postJson(route('admin.products.edit-lock.renew', $this->product), [
            'edit_lock_token' => ['invalid'],
        ])->assertUnprocessable();
    }

    public function test_post_and_product_locks_are_independent(): void
    {
        $post = Post::withoutEvents(fn () => Post::create([
            'author_id' => $this->editor->id, 'title' => 'نوشته', 'slug' => 'separate-post',
            'body' => '', 'excerpt' => '', 'status' => 'active', 'published_at' => now()->subMinute(),
        ]));
        $postToken = app(PostEditLock::class)->acquire($post, $this->editor);
        $productToken = $this->locks->acquire($this->product, $this->other);
        self::assertSame($this->editor->id, (int) $post->fresh()->edit_lock_user_id);
        self::assertSame($this->other->id, (int) $this->product->fresh()->edit_lock_user_id);
        self::assertNotSame($postToken, $productToken);
    }

    private function payload(string $token): array
    {
        return [
            'title' => 'متن تازه', 'body' => 'متن تازه', 'slug' => 'locked-product',
            'status' => 'active', 'type' => 'digital', 'structure' => 'simple',
            'store_id' => $this->product->store_id, 'edit_lock_token' => $token,
        ];
    }
}
