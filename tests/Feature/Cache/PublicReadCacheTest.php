<?php

namespace Tests\Feature\Cache;

use App\Application\Content\Actions\GetPostArchiveAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\PublicReadCache;
use App\Application\Taxonomy\Actions\SyncModelTaxonomyTermsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicReadCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_warm_archives_execute_no_queries_and_model_updates_refresh_them(): void
    {
        $post = $this->createPost();
        $action = app(GetPostArchiveAction::class);
        self::assertSame('Original title', $action->execute(1, 9, $post->author_id)->first()->title);

        DB::enableQueryLog();
        DB::flushQueryLog();
        self::assertSame($post->id, $action->execute(1, 9, $post->author_id)->first()->id);
        self::assertSame([], DB::getQueryLog());
        DB::disableQueryLog();

        $post->update(['title' => 'Updated title']);
        self::assertSame('Updated title', $action->execute(1, 9, $post->author_id)->first()->title);
        $post->delete();
        self::assertSame(0, $action->execute(1, 9, $post->author_id)->total());
        $post->restore();
        self::assertSame(1, $action->execute(1, 9, $post->author_id)->total());
    }

    public function test_scheduled_posts_appear_at_publication_without_a_write(): void
    {
        $this->freezeTime();
        $post = $this->createPost(['published_at' => now()->addSeconds(20)]);
        $action = app(GetPostArchiveAction::class);

        self::assertSame(0, $action->execute(1, 9)->total());
        $this->travel(19)->seconds();
        self::assertSame(0, $action->execute(1, 9)->total());
        $this->travel(1)->seconds();
        self::assertSame([$post->id], $action->execute(1, 9)->pluck('id')->all());
    }

    public function test_changing_a_scheduled_date_invalidates_the_cached_transition(): void
    {
        $this->freezeTime();
        $post = $this->createPost(['published_at' => now()->addHour()]);
        $action = app(GetPostArchiveAction::class);
        self::assertSame(0, $action->execute(1, 9)->total());

        $post->update(['published_at' => now()->addSeconds(10)]);
        self::assertSame(0, $action->execute(1, 9)->total());
        $this->travel(10)->seconds();
        self::assertSame(1, $action->execute(1, 9)->total());
    }

    public function test_taxonomy_sync_and_parent_changes_refresh_descendant_archives(): void
    {
        $post = $this->createPost();
        $parent = $this->term('parent');
        $child = $this->term('child', $parent->id);
        $other = $this->term('other');
        $action = app(GetPostArchiveAction::class);
        self::assertSame(0, $action->execute(1, 9, term: $parent)->total());

        app(SyncModelTaxonomyTermsAction::class)->execute($post, 'categories', TaxonomyKeyEnum::POST_CATEGORY, [$child->id]);
        self::assertSame([$post->id], $action->execute(1, 9, term: $parent->fresh())->pluck('id')->all());

        $child->update(['parent_id' => $other->id]);
        self::assertSame(0, $action->execute(1, 9, term: $parent->fresh())->total());
        self::assertSame(1, $action->execute(1, 9, term: $other->fresh())->total());
        $child->update(['title' => 'Renamed category']);
        self::assertSame('Renamed category', $action->execute(1, 9)->first()->categories->sole()->title);
    }

    public function test_cached_paginators_use_the_current_url_and_do_not_retain_query_strings(): void
    {
        $this->createPost();
        $action = app(GetPostArchiveAction::class);
        $this->app->instance('request', Request::create('https://first.example/posts?tracking=first'));
        $first = $action->execute(1, 9)->appends(['tracking' => 'first']);
        self::assertStringContainsString('tracking=first', $first->url(2));

        $this->app->instance('request', Request::create('https://second.example/posts?tracking=second'));
        $second = $action->execute(1, 9);
        self::assertStringStartsWith('https://second.example/posts?', $second->url(2));
        self::assertStringNotContainsString('tracking', $second->url(2));
    }

    public function test_preview_reads_neither_use_nor_populate_public_cache(): void
    {
        $cache = app(PublicReadCache::class);
        $resolve = fn () => 'public';
        self::assertSame('public', $cache->remember('preview-isolation', [], [Post::class], $resolve));

        $request = Request::create('/preview/posts/1');
        $route = (new Route('GET', '/preview/posts/1', fn () => null))->name('preview.posts');
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);
        self::assertSame('preview', $cache->remember('preview-isolation', [], [Post::class], fn () => 'preview'));

        $this->app->instance('request', Request::create('/posts'));
        self::assertSame('public', $cache->remember('preview-isolation', [], [Post::class], fn () => 'wrong'));
    }

    public function test_missing_results_are_cached_and_creation_invalidates_them(): void
    {
        $cache = app(PublicReadCache::class);
        $calls = 0;
        $resolve = function () use (&$calls) {
            $calls++;

            return Post::query()->first();
        };
        self::assertNull($cache->remember('missing', [], [Post::class], $resolve));
        self::assertNull($cache->remember('missing', [], [Post::class], $resolve));
        self::assertSame(1, $calls);
        $post = $this->createPost();
        self::assertSame($post->id, $cache->remember('missing', [], [Post::class], $resolve)->id);
    }

    public function test_media_changes_invalidate_embedded_media_in_new_and_existing_caches(): void
    {
        $media = MediaFile::query()->create(['type' => 'image', 'visibility' => 'public', 'status' => 'ready', 'title' => 'Original']);
        $post = $this->createPost(['picture_id' => $media->id]);
        $action = app(GetPostArchiveAction::class);
        self::assertSame('Original', $action->execute(1, 9)->first()->picture->title);
        Cache::tags([CacheTag::PRODUCTS])->put('embedded-media', 'old', 600);

        $media->update(['title' => 'Updated media']);
        self::assertSame('Updated media', $action->execute(1, 9)->first()->picture->title);
        self::assertNull(Cache::tags([CacheTag::PRODUCTS])->get('embedded-media'));
        $media->delete();
        self::assertNull($action->execute(1, 9)->first()->picture);
    }

    public function test_store_and_post_seo_keys_cannot_collide(): void
    {
        self::assertNotSame(CacheKey::postSeoData(1, 123), CacheKey::storeSeoData(1, 123));
    }

    private function createPost(array $attributes = []): Post
    {
        return Post::query()->create(array_merge([
            'author_id' => User::factory()->create()->id,
            'title' => 'Original title', 'slug' => 'post-'.str()->random(8),
            'excerpt' => 'Excerpt', 'body' => 'Body', 'status' => 'active',
            'published_at' => now()->subMinute(),
        ], $attributes));
    }

    private function term(string $title, ?int $parent = null): TaxonomyTerm
    {
        return TaxonomyTerm::query()->create([
            'taxonomy' => TaxonomyKeyEnum::POST_CATEGORY, 'title' => $title,
            'slug' => $title, 'path' => $title, 'parent_id' => $parent,
        ]);
    }
}
