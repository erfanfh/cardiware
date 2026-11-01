<?php

namespace App\Http\Controllers\Admin\Gallery;

use App\Domain\Content\Enums\UserMediaSubmissionStatusEnum;
use App\Domain\Content\Models\UserMediaSubmission;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Setting\Groups\GallerySettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GallerySubmissionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('gallery.manage');

        $status = $request->input('status');

        $query = UserMediaSubmission::query()
            ->with('user')
            ->latest('id');

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        return view('admin.gallery.index', [
            'submissions' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => [
                'all' => UserMediaSubmission::query()->count(),
                'pending' => UserMediaSubmission::query()->pending()->count(),
                'approved' => UserMediaSubmission::query()->approved()->count(),
                'rejected' => UserMediaSubmission::query()->where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, UserMediaSubmission $submission): RedirectResponse
    {
        Gate::authorize('gallery.manage');

        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $awardedPoints = $submission->points_awarded;

        if ($data['status'] === 'approved') {
            $awardedPoints = $this->awardPointsForSubmission($submission) ?? $awardedPoints;
        }

        $submission->update([
            'status' => $data['status'],
            'approved_at' => $data['status'] === 'approved' ? now() : null,
            'points_awarded' => $awardedPoints,
        ]);

        return redirect()
            ->route('admin.gallery.index', ['status' => $submission->status->value])
            ->with('success', 'وضعیت رسانه به‌روزرسانی شد.');
    }

    /**
     * با تأیید هر رسانه، امتیاز باشگاه به ارسال‌کننده داده می‌شود (یک‌بار برای هر ارسال).
     */
    private function awardPointsForSubmission(UserMediaSubmission $submission): ?int
    {
        if ($submission->points_awarded !== null) {
            return $submission->points_awarded;
        }

        $points = (int)(get_setting('gallery', $submission->type->value === 'video' ? 'video_points' : 'image_points', 150));

        if ($points <= 0) {
            return null;
        }

        $account = app(\App\Application\Loyalty\Actions\GetOrCreatePointAccountAction::class)
            ->execute($submission->user);

        app(\App\Application\Loyalty\Actions\AwardPointsAction::class)->execute(
            new \App\Application\Loyalty\DTOs\AwardPointsDTO(
                account: $account,
                amount: $points,
                reason: 'gallery_submission',
                idempotencyKey: 'gallery-submission:' . $submission->getKey(),
                reference: $submission,
                description: 'امتیاز رسانه ارسالی در گالری کاربران',
            ),
        );

        return $points;
    }

    public function destroy(UserMediaSubmission $submission): RedirectResponse
    {
        Gate::authorize('gallery.manage');

        $path = public_path($submission->image_path);
        if ($submission->image_path && is_file($path)) {
            @unlink($path);
        }

        $submission->delete();

        return redirect()
            ->route('admin.gallery.index')
            ->with('success', 'رسانه حذف شد.');
    }

    public function storeSettings(Request $request): RedirectResponse
    {
        Gate::authorize('gallery.manage');

        $settings = app(GallerySettings::class);
        $settings->set('page_title', (string)$request->input('page_title', 'رسانه‌های ارسالی کاربران'));
        $settings->set('page_subtitle', (string)$request->input('page_subtitle', ''));
        $settings->set('per_page', max(3, (int)$request->input('per_page', 9)));
        $settings->set('image_points', max(0, (int)$request->input('image_points', 150)));
        $settings->set('video_points', max(0, (int)$request->input('video_points', 500)));
        $settings->set('submit_enabled', $request->boolean('submit_enabled'));

        return redirect()->route('admin.gallery.index')->with('success', 'تنظیمات برگه ذخیره شد.');
    }
}
