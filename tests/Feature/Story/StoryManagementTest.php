<?php

namespace Tests\Feature\Story;

use App\Application\Story\Actions\GetVisibleStoriesAction;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Story\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(User::factory()->create([
            'role_id' => Role::factory()->create(['is_super_admin' => true])->id,
        ]));
    }

    public function test_admin_can_create_edit_list_and_delete_stories(): void
    {
        $this->get(route('admin.stories.create'))->assertOk()->assertSee('کاور دایره‌ای')
            ->assertSee('data-jdp', false)->assertDontSee('datetime-local', false);
        $image = $this->image();
        $thumbnail = $this->image();
        $this->post(route('admin.stories.store'), $this->payload($image, [
            'thumbnail' => ['id' => $thumbnail->id],
            'starts_at' => '1405/08/08 09:00', 'ends_at' => '1405/08/12 09:00',
        ]))->assertSessionHasNoErrors();
        $story = Story::query()->sole();
        $this->assertSame($thumbnail->id, $story->thumbnail_id);
        $this->assertSame('2026-10-30 09:00', $story->starts_at->format('Y-m-d H:i'));
        $this->get(route('admin.stories.index'))->assertOk()->assertSee('پچ جدید');
        $this->assertSame('2026-11-03 09:00', $story->ends_at->format('Y-m-d H:i'));
        $this->get(route('admin.stories.edit', $story))->assertOk()->assertSee('1405/08/08 09:00', false)
            ->assertSee('1405/08/12 09:00', false)->assertSee('data-jdp', false)->assertDontSee('datetime-local', false);
        $this->put(route('admin.stories.update', $story), $this->payload($image, [
            'title' => 'ویرایش‌شده', 'is_active' => '0', 'sort_order' => 8,
            'duration_seconds' => 7, 'link_url' => null, 'caption' => null,
        ]))->assertRedirect(route('admin.stories.edit', $story))->assertSessionHasNoErrors();
        $story->refresh();
        $this->assertFalse($story->is_active);
        $this->assertSame(8, $story->sort_order);
        $this->assertSame(7, $story->duration_seconds);
        $this->assertNull($story->thumbnail_id);
        $this->assertNull($story->starts_at);
        $this->assertNull($story->ends_at);
        $this->assertNull($story->link_url);
        $this->delete(route('admin.stories.destroy', $story))->assertRedirect(route('admin.stories.index'));
        $this->assertDatabaseMissing('stories', ['id' => $story->id]);
        $this->assertDatabaseHas('media_files', ['id' => $image->id]);
    }

    #[DataProvider('invalidData')]
    public function test_invalid_story_fields_are_rejected(array $overrides, string $error): void
    {
        $this->post(route('admin.stories.store'), $this->payload($this->image(), $overrides))
            ->assertSessionHasErrors($error);
        $this->assertDatabaseCount('stories', 0);
    }

    public static function invalidData(): array
    {
        return [
            'empty title' => [['title' => ''], 'title'],
            'missing image' => [['image' => null], 'image.id'],
            'script URL' => [['link_url' => 'javascript:alert(1)'], 'link_url'],
            'data URL' => [['link_url' => 'data:text/html,hello'], 'link_url'],
            'short duration' => [['duration_seconds' => 2], 'duration_seconds'],
            'long duration' => [['duration_seconds' => 61], 'duration_seconds'],
            'negative order' => [['sort_order' => -1], 'sort_order'],
            'inverted schedule' => [['starts_at' => '1405/08/11 12:00', 'ends_at' => '1405/08/10 12:00'], 'ends_at'],
            'equal dates' => [['starts_at' => '1405/08/11 12:00', 'ends_at' => '1405/08/11 12:00'], 'ends_at'],
            'invalid date' => [['starts_at' => 'nonsense'], 'starts_at'],
            'invalid Jalali day' => [['starts_at' => '1405/07/31 09:00'], 'starts_at'],
            'invalid leap day' => [['ends_at' => '1404/12/30 09:00'], 'ends_at'],
            'invalid time' => [['starts_at' => '1405/08/08 24:00'], 'starts_at'],
            'array date' => [['starts_at' => ['1405/08/08 09:00']], 'starts_at'],
        ];
    }

    public function test_jalali_dates_accept_persian_digits_and_can_be_updated(): void
    {
        $image = $this->image();
        $this->post(route('admin.stories.store'), $this->payload($image, [
            'starts_at' => '۱۴۰۵/۰۸/۰۸ ۰۹:۳۰', 'ends_at' => '۱۴۰۵/۰۸/۰۸ ۱۱:۴۵',
        ]))->assertSessionHasNoErrors();
        $story = Story::query()->sole();
        $this->assertSame('2026-10-30 09:30', $story->starts_at->format('Y-m-d H:i'));
        $this->assertSame('2026-10-30 11:45', $story->ends_at->format('Y-m-d H:i'));

        $this->put(route('admin.stories.update', $story), $this->payload($image, [
            'starts_at' => '1405/08/09 10:15', 'ends_at' => '1405/08/09 12:45',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('2026-10-31 10:15', $story->fresh()->starts_at->format('Y-m-d H:i'));
        $this->assertSame('2026-10-31 12:45', $story->fresh()->ends_at->format('Y-m-d H:i'));
    }

    public function test_validation_errors_preserve_original_jalali_input(): void
    {
        $this->from(route('admin.stories.create'))
            ->post(route('admin.stories.store'), $this->payload($this->image(), [
                'title' => '', 'starts_at' => '1405/08/08 09:30', 'ends_at' => '۱۴۰۵/۰۸/۰۹ ۱۱:۴۵',
            ]))->assertSessionHasErrors('title')
            ->assertSessionHasInput('starts_at', '1405/08/08 09:30')
            ->assertSessionHasInput('ends_at', '۱۴۰۵/۰۸/۰۹ ۱۱:۴۵');
        $this->get(route('admin.stories.create'))->assertOk()
            ->assertSee('1405/08/08 09:30', false)->assertSee('۱۴۰۵/۰۸/۰۹ ۱۱:۴۵', false);
    }

    public function test_media_must_be_ready_public_images_for_both_fields(): void
    {
        $valid = $this->image();
        foreach (['private', 'pending', 'video', 'missing_variant'] as $type) {
            $image = $this->image();
            $image->update(match ($type) {
                'private' => ['visibility' => 'private'],
                'pending' => ['status' => 'processing'],
                'video' => ['type' => 'video'],
                default => ['primary_variant_id' => null],
            });
            $this->post(route('admin.stories.store'), $this->payload($image))->assertSessionHasErrors('image.id');
            $this->post(route('admin.stories.store'), $this->payload($valid, ['thumbnail' => ['id' => $image->id]]))
                ->assertSessionHasErrors('thumbnail.id');
        }
    }

    public function test_admin_can_filter_sort_and_apply_bulk_actions(): void
    {
        $image = $this->image();
        $first = $this->story($image, ['title' => 'پچ الف', 'sort_order' => 2]);
        $second = $this->story($image, ['title' => 'پچ ب', 'sort_order' => 1, 'is_active' => false]);
        $this->get(route('admin.stories.index', ['title' => 'پچ', 'is_active' => '0']))
            ->assertOk()->assertViewHas('stories', fn ($stories) => $stories->modelKeys() === [$second->id]);
        $this->get(route('admin.stories.index'))
            ->assertViewHas('stories', fn ($stories) => $stories->modelKeys() === [$second->id, $first->id]);
        $this->get(route('admin.stories.index', ['sort' => 'title', 'direction' => 'desc']))->assertOk();
        $this->get(route('admin.stories.index', ['sort' => 'unsafe']))->assertSessionHasErrors('sort');
        $ids = [$first->id, $second->id];
        foreach (['activate' => true, 'deactivate' => false] as $action => $active) {
            $this->post(route('admin.stories.bulk-action'), ['bulk_action' => $action, 'ids' => $ids])
                ->assertSessionHasNoErrors()->assertRedirect(route('admin.stories.index'));
            $this->assertSame($active, $first->fresh()->is_active);
            $this->assertSame($active, $second->fresh()->is_active);
        }
        $this->post(route('admin.stories.bulk-action'), ['bulk_action' => 'delete', 'ids' => $ids])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('stories', 0);
    }

    public function test_read_only_admin_cannot_create_edit_delete_or_bulk_modify_stories(): void
    {
        $image = $this->image();
        $story = $this->story($image);
        $role = Role::factory()->create(['is_default' => false, 'is_super_admin' => false]);
        foreach ([['admin', 'display'], ['story', 'list']] as [$group, $ability]) {
            $permission = Permission::firstOrCreate(compact('group', 'ability'), ['label' => $ability]);
            $role->permissions()->attach($permission);
        }
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        Cache::flush();
        $this->get(route('admin.stories.index'))->assertOk()->assertDontSee('افزودن استوری جدید');
        $this->get(route('admin.stories.create'))->assertForbidden();
        $this->get(route('admin.stories.edit', $story))->assertForbidden();
        $this->post(route('admin.stories.store'), $this->payload($image))->assertForbidden();
        $this->put(route('admin.stories.update', $story), $this->payload($image))->assertForbidden();
        $this->delete(route('admin.stories.destroy', $story))->assertForbidden();
        foreach (['activate', 'deactivate', 'delete'] as $action) {
            $this->post(route('admin.stories.bulk-action'), ['bulk_action' => $action, 'ids' => [$story->id]])->assertForbidden();
        }
        $this->assertDatabaseCount('stories', 1);
    }

    public function test_visibility_order_and_schedule_boundaries_survive_cached_reads(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 30)->setTime(10, 0));
        $image = $this->image();
        $first = $this->story($image, ['title' => 'اول', 'sort_order' => 1]);
        $second = $this->story($image, ['title' => 'دوم', 'sort_order' => 2, 'ends_at' => now()->addMinutes(2)]);
        $scheduled = $this->story($image, ['title' => 'زمان‌بندی', 'starts_at' => now()->addMinute(), 'sort_order' => 0]);
        $this->story($image, ['is_active' => false]);
        $this->story($image, ['ends_at' => now()]);
        $action = app(GetVisibleStoriesAction::class);
        $this->assertSame([$first->id, $second->id], array_column($action->execute(), 'id'));
        $this->travel(1)->minutes();
        $this->assertSame([$scheduled->id, $first->id, $second->id], array_column($action->execute(), 'id'));
        $this->travel(1)->minutes();
        $this->assertSame([$scheduled->id, $first->id], array_column($action->execute(), 'id'));
    }

    public function test_changes_and_media_deletion_invalidate_public_cache_and_thumbnail_falls_back(): void
    {
        $image = $this->image();
        $thumbnail = $this->image();
        $story = $this->story($image, ['thumbnail_id' => $thumbnail->id]);
        $action = app(GetVisibleStoriesAction::class);
        $this->assertSame($thumbnail->url, $action->execute()[0]['thumb']);
        $story->update(['title' => 'عنوان جدید', 'duration_seconds' => 8]);
        $this->assertSame('عنوان جدید', $action->execute()[0]['title']);
        $this->assertSame(8000, $action->execute()[0]['durationMs']);
        $thumbnail->update(['visibility' => 'private']);
        $this->assertSame($image->url, $action->execute()[0]['thumb']);
        $image->primaryVariant->update(['path' => 'stories/changed.webp']);
        $this->assertStringContainsString('changed.webp', $action->execute()[0]['image']);
        $image->update(['status' => 'processing']);
        $this->assertSame([], $action->execute());
        $image->update(['status' => 'ready']);
        $this->assertCount(1, $action->execute());
        $image->delete();
        $this->assertNull($story->fresh()->image_id);
        $this->assertSame([], $action->execute());
    }

    public function test_empty_front_is_hidden_and_content_is_safely_encoded(): void
    {
        $this->assertSame('', trim(view('front.home.partials.story')->render()));
        $story = $this->story($this->image(), ['title' => '<script>alert(1)</script>', 'caption' => '</script><script>bad()</script>']);
        $html = view('front.home.partials.story')->render();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('wp-content/uploads', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('openByIndex(0)', $html);
        $story->delete();
        $this->assertSame('', trim(view('front.home.partials.story')->render()));
    }

    private function image(): MediaFile
    {
        $image = MediaFile::create([
            'title' => 'تصویر استوری', 'type' => 'image', 'visibility' => 'public', 'status' => 'ready',
            'created_by' => auth()->id(),
        ]);
        $variant = $image->variants()->create([
            'disk' => 'public', 'variant' => 'original', 'format' => 'webp', 'mime_type' => 'image/webp',
            'path' => 'stories/'.$image->id.'.webp', 'byte_size' => 100, 'width' => 900, 'height' => 1600,
        ]);
        $image->update(['primary_variant_id' => $variant->id]);

        return $image->fresh('primaryVariant');
    }

    private function payload(MediaFile $image, array $overrides = []): array
    {
        return array_replace([
            'title' => 'پچ جدید', 'subtitle' => 'آپدیت امروز', 'caption' => 'توضیحات استوری',
            'image' => ['id' => $image->id], 'thumbnail' => ['id' => null],
            'link_url' => 'https://example.com/details', 'link_label' => 'دانلود',
            'duration_seconds' => 15, 'sort_order' => 0, 'is_active' => '1',
            'starts_at' => null, 'ends_at' => null,
        ], $overrides);
    }

    private function story(MediaFile $image, array $overrides = []): Story
    {
        return Story::create(array_merge([
            'title' => 'پچ جدید', 'image_id' => $image->id, 'is_active' => true,
            'duration_seconds' => 15, 'sort_order' => 0,
        ], $overrides));
    }
}
