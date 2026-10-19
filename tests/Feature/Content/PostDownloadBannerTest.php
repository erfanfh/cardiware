<?php

namespace Tests\Feature\Content;

use App\Application\Content\Actions\GetPostDetailDataAction;
use App\Application\Content\Support\PostEditLock;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\Layout\Models\PageLayout;
use App\Domain\Media\Models\MediaFile;
use Database\Seeders\Feature\FeatureDefinitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Js;
use Tests\Support\CreatesDownloadEntries;
use Tests\TestCase;

class PostDownloadBannerTest extends TestCase
{
    use CreatesDownloadEntries, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->createQuietly());
        $this->seed(FeatureDefinitionSeeder::class);
    }

    public function test_admin_can_create_replace_and_remove_a_banner_independently_for_each_post(): void
    {
        $firstImage = $this->image('first');
        $secondImage = $this->image('second');
        $payload = [
            'title' => 'Banner post', 'slug' => 'banner-post', 'body' => 'Post body', 'status' => 'active',
            'download_banner' => ['id' => $firstImage->id],
            'download_banner_url' => 'https://example.test/first?from=post&campaign=banner',
        ];

        $this->post(route('admin.posts.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $post = Post::withoutGlobalScopes()->where('slug', 'banner-post')->firstOrFail();
        self::assertEquals($firstImage->id, $post->download_banner_media_id);
        self::assertSame($payload['download_banner_url'], $post->download_banner_url);
        $this->get(route('admin.posts.edit', $post))->assertOk()
            ->assertSee('بنر بالای باکس دانلود')
            ->assertSee('selectedPath: '.Js::from($firstImage->url)->toHtml(), false)
            ->assertSee('name="download_banner_url"', false);

        $other = $this->createDownloadEntry()->section->post;
        self::assertNull($other->download_banner_media_id);
        PageLayout::create(['key' => 'post-content', 'title' => 'Post']);
        $detail = app(GetPostDetailDataAction::class);
        self::assertTrue($detail->execute($post)['post']->relationLoaded('downloadBanner'));

        $payload['edit_lock_token'] = app(PostEditLock::class)->acquire($post, auth()->user());
        $payload['download_banner']['id'] = $secondImage->id;
        $payload['download_banner_url'] = 'https://example.test/second';
        $this->put(route('admin.posts.update', $post), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertEquals($secondImage->id, $post->fresh()->download_banner_media_id);
        self::assertSame('https://example.test/second', $detail->execute($post->fresh())['post']->download_banner_url);

        // A save that does not submit the banner fields must preserve them.
        unset($payload['download_banner'], $payload['download_banner_url']);
        $this->put(route('admin.posts.update', $post), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertEquals($secondImage->id, $post->fresh()->download_banner_media_id);

        $payload['download_banner'] = ['id' => ''];
        $payload['download_banner_url'] = '';
        $this->put(route('admin.posts.update', $post), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertNull($post->fresh()->download_banner_media_id);
        self::assertNull($post->fresh()->download_banner_url);
        self::assertNull($other->fresh()->download_banner_media_id);
    }

    public function test_banner_renders_above_downloads_without_cropping_and_is_hidden_when_unconfigured(): void
    {
        $post = $this->createDownloadEntry()->section->post;
        $image = $this->image('banner');
        $post->update(['download_banner_media_id' => $image->id, 'download_banner_url' => 'https://example.test/offer?a=1&b=2']);
        $html = view('front.posts.partials.detail.download-box', ['post' => $post->fresh()])->render();
        self::assertStringContainsString('data-post-download-banner', $html);
        self::assertStringContainsString('href="https://example.test/offer?a=1&amp;b=2"', $html);
        self::assertStringContainsString($image->url, $html);
        self::assertStringContainsString('block h-auto w-full', $html);
        self::assertLessThan(strpos($html, 'باکس دانلود'), strpos($html, 'data-post-download-banner'));

        $post->update(['download_banner_url' => null]);
        $html = view('front.posts.partials.detail.download-banner', ['post' => $post->fresh()])->render();
        self::assertStringContainsString($image->url, $html);
        self::assertStringNotContainsString('<a ', $html);

        $post->downloadSections()->delete();
        self::assertStringNotContainsString('data-post-download-banner', view('front.posts.partials.detail.download-box', [
            'post' => $post->fresh(),
        ])->render());

        $image->delete();
        self::assertNull($post->fresh()->download_banner_media_id);
        self::assertStringNotContainsString('data-post-download-banner', view('front.posts.partials.detail.download-banner', [
            'post' => $post->fresh(),
        ])->render());
    }

    public function test_form_restores_unsaved_banner_and_validation_rejects_invalid_media_and_links(): void
    {
        $image = $this->image('old-input');
        $this->withSession(['_old_input' => [
            'download_banner' => ['id' => $image->id, 'path' => $image->url],
            'download_banner_url' => 'https://example.test/unsaved',
        ]])->get(route('admin.posts.create'))->assertOk()
            ->assertSee('https://example.test/unsaved')
            ->assertSee('selectedPath: '.Js::from($image->url)->toHtml(), false);

        foreach (['javascript:alert(1)', 'data:text/html,test', ['invalid']] as $url) {
            $this->postJson(route('admin.posts.store'), ['status' => 'active', 'download_banner_url' => $url])
                ->assertUnprocessable()->assertJsonValidationErrors('download_banner_url');
        }

        foreach ([['type' => 'document'], ['type' => 'image', 'visibility' => 'private']] as $attributes) {
            $image->update($attributes);
            $this->postJson(route('admin.posts.store'), ['status' => 'active', 'download_banner' => ['id' => $image->id]])
                ->assertUnprocessable()->assertJsonValidationErrors('download_banner_media_id');
        }
        $this->assertDatabaseCount('posts', 0);
    }

    private function image(string $name): MediaFile
    {
        $media = MediaFile::create([
            'type' => 'image', 'visibility' => 'public', 'status' => 'ready',
            'title' => 'بنر '.$name, 'created_by' => auth()->id(),
        ]);
        $variant = $media->variants()->create([
            'disk' => 'public', 'mime_type' => 'image/webp', 'variant' => 'original',
            'format' => 'webp', 'path' => 'banners/'.$name.'.webp', 'byte_size' => 1200,
            'width' => 1200, 'height' => 200, 'checksum' => hash('sha256', $name), 'generated_at' => now(),
        ]);
        $media->update(['primary_variant_id' => $variant->id]);

        return $media->fresh();
    }
}
