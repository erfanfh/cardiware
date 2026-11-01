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

        UserMediaSubmission::query()->create([
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('gallery.index')
            ->with('success', 'رسانه شما ثبت شد و پس از بررسی مدیر منتشر و امتیازش به حسابتان اضافه می‌شود.');
    }
}
