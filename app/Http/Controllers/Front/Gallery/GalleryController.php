<?php

namespace App\Http\Controllers\Front\Gallery;

use App\Domain\Content\Models\UserMediaSubmission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Gallery\StoreUserMediaSubmissionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GalleryController extends Controller
{
    public function index(): View
    {
        $submissions = UserMediaSubmission::query()
            ->approved()
            ->with('user')
            ->latest('approved_at')
            ->latest('id')
            ->paginate(max(6, (int)get_setting('gallery', 'per_page', 12)))
            ->withQueryString();

        $mySubmissions = auth()->check()
            ? UserMediaSubmission::query()
                ->where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'approved', 'rejected'])
                ->latest('id')
                ->limit(5)
                ->get()
            : collect();

        return view('front.gallery.index', [
            'submissions' => $submissions,
            'mySubmissions' => $mySubmissions,
        ]);
    }

    public function store(StoreUserMediaSubmissionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // سقف ارسال ماهانه هر کاربر (عدالت بین کاربران و جلوگیری از ارسال انبوه).
        $limit = (int) get_setting('gallery', 'monthly_submission_limit', 10);
        if ($limit > 0) {
            $sentThisMonth = UserMediaSubmission::query()
                ->where('user_id', auth()->id())
                ->where('created_at', '>=', \App\Application\Content\Actions\Gallery\ResolveGallerySubmissionPointsAction::monthStart())
                ->count();

            if ($sentThisMonth >= $limit) {
                return redirect()
                    ->route('gallery.index')
                    ->withInput()
                    ->withErrors(['title' => "سقف ارسال این ماه ({$limit} رسانه) پر شده است؛ ماه بعد دوباره ارسال کنید."]);
            }
        }

        UserMediaSubmission::query()->create([
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'order_number' => $data['order_number'] ?? null,
            'purchaser_mobile' => $data['purchaser_mobile'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('gallery.index')
            ->with('success', 'رسانه شما ثبت شد و پس از بررسی مدیر منتشر و امتیازش به حسابتان اضافه می‌شود.');
    }
}
