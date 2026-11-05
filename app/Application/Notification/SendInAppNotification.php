<?php

namespace App\Application\Notification;

use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Financial\Enums\WalletWithdrawalStatusEnum;
use App\Domain\Financial\Models\WalletWithdrawal;
use App\Domain\Notification\Models\UserNotification;
use App\Domain\Notification\NotificationEvents;
use App\Domain\Video\Models\Video;
use Illuminate\Support\Facades\DB;

class SendInAppNotification
{
    public function commentCreated(int $commentId): void
    {
        $comment = Comment::query()->with(['parent', 'commentable'])->find($commentId);
        if (!$comment) {
            return;
        }

        if ($comment->isReply()) {
            $recipientId = $comment->parent?->user_id;
            if ($recipientId && (int) $recipientId !== (int) $comment->user_id) {
                $this->send(
                    NotificationEvents::COMMENT_REPLIED, (int) $recipientId,
                    $comment, 'پاسخ تازه به دیدگاه شما',
                    $comment->isApproved()
                        ? 'پاسخی برای دیدگاه شما منتشر شد.'
                        : 'پاسخی برای دیدگاه شما ثبت شد و پس از تأیید نمایش داده می‌شود.',
                    $this->commentUrl($comment),
                );
            }
            return;
        }

        $commentable = $comment->commentable;
        if ($commentable instanceof Post && $commentable->author_id && (int) $commentable->author_id !== (int) $comment->user_id) {
            $this->send(
                NotificationEvents::COMMENT_CREATED, (int) $commentable->author_id,
                $comment, 'دیدگاه تازه برای مطلب شما',
                $comment->isApproved()
                    ? 'برای «'.$commentable->title.'» دیدگاه تازه‌ای منتشر شد.'
                    : 'برای «'.$commentable->title.'» دیدگاهی ثبت شد و پس از تأیید نمایش داده می‌شود.',
                $this->adminCommentUrl($comment),
            );
        }

        if ($commentable instanceof Video && $commentable->author_id && (int) $commentable->author_id !== (int) $comment->user_id) {
            $this->send(
                NotificationEvents::VIDEO_COMMENT_CREATED, (int) $commentable->author_id,
                $comment, 'دیدگاه تازه برای ویدیوی شما',
                $comment->isApproved()
                    ? 'برای «'.$commentable->title.'» دیدگاه تازه‌ای منتشر شد.'
                    : 'برای «'.$commentable->title.'» دیدگاهی ثبت شد و پس از تأیید نمایش داده می‌شود.',
                $this->adminCommentUrl($comment),
            );
        }

        if ($commentable instanceof Product && $commentable->store) {
            foreach ($commentable->store->sellers()->pluck('users.id') as $sellerId) {
                if ((int) $sellerId === (int) $comment->user_id) {
                    continue;
                }

                $this->send(
                    NotificationEvents::PRODUCT_COMMENT_CREATED, (int) $sellerId,
                    $comment, 'دیدگاه تازه برای محصول شما',
                    $comment->isApproved()
                        ? 'برای «'.$commentable->title.'» دیدگاه تازه‌ای منتشر شد.'
                        : 'برای «'.$commentable->title.'» دیدگاهی ثبت شد و پس از تأیید نمایش داده می‌شود.',
                    route('seller.stores.comments', [$commentable->store, 'comment' => $comment->id], false).'#comment-'.$comment->id,
                );
            }
        }
    }

    public function commentApproved(int $commentId): void
    {
        $comment = Comment::query()->with('commentable')->find($commentId);
        if (!$comment || !$comment->isApproved() || !$comment->user_id) {
            return;
        }

        $this->send(
            NotificationEvents::COMMENT_APPROVED, (int) $comment->user_id,
            $comment, 'دیدگاه شما تأیید شد',
            'دیدگاه شما منتشر شد.', $this->commentUrl($comment),
        );
    }

