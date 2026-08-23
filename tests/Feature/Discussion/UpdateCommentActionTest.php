<?php

namespace Tests\Feature\Discussion;

use App\Application\Discussion\Actions\UpdateCommentAction;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateCommentActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');
            $table->unsignedBigInteger('root_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('content');
            $table->string('status');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedTinyInteger('rating_percent')->nullable();
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('dislikes_count')->default(0);
            $table->bigInteger('score')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('pinned_at')->nullable();
            $table->unsignedBigInteger('pinned_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_it_updates_root_comment_content_and_rating_and_flushes_its_cache(): void
    {
        $comment = $this->createComment([
            'content' => 'متن قبلی',
            'rating_percent' => 40,
        ]);

        $cacheTags = [
            CacheTag::COMMENTS,
            CacheTag::commentable($comment->commentable_type, $comment->commentable_id),
        ];

        Cache::tags($cacheTags)->put('comment-update-test', 'cached');

        $updated = app(UpdateCommentAction::class)->execute(
            comment: $comment,
            content: "  خط اول\r\nخط دوم  ",
            ratingStars: 4,
        );

        $this->assertSame("خط اول\nخط دوم", $updated->content);
        $this->assertSame(80, $updated->rating_percent);
        $this->assertSame(4, $updated->rating_stars);
        $this->assertNull(Cache::tags($cacheTags)->get('comment-update-test'));
    }

    public function test_reply_content_can_be_updated_without_a_rating(): void
    {
        $root = $this->createComment();
        $reply = $this->createComment([
            'root_id' => $root->id,
            'parent_id' => $root->id,
            'content' => 'پاسخ قبلی',
            'rating_percent' => null,
        ]);

        $updated = app(UpdateCommentAction::class)->execute(
            comment: $reply,
            content: 'پاسخ ویرایش‌شده',
            ratingStars: null,
        );

        $this->assertSame('پاسخ ویرایش‌شده', $updated->content);
        $this->assertNull($updated->rating_percent);
    }

    public function test_reply_cannot_receive_an_independent_rating(): void
    {
        $root = $this->createComment();
        $reply = $this->createComment([
            'root_id' => $root->id,
            'parent_id' => $root->id,
            'rating_percent' => null,
        ]);

        $this->expectException(ValidationException::class);

        app(UpdateCommentAction::class)->execute(
            comment: $reply,
            content: 'پاسخ',
            ratingStars: 5,
        );
    }

    private function createComment(array $attributes = []): Comment
    {
        $comment = Comment::query()->create(array_merge([
            'commentable_type' => 'post',
            'commentable_id' => 10,
            'root_id' => null,
            'parent_id' => null,
            'content' => 'دیدگاه تست',
            'status' => CommentStatusEnum::APPROVED,
            'approved_at' => now(),
            'rating_percent' => 60,
        ], $attributes));

        if ($comment->isRoot() && $comment->root_id === null) {
            $comment->forceFill(['root_id' => $comment->id])->saveQuietly();
        }

        return $comment->refresh();
    }
}
