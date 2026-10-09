<?php

namespace Tests\Feature\Discussion;

use App\Application\Discussion\Actions\GetCommentableRatingSummaryAction;
use App\Application\Discussion\Actions\GetCommentableReviewsAction;
use App\Application\Discussion\Actions\ToggleCommentPinAction;
use App\Application\Discussion\Actions\UpdateCommentAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Catalog\Models\Product;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CommentReviewsTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('display_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('deleted_mobile')->nullable();
            $table->string('deleted_email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('comments', function (Blueprint $table): void {
            $table->id();
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');
            $table->unsignedBigInteger('root_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('content');
            $table->string('status');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedTinyInteger('rating_percent')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('pinned_at')->nullable();
            $table->unsignedBigInteger('pinned_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Cache::flush();
        $this->product = new Product;
        $this->product->id = 10;
    }

    public function test_only_public_rated_root_comments_for_this_product_are_included(): void
    {
        $valid = $this->comment(['content' => '<ul><li>خوب</li><li>سریع</li></ul>']);
        $this->comment(['status' => CommentStatusEnum::PENDING]);
        $this->comment(['status' => CommentStatusEnum::REJECTED]);
        $this->comment(['parent_id' => $valid->id, 'root_id' => $valid->id]);
        $this->comment(['commentable_id' => 11]);
        $this->comment(['commentable_type' => 'post']);
        $deleted = $this->comment();
        $deleted->delete();

        $reviews = app(GetCommentableReviewsAction::class)->execute($this->product);
        self::assertCount(1, $reviews);
        self::assertSame($valid->id, $reviews[0]->id);
        self::assertSame('نویسنده مهمان', $reviews[0]->authorName);
        self::assertSame('خوب سریع', $reviews[0]->body);
        self::assertSame(4.0, $reviews[0]->rating);
        self::assertSame($valid->created_at->toAtomString(), $reviews[0]->publishedAt);
        self::assertStringNotContainsString('private@example.test', serialize($reviews));
        self::assertStringNotContainsString('127.0.0.1', serialize($reviews));
    }

    public function test_invalid_ratings_empty_bodies_and_unknown_authors_are_omitted(): void
    {
        foreach ([null, 0, 19, 101] as $rating) {
            $this->comment(['rating_percent' => $rating]);
        }
        self::assertSame([], app(GetCommentableReviewsAction::class)->execute($this->product));
        $this->comment(['content' => '<ul></ul>']);
        $this->comment(['author_name' => null]);
        self::assertSame([], app(GetCommentableReviewsAction::class)->execute($this->product));
    }

    public function test_reviews_follow_the_first_public_page_and_do_not_filter_it_before_limiting(): void
    {
        $older = $this->comment();
        for ($i = 0; $i < 5; $i++) {
            $this->comment(['rating_percent' => null]);
        }
        self::assertSame([], app(GetCommentableReviewsAction::class)->execute($this->product));
        app(ToggleCommentPinAction::class)->execute($older, 99);
        $reviews = app(GetCommentableReviewsAction::class)->execute($this->product);
        self::assertSame([$older->id], array_column($reviews, 'id'));
        app(ToggleCommentPinAction::class)->execute($older, 99);
        self::assertSame([], app(GetCommentableReviewsAction::class)->execute($this->product));
    }

    public function test_warm_reviews_use_cache_without_queries_and_are_invalidated_on_edits(): void
    {
        $comment = $this->comment();
        $action = app(GetCommentableReviewsAction::class);
        $action->execute($this->product);
        $this->cacheSeo();
        DB::enableQueryLog();
        DB::flushQueryLog();
        self::assertSame('دیدگاه تست', $action->execute($this->product)[0]->body);
        self::assertSame([], DB::getQueryLog());
        DB::disableQueryLog();

        app(UpdateCommentAction::class)->execute($comment, 'ویرایش دیدگاه', 1);
        $this->assertSeoWasInvalidated();
        $reviews = $action->execute($this->product);
        self::assertSame('ویرایش دیدگاه', $reviews[0]->body);
        self::assertSame(1.0, $reviews[0]->rating);
        self::assertSame(1.0, app(GetCommentableRatingSummaryAction::class)->execute($this->product)->averageRating);

        $this->cacheSeo();
        $comment->update(['author_name' => 'نام جدید']);
        $this->assertSeoWasInvalidated();
        self::assertSame('نام جدید', $action->execute($this->product)[0]->authorName);
    }

    public function test_review_limit_does_not_change_the_aggregate_rating_of_all_comments(): void
    {
        $this->comment(['rating_percent' => 20]);
        for ($i = 0; $i < 6; $i++) {
            $this->comment(['rating_percent' => 100]);
        }

        $reviews = app(GetCommentableReviewsAction::class)->execute($this->product);
        self::assertCount(5, $reviews);
        self::assertSame(5.0, $reviews[0]->rating);
        $summary = app(GetCommentableRatingSummaryAction::class)->execute($this->product);
        self::assertSame(7, $summary->ratingCount);
        self::assertSame(4.43, $summary->averageRating);
    }

    public function test_moving_a_comment_invalidates_the_original_product_seo(): void
    {
        $comment = $this->comment();
        $action = app(GetCommentableReviewsAction::class);
        $action->execute($this->product);
        $this->cacheSeo();
        $comment->update(['commentable_id' => 11]);
        $this->assertSeoWasInvalidated();
        self::assertSame([], $action->execute($this->product));
        $otherProduct = new Product;
        $otherProduct->id = 11;
        self::assertSame($comment->id, $action->execute($otherProduct)[0]->id);
    }

    public function test_approval_deletion_restoration_and_creation_refresh_reviews_and_seo(): void
    {
        $comment = $this->comment(['status' => CommentStatusEnum::PENDING]);
        $action = app(GetCommentableReviewsAction::class);
        self::assertSame([], $action->execute($this->product));
        $this->cacheSeo();
        $comment->update(['status' => CommentStatusEnum::APPROVED]);
        $this->assertSeoWasInvalidated();
        self::assertCount(1, $action->execute($this->product));
        $this->cacheSeo();
        $comment->update(['status' => CommentStatusEnum::REJECTED]);
        $this->assertSeoWasInvalidated();
        self::assertSame([], $action->execute($this->product));
        $comment->update(['status' => CommentStatusEnum::APPROVED]);
        $action->execute($this->product);
        $this->cacheSeo();
        $comment->delete();
        $this->assertSeoWasInvalidated();
        self::assertSame([], $action->execute($this->product));
        $this->cacheSeo();
        $comment->restore();
        $this->assertSeoWasInvalidated();
        self::assertCount(1, $action->execute($this->product));
        $this->cacheSeo();
        $comment->forceDelete();
        $this->assertSeoWasInvalidated();
        self::assertSame([], $action->execute($this->product));
        $this->cacheSeo();
        $this->comment();
        $this->assertSeoWasInvalidated();
        self::assertCount(1, $action->execute($this->product));
    }

    public function test_registered_author_names_and_account_lifecycle_invalidate_both_cache_layers(): void
    {
        $user = User::query()->create(['display_name' => 'نام نمایشی']);
        $this->comment(['user_id' => $user->id, 'author_name' => null]);
        $action = app(GetCommentableReviewsAction::class);
        self::assertSame('نام نمایشی', $action->execute($this->product)[0]->authorName);
        $this->cacheSeo();
        $user->update(['display_name' => '', 'first_name' => 'علی', 'last_name' => 'رضایی']);
        $this->assertSeoWasInvalidated();
        self::assertSame('علی رضایی', $action->execute($this->product)[0]->authorName);
        $this->cacheSeo();
        $user->delete();
        $this->assertSeoWasInvalidated();
        self::assertSame([], $action->execute($this->product));
        $this->cacheSeo();
        $user->restore();
        $this->assertSeoWasInvalidated();
        self::assertSame('علی رضایی', $action->execute($this->product)[0]->authorName);
    }

    private function comment(array $attributes = []): Comment
    {
        return Comment::query()->create(array_merge([
            'commentable_type' => $this->product->getMorphClass(),
            'commentable_id' => $this->product->id,
            'author_name' => 'نویسنده مهمان', 'author_email' => 'private@example.test',
            'ip_address' => '127.0.0.1', 'content' => 'دیدگاه تست',
            'status' => CommentStatusEnum::APPROVED, 'rating_percent' => 80,
        ], $attributes));
    }

    private function seoTags(): array
    {
        return [
            CacheTag::SEO, CacheTag::SCHEMA, CacheTag::COMMENT_AUTHORS,
            CacheTag::PRODUCTS, CacheTag::product($this->product->id),
            CacheTag::commentable($this->product->getMorphClass(), $this->product->id),
        ];
    }

    private function cacheSeo(): void
    {
        Cache::tags($this->seoTags())->put(CacheKey::productSeoData($this->product->id, 0), 'cached');
    }

    private function assertSeoWasInvalidated(): void
    {
        self::assertNull(Cache::tags($this->seoTags())->get(CacheKey::productSeoData($this->product->id, 0)));
    }
}
