<?php

namespace Tests\Feature\Content;

use App\Application\Content\Actions\SyncPostInformationBoxesAction;
use App\Application\Content\Support\PostEditLock;
use App\Domain\Content\Enums\PostInformationBoxTypeEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use Database\Seeders\Feature\FeatureDefinitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Js;
use Tests\Support\CreatesDownloadEntries;
use Tests\TestCase;

class PostPrerequisitesTest extends TestCase
{
    use CreatesDownloadEntries, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);
        $this->actingAs(User::factory()->createQuietly());
    }

    public function test_admin_form_has_only_an_editor_for_prerequisites_and_restores_old_input(): void
    {
        $this->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee('پیش نیاز ها')
            ->assertSee('name="information_boxes[prerequisites][body]"', false)
            ->assertDontSee('name="information_boxes[prerequisites][title]"', false)
            ->assertSee('name="information_boxes[install_guide][title]"', false);

        $this->withSession(['_old_input' => [
            'information_boxes' => ['prerequisites' => ['body' => '<p>متن ذخیره نشده</p>']],
        ]])->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee(e('<p>متن ذخیره نشده</p>'), false);
    }

    public function test_prerequisites_can_be_created_updated_and_removed_through_the_admin_form(): void
    {
        $this->seed(FeatureDefinitionSeeder::class);
        $body = '<p>ابتدا <strong>ابزار مورد نیاز</strong> را نصب کنید.</p><ul><li><a href="https://example.test/tool">دانلود ابزار</a></li></ul>';
        $payload = [
            'title' => 'Prerequisites test',
            'slug' => 'prerequisites-test',
            'body' => 'Post content',
            'status' => 'active',
            'information_boxes' => [
                'prerequisites' => ['body' => $body, 'title' => 'Ignored title'],
                'install_guide' => ['title' => 'راهنمای سفارشی', 'body' => '<p>راهنمای نصب</p>'],
            ],
        ];

        $this->post(route('admin.posts.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $post = Post::withoutGlobalScopes()->where('slug', 'prerequisites-test')->firstOrFail();
        $box = $post->informationBoxes()->where('type', 'prerequisites')->firstOrFail();
        self::assertSame(PostInformationBoxTypeEnum::PREREQUISITES, $box->type);
        self::assertSame($body, $box->body);
        self::assertNull($box->title);

        $this->get(route('admin.posts.edit', $post))->assertOk()->assertSee(e($body), false);
        $html = view('front.posts.partials.detail.download-box', compact('post'))->render();
        self::assertStringContainsString('پیش نیاز ها', $html);
        self::assertStringContainsString($body, $html);

        $payload['edit_lock_token'] = app(PostEditLock::class)->acquire($post, auth()->user());
        $payload['information_boxes']['prerequisites']['body'] = '<p>پیش نیاز جدید</p>';
        $this->put(route('admin.posts.update', $post), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame('<p>پیش نیاز جدید</p>', $box->fresh()->body);
        self::assertSame(1, $post->informationBoxes()->where('type', 'prerequisites')->count());

        $payload['information_boxes']['prerequisites']['body'] = '';
        $this->put(route('admin.posts.update', $post), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseMissing('post_information_boxes', ['post_id' => $post->id, 'type' => 'prerequisites']);
        $this->assertDatabaseHas('post_information_boxes', [
            'post_id' => $post->id, 'type' => 'install_guide', 'title' => 'راهنمای سفارشی',
        ]);
        self::assertStringNotContainsString('پیش نیاز ها', view('front.posts.partials.detail.download-box', [
            'post' => $post->fresh(),
        ])->render());
    }

    public function test_prerequisites_render_with_downloads_or_as_the_only_tab(): void
    {
        $entry = $this->createDownloadEntry();
        $post = $entry->section->post;
        $body = '<p>پیش نیاز دانلود</p>';
        app(SyncPostInformationBoxesAction::class)->execute($post, ['prerequisites' => ['body' => $body]]);

        $html = view('front.posts.partials.detail.download-box', compact('post'))->render();
        self::assertStringContainsString('activeTab: '.Js::from('downloads')->toHtml(), $html);
        self::assertStringContainsString('information-box-prerequisites', $html);
        self::assertStringContainsString('پیش نیاز ها', $html);
        self::assertStringContainsString($body, $html);
        self::assertStringContainsString('href="'.e($entry->content).'"', $html);

        $post->downloadSections()->delete();
        $html = view('front.posts.partials.detail.download-box', ['post' => $post->fresh()])->render();
        self::assertStringContainsString('activeTab: '.Js::from('information-box-prerequisites')->toHtml(), $html);
        self::assertStringContainsString($body, $html);
    }

    public function test_prerequisites_body_rejects_non_text_input(): void
    {
        $this->postJson(route('admin.posts.store'), [
            'status' => 'active',
            'information_boxes' => ['prerequisites' => ['body' => ['invalid']]],
        ])->assertUnprocessable()->assertJsonValidationErrors('information_boxes.prerequisites.body');

        $this->assertDatabaseCount('post_information_boxes', 0);
    }
}
