<?php

namespace Tests\Feature\Integration;

use App\Application\System\Actions\Home\GetHomeFreePostsTabDataAction;
use App\Application\System\Actions\Home\GetHomePostTabsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\UserMediaSubmission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\HomeSettings;
use App\Livewire\Front\Home\FreePosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class November13SitePackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        $this->withoutVite();
        $this->travelTo(now()->startOfDay()->addHours(12));
    }

    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        parent::tearDown();
    }

    public function test_home_shows_all_pins_plus_three_new_cards_and_eight_distinct_list_items(): void
    {
        $author = User::factory()->create();
        $parent = $this->category('parent');
        $child = $this->category('child', $parent->id);
        $other = $this->category('other');
        $fresh = collect();
        for ($i = 0; $i < 13; $i++) {
            $post = $this->createPost($author, $child, ['published_at' => now()->subMinutes($i + 1)]);
            if ($i === 0) {
                $post->taxonomyTerms()->attach($parent);
            }
            $fresh->push($post);
        }
        $pin = $this->createPost($author, $child, ['is_pinned' => true, 'published_at' => now()->subMonths(2)]);
        $this->createPost($author, $child, ['status' => 'inactive', 'is_pinned' => true]);
        $this->createPost($author, $child, ['published_at' => now()->addHour(), 'is_pinned' => true]);
        $this->createPost($author, $other, ['is_pinned' => true]);
        $deleted = $this->createPost($author, $child, ['is_pinned' => true]);
        $deleted->delete();
        request()->setRouteResolver(fn () => (new Route('POST', 'livewire/update', fn () => null))->name('livewire.update'));

        $item = ['key' => 'test', 'main_category_ids' => [$parent->id], 'main_count' => 1];
        $action = app(GetHomeFreePostsTabDataAction::class);
        $data = $action->execute($item);
        $this->assertSame([$pin->id, ...$fresh->take(3)->pluck('id')->all()], $data['mainPosts']->pluck('id')->all());
        $this->assertSame($fresh->skip(3)->take(8)->pluck('id')->all(), $data['list1Posts']->pluck('id')->all());
        $this->assertTrue($data['list2Posts']->isEmpty());

        // Counts must participate in the cache key; an older main_count must not truncate the new layout.
        $changed = $action->execute($item + ['hero_count' => 6, 'list_count' => 2]);
        $this->assertCount(7, $changed['mainPosts']);
        $this->assertSame($fresh->skip(6)->take(2)->pluck('id')->all(), $changed['list1Posts']->pluck('id')->all());
        $this->assertTrue($action->execute($item + ['list_count' => 0])['list1Posts']->isEmpty());
    }

    public function test_today_counter_includes_descendants_and_livewire_switches_only_to_configured_tabs(): void
    {
        $author = User::factory()->create();
        $parent = $this->category('parent');
        $child = $this->category('child', $parent->id);
        $other = $this->category('other');
        $this->createPost($author, $parent);
        $this->createPost($author, $child);
        $this->createPost($author, $parent, ['published_at' => now()->subDay()]);
        $this->createPost($author, $child, ['published_at' => now()->addHour()]);
        $this->createPost($author, $child, ['status' => 'inactive']);
        $otherPost = $this->createPost($author, $other);
        app(HomeSettings::class)->set('posts_items', [
            ['main_title' => 'Free posts', 'main_category_ids' => [$parent->id]],
            ['main_title' => 'Other posts', 'main_category_ids' => [$other->id], 'hero_count' => 99, 'list_count' => -1],
        ]);

        $tabs = app(GetHomePostTabsAction::class)->execute();
        $this->assertSame(2, $tabs[0]['today_count']);
        $this->assertSame(12, $tabs[1]['hero_count']);
        $this->assertSame(0, $tabs[1]['list_count']);
        Livewire::test(FreePosts::class)
            ->assertSet('activeTabKey', 'tab-1')
            ->assertSeeHtml('title="امروز 2 پست جدید برای این دسته‌بندی منتشر شده"')
            ->call('changeTab', 'invalid')->assertSet('activeTabKey', 'tab-1')
            ->call('changeTab', 'tab-2')->assertSet('activeTabKey', 'tab-2')
            ->assertSet('mainPosts', fn ($posts) => $posts->pluck('id')->all() === [$otherPost->id]);
    }

    public function test_manual_home_hero_reaches_livewire_and_preserves_pins_and_distinct_list_items(): void
    {
        $author = User::factory()->create();
        $category = $this->category('manual-hero');
        $fresh = collect();
        for ($i = 0; $i < 8; $i++) {
            $fresh->push($this->createPost($author, $category, ['published_at' => now()->subMinutes($i + 1)]));
        }
        $pin = $this->createPost($author, $category, ['is_pinned' => true]);
        $hero = $fresh[4];
        app(HomeSettings::class)->set('posts_items', [[
            'main_title' => 'Manual hero', 'main_category_ids' => [$category->id],
            'featured_post_id' => $hero->id, 'hero_count' => 3, 'list_count' => 4,
        ]]);

        Livewire::test(FreePosts::class)
            ->assertSet('mainPosts', fn ($posts) => $posts->pluck('id')->all() === [$hero->id, $pin->id, $fresh[0]->id, $fresh[1]->id])
            ->assertSet('list1Posts', fn ($posts) => $posts->pluck('id')->all() === [$fresh[2]->id, $fresh[3]->id, $fresh[5]->id, $fresh[6]->id]);
    }

    public function test_manual_hero_choices_have_separate_cache_entries_and_enforce_public_visibility(): void
    {
        $author = User::factory()->create();
        $category = $this->category('automatic');
        $other = $this->category('featured');
        $child = $this->category('featured-child', $other->id);
        $automatic = $this->createPost($author, $category);
        $selected = $this->createPost($author, $child, ['published_at' => now()->subMinutes(2)]);
        $inactive = $this->createPost($author, $child, ['status' => 'inactive']);
        $future = $this->createPost($author, $child, ['published_at' => now()->addHour()]);
        $deleted = $this->createPost($author, $child);
        $deleted->delete();
        request()->setRouteResolver(fn () => (new Route('POST', 'livewire/update', fn () => null))->name('livewire.update'));
        $action = app(GetHomeFreePostsTabDataAction::class);
        $item = ['key' => 'manual-cache', 'main_category_ids' => [$category->id]];

        $this->assertSame($automatic->id, $action->execute($item)['mainPosts']->first()->id);
        $this->assertSame($selected->id, $action->execute($item + ['featured_post_id' => $selected->id])['mainPosts']->first()->id);
        $this->assertSame($selected->id, $action->execute($item + ['featured_category_id' => $other->id])['mainPosts']->first()->id);
        foreach ([$inactive, $future, $deleted] as $hidden) {
            $this->assertSame($automatic->id, $action->execute($item + ['featured_post_id' => $hidden->id])['mainPosts']->first()->id);
        }
    }

    public function test_home_settings_form_has_no_duplicate_club_card_fields_and_saves_visibility(): void
    {
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $html = $this->actingAs($admin)->get(route('admin.settings.home', ['tab' => 'club-cards']))->assertOk()->getContent();
        foreach (['subscription_title', 'subscription_subtitle', 'subscription_button_title', 'subscription_button_url', 'subscription_description', 'projects_title'] as $name) {
            $this->assertSame(1, substr_count($html, 'name="'.$name.'"'), $name);
        }
        $this->put(route('admin.settings.home.store'), [
            'club_cards_show_vip' => '0', 'club_cards_show_projects' => '0', 'club_cards_show_leaders' => '0',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertStringNotContainsString('<section', view('front.home.partials.club-cards')->render());
    }

    public function test_admin_can_edit_a_video_and_its_cover_without_changing_moderation_or_points(): void
    {
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $submission = $this->video();
        $this->actingAs($admin)->put(route('admin.gallery.update', $submission), [
            'title' => 'Updated video', 'video_url' => 'https://cdn.example.com/game.mp4',
            'video_thumbnail_url' => 'https://cdn.example.com/cover.jpg',
            'status' => 'rejected', 'points_awarded' => 9000, 'user_id' => $admin->id,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.gallery.index', ['status' => 'approved']));
        $submission->refresh();
        $this->assertSame('approved', $submission->status->value);
        $this->assertSame(20, (int) $submission->points_awarded);
        $this->assertNotSame($admin->id, $submission->user_id);
        $this->assertSame('https://cdn.example.com/cover.jpg', $submission->videoThumbnailUrl());
        $this->get(route('admin.gallery.index'))->assertOk()->assertSee('ویرایش ویدیو و تصویر شاخص');
        $this->get(route('gallery.index'))->assertOk()
            ->assertSeeHtml('<video controls preload="none" poster="https://cdn.example.com/cover.jpg"')
            ->assertSeeHtml('<source src="https://cdn.example.com/game.mp4">');

        $this->put(route('admin.gallery.update', $submission), ['video_thumbnail_url' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($submission->fresh()->videoThumbnailUrl());
    }

    public function test_gallery_edits_require_permission_and_http_urls(): void
    {
        $submission = $this->video();
        $this->withoutMiddleware(EnsureUserIsAdmin::class)->actingAs($submission->user)
            ->put(route('admin.gallery.update', $submission), ['title' => 'Unauthorized'])->assertForbidden();
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $this->actingAs($admin)->put(route('admin.gallery.update', $submission), [
            'video_url' => 'javascript:alert(1)', 'video_thumbnail_url' => 'data:image/png;base64,test',
        ])->assertSessionHasErrors(['video_url', 'video_thumbnail_url']);
        $this->assertSame('https://example.com/clip.mp4', $submission->fresh()->video_url);
    }

    public function test_known_video_hosts_embed_and_direct_files_use_the_video_player(): void
    {
        $submission = $this->video();
        foreach ([
            'https://www.aparat.com/v/abc12' => 'https://www.aparat.com/video/video/embed/videohash/abc12/vt/frame',
            'https://www.youtube.com/watch?feature=share&v=abc123DEF45' => 'https://www.youtube.com/embed/abc123DEF45',
            'https://youtu.be/abc123DEF45' => 'https://www.youtube.com/embed/abc123DEF45',
        ] as $url => $expected) {
            $submission->video_url = $url;
            $this->assertSame($expected, $submission->videoEmbedUrl());
        }
        foreach (['https://example.com/movie.mp4', 'https://fakeaparat.com/v/abc12', 'javascript:alert(1)'] as $url) {
            $submission->video_url = $url;
            $this->assertNull($submission->videoEmbedUrl());
        }
    }

    private function category(string $slug, ?int $parentId = null): TaxonomyTerm
    {
        return TaxonomyTerm::query()->create([
            'taxonomy' => TaxonomyKeyEnum::POST_CATEGORY, 'title' => $slug, 'slug' => $slug,
            'path' => $slug, 'parent_id' => $parentId,
        ]);
    }

    private function createPost(User $author, TaxonomyTerm $category, array $attributes = []): Post
    {
        $post = Post::query()->create(array_merge([
            'author_id' => $author->id, 'title' => 'Post '.str()->random(8), 'slug' => 'post-'.str()->random(8),
            'body' => 'Body', 'status' => 'active', 'is_pinned' => false, 'published_at' => now()->subMinute(),
        ], $attributes));
        $post->taxonomyTerms()->attach($category);

        return $post;
    }

    private function video(): UserMediaSubmission
    {
        return UserMediaSubmission::query()->create([
            'user_id' => User::factory()->create()->id, 'type' => 'video', 'title' => 'Game video',
            'video_url' => 'https://example.com/clip.mp4', 'status' => 'approved',
            'approved_at' => now(), 'points_awarded' => 20,
        ]);
    }
}
