<?php

namespace Tests\Feature\Discussion;

use App\Application\Discussion\Actions\HandleCommentBulkAction;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommentBulkActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('comments');
        Schema::create('comments', function (Blueprint $table): void {
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

        Event::fake();
    }

    public function test_it_applies_every_supported_status_action_to_selected_comments(): void
    {
        $pending = $this->createComment(CommentStatusEnum::PENDING);
        $rejected = $this->createComment(CommentStatusEnum::REJECTED);
        $alreadyApproved = $this->createComment(CommentStatusEnum::APPROVED);
        $handler = app(HandleCommentBulkAction::class);
        $ids = [$pending->id, $rejected->id, $alreadyApproved->id];

        $approvedResult = $handler->execute('approve', $ids);

        self::assertSame(2, $approvedResult['affected']);
        self::assertSame(CommentStatusEnum::APPROVED, $pending->fresh()->status);
        self::assertSame(CommentStatusEnum::APPROVED, $rejected->fresh()->status);
        self::assertNotNull($pending->fresh()->approved_at);

        $pendingResult = $handler->execute('pending', $ids);

        self::assertSame(3, $pendingResult['affected']);
        self::assertSame(CommentStatusEnum::PENDING, $alreadyApproved->fresh()->status);
        self::assertNull($alreadyApproved->fresh()->approved_at);

        $rejectedResult = $handler->execute('reject', $ids);

        self::assertSame(3, $rejectedResult['affected']);
        self::assertSame(CommentStatusEnum::REJECTED, $pending->fresh()->status);
    }

    public function test_it_soft_deletes_only_the_selected_comments(): void
    {
        $first = $this->createComment(CommentStatusEnum::APPROVED);
        $second = $this->createComment(CommentStatusEnum::PENDING);
        $untouched = $this->createComment(CommentStatusEnum::APPROVED);

        $result = app(HandleCommentBulkAction::class)->execute('delete', [
            $first->id,
            $second->id,
        ]);

        self::assertSame(2, $result['affected']);
        self::assertTrue(Comment::withTrashed()->findOrFail($first->id)->trashed());
        self::assertTrue(Comment::withTrashed()->findOrFail($second->id)->trashed());
        self::assertFalse($untouched->fresh()->trashed());
    }

    private function createComment(CommentStatusEnum $status): Comment
    {
        $comment = Comment::query()->create([
            'commentable_type' => 'post',
            'commentable_id' => 10,
            'content' => 'دیدگاه تست',
            'status' => $status,
            'approved_at' => $status->isApproved() ? now() : null,
            'rating_percent' => 60,
        ]);

        $comment->forceFill(['root_id' => $comment->id])->saveQuietly();

        return $comment->refresh();
    }
}