    public function withdrawalResolved(int $withdrawalId): void
    {
        $withdrawal = WalletWithdrawal::query()->find($withdrawalId);
        if (!$withdrawal || !in_array($withdrawal->status, [WalletWithdrawalStatusEnum::PAID, WalletWithdrawalStatusEnum::REJECTED], true)) {
            return;
        }

        $paid = $withdrawal->status === WalletWithdrawalStatusEnum::PAID;
        $this->send(
            NotificationEvents::WITHDRAWAL_RESOLVED, (int) $withdrawal->user_id,
            $withdrawal, $paid ? 'درخواست برداشت واریز شد' : 'درخواست برداشت رد شد',
            $paid ? 'واریز درخواست برداشت شما ثبت شد.' : 'درخواست برداشت شما رد شد. جزئیات را در کیف پول ببینید.',
            parse_url(route('account.wallet'), PHP_URL_PATH).'#wallet-withdrawal',
        );
    }

    public function loyaltyChampion(\App\Domain\Loyalty\Models\LoyaltyChampion $champion): void
    {
        $parts = array_filter([
            $champion->points_awarded > 0 ? number_format($champion->points_awarded) . ' امتیاز' : null,
            $champion->coupon_code ? 'کد تخفیف ' . $champion->coupon_code : null,
            $champion->raffle_tickets > 0 ? number_format($champion->raffle_tickets) . ' کد قرعه‌کشی' : null,
            $champion->grand_prize ? 'جایزه ویژه: ' . $champion->grand_prize : null,
        ]);

        $this->send(
            NotificationEvents::LOYALTY_CHAMPION, (int) $champion->user_id, $champion,
            'تبریک! ' . $champion->title(),
            'جایزه‌های شما: ' . implode('، ', $parts) . '.',
            parse_url(route('loyalty.hall-of-fame'), PHP_URL_PATH),
        );
    }

    public function sellerReward(\App\Domain\Marketplace\Models\StoreReward $reward): void
    {
        $parts = array_filter([
            $reward->commission_bonus_bp > 0
                ? number_format($reward->commission_bonus_bp / 100, 1) . '٪ سهم بیشتر از هر فروش تا ' . jalali_date($reward->bonus_ends_at)
                : null,
            $reward->coupon_code ? 'کد تخفیف ' . $reward->coupon_code : null,
            \App\Domain\Marketplace\Models\StoreSpotlight::query()->where('store_reward_id', $reward->getKey())->exists()
                ? 'نمایش محصولات در صفحه اصلی'
                : null,
        ]);

        foreach ($reward->store?->sellers ?? [] as $seller) {
            $this->send(
                NotificationEvents::SELLER_REWARD, (int) $seller->getKey(), $reward,
                'تبریک! ' . $reward->title(),
                'فروشگاه ' . $reward->store->title . ' جایزه گرفت: ' . implode('، ', $parts) . '.',
                null,
            );
        }
    }

    private function commentUrl(Comment $comment): ?string
    {
        $commentable = $comment->commentable;
        if ((!$commentable instanceof Post && !$commentable instanceof Product && !$commentable instanceof Video)
            || !$commentable->isPubliclyVisible()) {
            return null;
        }

        $path = parse_url($commentable->url, PHP_URL_PATH);
        if (!is_string($path)) {
            return null;
        }

        $targetId = $comment->isApproved()
            ? $comment->id
            : ($comment->parent_id ?: null);

        $query = $targetId ? '?notification_comment='.$targetId : '?show_comments=1';

        return $path.$query.($commentable instanceof Post ? '#discussion-comments' : '#comments');
    }

    private function adminCommentUrl(Comment $comment): string
    {
        return route('admin.comments.index', ['id' => $comment->id], false).'#comment-'.$comment->id;
    }

    private function send(string $eventKey, int $userId, object $subject, string $title, string $body, ?string $url): void
    {
        if (!array_key_exists($eventKey, NotificationEvents::LABELS)) {
            return;
        }
        $enabled = DB::table('notification_event_settings')->where('event_key', $eventKey)->value('is_enabled');
        if ($enabled !== null && !(bool) $enabled) {
            return;
        }

        UserNotification::query()->firstOrCreate(
            [
                'user_id' => $userId,
                'event_key' => $eventKey,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
            ],
            ['title' => $title, 'body' => $body, 'url' => $url, 'sent_at' => now()],
        );
    }
}
