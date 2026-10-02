<?php

namespace Tests\Feature\Notification;

use App\Application\Discussion\Actions\CreateCommentAction;
use App\Application\Discussion\Actions\ChangeCommentStatusAction;
use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Application\Discussion\Listings\CommentListing;
use App\Application\Notification\SendInAppNotification;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Notification\Models\UserNotification;
use App\Domain\Notification\NotificationEvents;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RecordDailyLoyaltyVisit;
use App\Http\Middleware\RedirectToCanonicalUrl;
use App\Livewire\Front\Discussion\CommentsList;
use App\Livewire\Front\Discussion\CommentItem;
use App\Livewire\Front\Discussion\ProductCommentsList;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InAppNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user_notifications', 'notification_event_settings', 'comments', 'posts', 'products', 'wallet_withdrawals', 'users', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::create('roles', function (Blueprint $table): void {
            $table->id(); $table->string('title'); $table->boolean('is_default')->default(false);
            $table->boolean('is_super_admin')->default(false); $table->timestamps();
        });
        DB::table('roles')->insert([
            ['id' => 1, 'title' => 'user', 'is_default' => true, 'is_super_admin' => false],
            ['id' => 2, 'title' => 'admin', 'is_default' => false, 'is_super_admin' => true],
        ]);
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->string('username'); $table->string('first_name');
            $table->string('last_name'); $table->string('display_name')->nullable();
            $table->string('email')->nullable(); $table->string('password');
            $table->string('status'); $table->unsignedBigInteger('role_id')->nullable();
            $table->softDeletes(); $table->timestamps();
        });
        Schema::create('posts', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('author_id'); $table->string('title');
            $table->string('slug'); $table->text('excerpt'); $table->longText('body');
            $table->string('status'); $table->timestamp('published_at');
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('comments', function (Blueprint $table): void {
            $table->id(); $table->morphs('commentable');
            $table->unsignedBigInteger('root_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('author_name')->nullable(); $table->string('author_email')->nullable();
            $table->string('ip_address')->nullable(); $table->text('user_agent')->nullable();
            $table->text('content'); $table->string('status');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedTinyInteger('rating_percent')->nullable();
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('dislikes_count')->default(0);
            $table->bigInteger('score')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('pinned_at')->nullable();
            $table->unsignedBigInteger('pinned_by')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id(); $table->string('title'); $table->string('slug');
            $table->string('status'); $table->timestamp('published_at');
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('wallet_withdrawals', function (Blueprint $table): void {
            $table->id(); $table->string('public_token')->nullable();
            $table->unsignedBigInteger('user_id'); $table->string('status');
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_02_130000_create_in_app_notifications_tables.php'))->up();
    }

    public function test_comment_creation_and_reply_target_the_right_users_once(): void
    {
        $author = $this->user('author');
        $commenter = $this->user('commenter');
        $replier = $this->user('replier');
        $post = $this->createPost($author);

        $root = $this->createComment($post, $commenter->id, null, 'دیدگاه اول');
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $author->id,
            'event_key' => NotificationEvents::COMMENT_CREATED,
            'subject_id' => $root->id,
            'read_at' => null,
        ]);
        $this->assertNotNull(UserNotification::query()->first()->sent_at);
        $this->assertSame(
            route('admin.comments.index', ['id' => $root->id], false).'#comment-'.$root->id,
            UserNotification::query()->first()->url,
        );
        $listed = app(CommentListing::class)->handle(Comment::query(), ['id' => $root->id])->get();
        $this->assertSame([$root->id], $listed->pluck('id')->all());
        $this->assertStringContainsString(
            'id="comment-'.$root->id.'"',
            view('admin.discussion.comments.partials.list-table', [
                'comments' => $listed,
                'commentIds' => [$root->id],
            ])->render(),
        );
        $notification = UserNotification::query()->firstOrFail();
        $this->withoutMiddleware([RecordDailyLoyaltyVisit::class, RedirectToCanonicalUrl::class, HandleSeoRedirectsMiddleware::class]);
        $this->actingAs($author)->post(route('account.notifications.read', $notification))
            ->assertRedirect($notification->url);

        $reply = $this->createComment($post, $replier->id, $root->id, 'پاسخ اول');
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $commenter->id,
            'event_key' => NotificationEvents::COMMENT_REPLIED,
            'subject_id' => $reply->id,
        ]);
        $this->assertStringContainsString('notification_comment='.$root->id, UserNotification::query()
            ->where('event_key', NotificationEvents::COMMENT_REPLIED)->value('url'));
        app(SendInAppNotification::class)->commentCreated($reply->id);
        $this->assertDatabaseCount('user_notifications', 2);

        $this->createComment($post, $author->id, null, 'نظر نویسنده');
        $this->createComment($post, $commenter->id, $root->id, 'پاسخ به خودم');
        $this->assertDatabaseCount('user_notifications', 2);
    }

    public function test_approval_and_disabled_events_are_independent(): void
    {
        $author = $this->user('author2');
        $commenter = $this->user('commenter2');
        $post = $this->createPost($author);

        DB::table('notification_event_settings')->insert([
            'event_key' => NotificationEvents::COMMENT_CREATED,
            'is_enabled' => false,
        ]);
        $comment = $this->createComment($post, $commenter->id, null, 'دیدگاه دوم');
        $this->assertDatabaseCount('user_notifications', 0);

        $comment->forceFill(['status' => CommentStatusEnum::APPROVED, 'approved_at' => now()])->save();
        app(SendInAppNotification::class)->commentApproved($comment->id);
        app(SendInAppNotification::class)->commentApproved($comment->id);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $commenter->id,
            'event_key' => NotificationEvents::COMMENT_APPROVED,
        ]);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertStringContainsString('notification_comment='.$comment->id, UserNotification::query()->first()->url);
    }

    public function test_automatically_approved_admin_comment_does_not_notify_its_author(): void
    {
        $author = $this->user('post-author');
        $admin = $this->user('auto-approved-admin', true);
        $post = $this->createPost($author);

        $this->actingAs($admin);
        $comment = $this->createComment($post, $admin->id, null, 'دیدگاه ادمین');

        $this->assertTrue($comment->isApproved());
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $author->id,
            'event_key' => NotificationEvents::COMMENT_CREATED,
            'subject_id' => $comment->id,
        ]);
        $this->assertDatabaseMissing('user_notifications', [
            'user_id' => $admin->id,
            'event_key' => NotificationEvents::COMMENT_APPROVED,
            'subject_id' => $comment->id,
        ]);

        $pendingComment = Comment::query()->create([
            'commentable_type' => $post->getMorphClass(),
            'commentable_id' => $post->id,
            'user_id' => $admin->id,
            'content' => 'دیدگاه قدیمی در انتظار تأیید',
            'status' => CommentStatusEnum::PENDING,
        ]);
        app(ChangeCommentStatusAction::class)->execute($pendingComment, CommentStatusEnum::APPROVED);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $admin->id,
            'event_key' => NotificationEvents::COMMENT_APPROVED,
            'subject_id' => $pendingComment->id,
        ]);
    }

    public function test_comment_link_opens_the_visible_reply_branch_without_exposing_pending_reply(): void
    {
        $author = $this->user('postauthor');
        $parentAuthor = $this->user('parentauthor');
        $replier = $this->user('replier2');
        $post = $this->createPost($author);
        $root = $this->createComment($post, $parentAuthor->id, null, 'ریشه');
        $reply = $this->createComment($post, $replier->id, $root->id, 'پاسخ');
        DB::table('comments')->where('id', $root->id)->update(['status' => 'approved', 'approved_at' => now()]);

        $this->actingAs($parentAuthor);
        request()->query->set('notification_comment', (string) $reply->id);
        $pendingList = new CommentsList();
        $pendingList->mount($post);
        $this->assertFalse($pendingList->isOpen);
        $this->assertSame([], $pendingList->notificationTargetPath);

        DB::table('comments')->where('id', $reply->id)->update(['status' => 'approved', 'approved_at' => now()]);
        $approvedList = new CommentsList();
        $approvedList->mount($post);
        $this->assertTrue($approvedList->isOpen);
        $this->assertSame($root->id, $approvedList->notificationTargetRootId);
        $this->assertSame([$root->id, $reply->id], $approvedList->notificationTargetPath);

        $rootItem = new CommentItem();
        $rootItem->mount(
            commentId: $root->id,
            commentableType: $post->getMorphClass(),
            commentableId: $post->id,
            notificationTargetPath: $approvedList->notificationTargetPath,
        );
        $this->assertTrue($rootItem->repliesOpen);
        $this->assertTrue($rootItem->repliesLoaded);

        for ($i = 0; $i < 12; $i++) {
            DB::table('comments')->insert([
                'commentable_type' => $post->getMorphClass(),
                'commentable_id' => $post->id,
                'user_id' => $replier->id,
                'content' => 'دیدگاه '. $i,
                'status' => 'approved',
                'approved_at' => now(),
                'created_at' => now()->addSeconds($i),
                'updated_at' => now(),
            ]);
        }
        $this->assertSame($root->id, $approvedList->comments()->items()[0]->id);
    }

    public function test_withdrawal_final_result_and_read_receipt_are_private(): void
    {
        $owner = $this->user('owner');
        $other = $this->user('other');
        $withdrawalId = DB::table('wallet_withdrawals')->insertGetId([
            'user_id' => $owner->id, 'status' => 'paid', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $sender = app(SendInAppNotification::class);
        $sender->withdrawalResolved($withdrawalId);
        $sender->withdrawalResolved($withdrawalId);

        $notification = UserNotification::query()->firstOrFail();
        $this->assertSame($owner->id, $notification->user_id);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->withoutMiddleware([RecordDailyLoyaltyVisit::class, RedirectToCanonicalUrl::class, HandleSeoRedirectsMiddleware::class]);

        $this->actingAs($other)->post(route('account.notifications.read', $notification))->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
        $this->actingAs($owner)->post(route('account.notifications.read', $notification))
            ->assertRedirect(parse_url(route('account.wallet'), PHP_URL_PATH).'#wallet-withdrawal');
        $firstReadAt = $notification->fresh()->read_at;
        $this->assertNotNull($firstReadAt);
        $this->post(route('account.notifications.read', $notification))->assertRedirect();
        $this->assertEquals($firstReadAt, $notification->fresh()->read_at);
    }

    public function test_admin_can_disable_one_event_and_reject_unknown_keys(): void
    {
        $admin = $this->user('admin', true);
        $this->actingAs($admin)->put(route('admin.settings.notifications.update'), [
            'event_key' => NotificationEvents::COMMENT_REPLIED,
            'is_enabled' => '0',
        ])->assertRedirect();
        $this->assertDatabaseHas('notification_event_settings', [
            'event_key' => NotificationEvents::COMMENT_REPLIED,
            'is_enabled' => false,
        ]);

        $this->put(route('admin.settings.notifications.update'), [
            'event_key' => 'made.up', 'is_enabled' => '1',
        ])->assertSessionHasErrors('event_key');
        $this->assertDatabaseCount('notification_event_settings', 1);
    }

    public function test_read_all_only_changes_the_signed_in_users_notifications(): void
    {
        $owner = $this->user('readall-owner');
        $other = $this->user('readall-other');
        foreach ([$owner, $other] as $user) {
            $withdrawalId = DB::table('wallet_withdrawals')->insertGetId([
                'user_id' => $user->id, 'status' => 'rejected',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            app(SendInAppNotification::class)->withdrawalResolved($withdrawalId);
        }

        $this->withoutMiddleware([RecordDailyLoyaltyVisit::class, RedirectToCanonicalUrl::class, HandleSeoRedirectsMiddleware::class]);
        $this->actingAs($owner)->post(route('account.notifications.read-all'))->assertRedirect();

        $this->assertNotNull(UserNotification::query()->where('user_id', $owner->id)->value('read_at'));
        $this->assertNull(UserNotification::query()->where('user_id', $other->id)->value('read_at'));
    }

    public function test_approved_product_comment_links_to_its_open_discussion(): void
    {
        $commenter = $this->user('product-commenter');
        $productId = DB::table('products')->insertGetId([
            'title' => 'محصول آزمایشی', 'slug' => 'notification-product',
            'status' => 'active', 'published_at' => now()->subDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $product = Product::query()->findOrFail($productId);
        $comment = Comment::query()->create([
            'commentable_type' => $product->getMorphClass(),
            'commentable_id' => $product->id,
            'user_id' => $commenter->id,
            'content' => 'دیدگاه محصول',
            'status' => CommentStatusEnum::APPROVED,
            'approved_at' => now(),
        ]);

        app(SendInAppNotification::class)->commentApproved($comment->id);
        $notification = UserNotification::query()->firstOrFail();
        $this->assertStringContainsString('notification_comment='.$comment->id.'#comments', $notification->url);

        request()->query->set('notification_comment', (string) $comment->id);
        $list = new ProductCommentsList();
        $list->mount($product);
        $this->assertTrue($list->isOpen);
        $this->assertSame([$comment->id], $list->notificationTargetPath);
    }

    public function test_account_sidebar_and_admin_header_show_only_unread_notifications_of_current_user(): void
    {
        $admin = $this->user('notification-admin', true);
        $other = $this->user('notification-other');
        foreach (['اول', 'دوم', 'سوم'] as $index => $title) {
            UserNotification::query()->create([
                'user_id' => $admin->id,
                'event_key' => NotificationEvents::COMMENT_APPROVED,
                'subject_type' => Comment::class,
                'subject_id' => $index + 1,
                'title' => 'اعلان '.$title,
                'body' => 'متن اعلان',
                'url' => '/account/notifications',
                'sent_at' => now()->addSeconds($index),
            ]);
        }
        UserNotification::query()->where('subject_id', 1)->update(['read_at' => now()]);
        UserNotification::query()->create([
            'user_id' => $other->id,
            'event_key' => NotificationEvents::COMMENT_APPROVED,
            'subject_type' => Comment::class,
            'subject_id' => 99,
            'title' => 'اعلان کاربر دیگر',
            'sent_at' => now(),
        ]);

        $this->actingAs($admin);
        $admin->setRelation('wallet', null);
        foreach (['purchase_accesses_count', 'paid_orders_count', 'tickets_count', 'favorite_products_count', 'loyalty_points', 'open_tickets_count'] as $stat) {
            $admin->setAttribute($stat, 0);
        }

        $sidebar = view('front.account.partials.sidebar', ['user' => $admin])->render();
        $this->assertStringContainsString('2 اعلان خوانده‌نشده', $sidebar);
        $this->assertStringNotContainsString('3 اعلان خوانده‌نشده', $sidebar);

        $topbar = view('admin.partials.topbar')->render();
        $this->assertStringContainsString('2 اعلان خوانده‌نشده', $topbar);
        $this->assertStringContainsString('اعلان سوم', $topbar);
        $this->assertStringNotContainsString('اعلان کاربر دیگر', $topbar);

        UserNotification::query()->where('user_id', $admin->id)->whereNull('read_at')->update(['read_at' => now()]);
        $this->assertStringNotContainsString('اعلان خوانده‌نشده', view('admin.partials.topbar')->render());
        $this->assertStringNotContainsString('اعلان خوانده‌نشده', view('front.account.partials.sidebar', ['user' => $admin])->render());
    }

    private function user(string $username, bool $admin = false): User
    {
        $id = DB::table('users')->insertGetId([
            'username' => $username, 'first_name' => 'کاربر', 'last_name' => $username,
            'display_name' => 'کاربر '.$username, 'email' => $username.'@example.test',
            'password' => 'unused', 'status' => 'active', 'role_id' => $admin ? 2 : 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return User::query()->findOrFail($id);
    }

    private function createPost(User $author): Post
    {
        $id = DB::table('posts')->insertGetId([
            'author_id' => $author->id, 'title' => 'مطلب آزمایشی',
            'slug' => 'notification-test-'.$author->id,
            'excerpt' => 'آزمایش', 'body' => 'متن',
            'status' => 'active', 'published_at' => now()->subDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return Post::query()->findOrFail($id);
    }

    private function createComment(Post $post, int $userId, ?int $parentId, string $content): Comment
    {
        return app(CreateCommentAction::class)->execute(new CreateCommentDataDTO(
            commentable: $post, parentId: $parentId, userId: $userId,
            authorName: null, authorEmail: null, content: $content,
            ratingPercent: $parentId ? null : 80, ipAddress: null, userAgent: null,
            allowReplyToUnapprovedParent: true, bypassCooldown: true,
        ));
    }
}
