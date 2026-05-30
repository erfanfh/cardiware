<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Application\Discussion\Listings\CommentListing;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Discussion\IndexCommentRequest;
use App\Infrastructure\Support\BulkActions\CommentBulkActions;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommentListController extends Controller
{
    public function __invoke(IndexCommentRequest $request, CommentListing $commentListing): View
    {
        Gate::authorize('comment.list');

        $baseQuery = Comment::query();

        $comments = $commentListing
            ->handle(
                Comment::query()->with(['user', 'commentable', 'parent.user']),
                $request->validated()
            )
            ->paginate($commentListing->perPage())
            ->withQueryString();

        $bulkActionOptions = CommentBulkActions::set()->toOptions();
        $hasActiveFilters = $commentListing->hasActiveFilters($request->validated());

        $statuses = collect(CommentStatusEnum::cases())
            ->mapWithKeys(fn(CommentStatusEnum $status) => [
                $status->value => $this->statusLabel($status),
            ])
            ->all();

        $types = [
            'root' => 'فقط دیدگاه‌های اصلی',
            'reply' => 'فقط پاسخ‌ها',
        ];

        $statusCounts = [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', CommentStatusEnum::PENDING)->count(),
            'approved' => (clone $baseQuery)->where('status', CommentStatusEnum::APPROVED)->count(),
            'rejected' => (clone $baseQuery)->where('status', CommentStatusEnum::REJECTED)->count(),
        ];

        $selectedPost = Post::query()
            ->where('id', $request->post_id)
            ->get(['id', 'title'])
            ->map(fn($tag) => [
                'value' => (string)$tag->id,
                'label' => $tag->title,
            ])
            ->values()
            ->all();

        return view('admin.discussion.comments.index', compact(
            'comments',
            'selectedPost',
            'bulkActionOptions',
            'hasActiveFilters',
            'statuses',
            'types',
            'statusCounts',
        ));
    }

    protected function statusLabel(CommentStatusEnum $status): string
    {
        return match ($status) {
            CommentStatusEnum::PENDING => 'در انتظار تایید',
            CommentStatusEnum::APPROVED => 'تایید شده',
            CommentStatusEnum::REJECTED => 'رد شده',
        };
    }
}
