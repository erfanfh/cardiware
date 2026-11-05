<?php

namespace Tests\Feature\Discussion;

use App\Application\Commerce\Actions\Checkout\CompleteOrderAfterSuccessfulPaymentAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Video\Models\Video;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use App\Domain\Commerce\Models\Cart;
use App\Livewire\Front\Discussion\CommentItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class VipCommentAuthorBadgeTest extends TestCase
{
    use RefreshDatabase;

    private function buyVip(User $user): VipSubscription
    {
        $plan = VipPlan::query()->create([
            'code' => 'vip-comment',
            'title' => 'پلن VIP',
            'duration_days' => 30,
            'price_toman' => 100000,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('vip.cart.add', $plan));
        $order = app(CreateOrderFromCartAction::class)->execute(Cart::query()->active()->firstOrFail());
        app(CompleteOrderAfterSuccessfulPaymentAction::class)->execute($order);

        return VipSubscription::query()->where('order_id', $order->id)->firstOrFail();
    }

    /** @return array<int, Model> */
    private function commentables(): array
    {
        $now = now();
        $postAuthor = User::factory()->create();
        $postId = DB::table('posts')->insertGetId([
            'author_id' => $postAuthor->id,
            'title' => 'نوشته نمونه', 'slug' => 'vip-comment-post', 'body' => 'متن',
            'status' => 'active', 'published_at' => $now,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $storeId = DB::table('stores')->insertGetId([
            'title' => 'فروشگاه نمونه', 'slug' => 'vip-comment-store', 'status' => 'active',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId,
            'title' => 'محصول نمونه', 'slug' => 'vip-comment-product',
            'type' => 'digital', 'structure' => 'simple', 'status' => 'active',
            'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $videoId = DB::table('videos')->insertGetId([
            'title' => 'ویدیوی نمونه', 'slug' => 'vip-comment-video',
            'status' => 'active', 'source_type' => 'direct',
            'video_url' => 'https://example.com/video.mp4', 'published_at' => $now,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return [
            Post::query()->findOrFail($postId),
            Product::query()->findOrFail($productId),
            Video::query()->findOrFail($videoId),
        ];
    }

    private function comment(Model $commentable, ?User $author): Comment
    {
        return Comment::query()->create([
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
            'user_id' => $author?->id,
            'author_name' => $author ? null : 'مهمان',
            'content' => 'دیدگاه آزمایشی',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    private function item(Comment $comment, Model $commentable): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(CommentItem::class, [
            'commentId' => $comment->id,
            'commentableType' => $commentable->getMorphClass(),
            'commentableId' => $commentable->getKey(),
        ]);
    }

    public function test_vip_author_badge_is_also_visible_on_replies(): void
    {
        $author = User::factory()->create();
        $this->buyVip($author);
        $product = $this->commentables()[1];
        $root = $this->comment($product, null);
        $reply = Comment::query()->create([
            'commentable_type' => $product->getMorphClass(),
            'commentable_id' => $product->getKey(),
            'root_id' => $root->id,
            'parent_id' => $root->id,
            'user_id' => $author->id,
            'content' => 'پاسخ VIP',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->item($reply, $product)
            ->assertSee('ring-amber-400')
            ->assertSee('کاربر دارای اشتراک VIP فعال');
    }

    public function test_active_vip_author_is_identified_on_post_product_and_video_comments(): void
    {
        $author = User::factory()->create();
        $this->buyVip($author);

        foreach ($this->commentables() as $commentable) {
            $comment = $this->comment($commentable, $author);
            $this->item($comment, $commentable)
                ->assertSee('ring-amber-400')
                ->assertSee('کاربر دارای اشتراک VIP فعال')
                ->assertSee(route('loyalty.members.show', $comment->user_id));
        }
    }

    public function test_vip_badge_follows_current_subscription_and_is_never_shown_for_guests(): void
    {
        $author = User::factory()->create();
        $post = $this->commentables()[0];
        $comment = $this->comment($post, $author);
        $guestComment = $this->comment($post, null);

        $this->item($comment, $post)->assertDontSee('کاربر دارای اشتراک VIP فعال');
        $subscription = $this->buyVip($author);
        $this->item($comment, $post)->assertSee('کاربر دارای اشتراک VIP فعال');
        $this->item($guestComment, $post)->assertDontSee('کاربر دارای اشتراک VIP فعال');

        $subscription->update(['expires_at' => now()->subSecond()]);
        $this->item($comment, $post)->assertDontSee('کاربر دارای اشتراک VIP فعال');

        $subscription->update(['expires_at' => now()->addDay(), 'revoked_at' => now()]);
        $this->item($comment, $post)->assertDontSee('کاربر دارای اشتراک VIP فعال');
    }
}
