<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Models\Order;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $startOfMonth = Jalalian::now()->getFirstDayOfMonth()->toCarbon()->startOfDay();
        $endOfMonth = Jalalian::now()->getEndDayOfMonth()->toCarbon()->endOfDay();

        $startOfToday = Carbon::today();
        $endOfToday = Carbon::today()->endOfDay();

        $stats = [
            'users' => User::count(),
            'posts' => Post::count(),
            'month_posts' => Post::whereBetween('published_at', [$startOfMonth, $endOfMonth])->count(),
            'inactive_posts' => Post::where('status', PostStatusEnum::INACTIVE)->count(),
            'projects' => Project::count(),
            'active_projects' => Project::where('status', ProjectStatusEnum::ACTIVE)->count(),
            'products' => Product::count(),
            'active_products' => Product::where('status', ProductStatusEnum::ACTIVE)->count(),
            'inactive_products' => Product::whereNot('status', ProductStatusEnum::ACTIVE)->count(),
            'month_products' => Product::whereBetween('published_at', [$startOfMonth, $endOfMonth])->count(),
            'comments' => Comment::count(),
            'pending_comments' => Comment::where('status', CommentStatusEnum::PENDING)->count(),
            'today_revenue' => Order::query()
                ->paid()
                ->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$startOfToday, $endOfToday])
                ->sum('total_amount'),
            'month_revenue' => Order::query()
                ->paid()
                ->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->sum('total_amount'),
        ];

        $topYesterdayPosts = $this->getTopYesterdayPosts();
        $recentComments = Comment::with(['user'])->latest()->limit(3)->get();

        return view('admin.dashboard', compact('stats', 'topYesterdayPosts', 'recentComments'));
    }

    private function getTopYesterdayPosts(): Collection
    {
        $topYesterdayPostRows = DB::table('page_view_details')
            ->select('pageable_id', 'count as views_count')
            ->where('pageable_type', Post::class)
            ->whereDate('date', Carbon::yesterday())
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $postIds = $topYesterdayPostRows->pluck('pageable_id');

        $posts = Post::query()
            ->whereIn('id', $postIds)
            ->get()
            ->keyBy('id');

        return $topYesterdayPostRows
            ->map(function ($row) use ($posts) {
                $post = $posts->get($row->pageable_id);

                if (!$post) {
                    return null;
                }

                $post->views_count = (int)$row->views_count;

                return $post;
            })
            ->filter()
            ->values();
    }
}
