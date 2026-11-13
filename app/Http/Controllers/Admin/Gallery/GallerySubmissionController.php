<?php

namespace App\Http\Controllers\Admin\Gallery;

use App\Application\Content\Actions\Gallery\ResolveGallerySubmissionPointsAction;
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

    public function update(Request $request, UserMediaSubmission $submission): RedirectResponse
    {
        Gate::authorize('gallery.manage');

        $rules = [
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
        if ($submission->type->value === 'video') {
            $rules['video_url'] = ['sometimes', 'required', 'url:http,https', 'max:600'];
            $rules['video_thumbnail_url'] = ['sometimes', 'nullable', 'url:http,https', 'max:1000'];
        } else {
            $rules['image_url'] = ['sometimes', 'nullable', 'url:http,https', 'max:600'];
        }

        $submission->update($request->validate($rules));

        return redirect()->route('admin.gallery.index', ['status' => $submission->status->value])
            ->with('success', 'اطلاعات رسانه ذخیره شد.');
    }

    public function updateStatus(Request $request, UserMediaSubmission $submission): RedirectResponse
    {
        Gate::authorize('gallery.manage');

        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            // امتیاز دلخواه مدیر هنگام تأیید (خالی = امتیاز پیش‌فرض تنظیمات گالری)
            'points' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $awardedPoints = $submission->points_awarded;

        if ($data['status'] === 'approved') {
            $awardedPoints = $this->awardPointsForSubmission($submission, $data['points'] ?? null) ?? $awardedPoints;
        } elseif ($submission->status->value === 'approved' && (int) $submission->points_awarded > 0) {
            // تأییدِ پس‌گرفته‌شده: امتیازش هم برمی‌گردد (با تأیید دوباره از نو داده می‌شود).
            $this->reversePointsForSubmission($submission);
            $awardedPoints = null;
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
     * امتیاز پیش‌فرض کاهشی است و سقف ماهانه دارد (ResolveGallerySubmissionPointsAction)؛
     * اگر مدیر امتیاز دلخواه وارد کرده باشد، همان ثبت می‌شود.
     */
    private function awardPointsForSubmission(UserMediaSubmission $submission, ?int $overridePoints = null): ?int
    {
        if ($submission->points_awarded !== null) {
            return $submission->points_awarded;
        }

        $points = $overridePoints !== null && $overridePoints > 0
            ? $overridePoints
            : app(ResolveGallerySubmissionPointsAction::class)->execute($submission)['points'];

        if ($points <= 0) {
            return null;
        }

        $account = app(\App\Application\Loyalty\Actions\GetOrCreatePointAccountAction::class)
            ->execute($submission->user);
        $key = app(\App\Application\Loyalty\Support\ReawardableLedgerKey::class)
            ->resolve((int) $account->getKey(), 'gallery-submission:' . $submission->getKey());

        if ($key === null) {
            return null;
        }

        app(\App\Application\Loyalty\Actions\AwardPointsAction::class)->execute(
            new \App\Application\Loyalty\DTOs\AwardPointsDTO(
                account: $account,
                amount: $points,
                reason: 'gallery_submission',
                idempotencyKey: $key,
                reference: $submission,
                description: 'امتیاز رسانه ارسالی در گالری کاربران',
            ),
        );

        return $points;
    }

    private function reversePointsForSubmission(UserMediaSubmission $submission): void
    {
        if ($submission->user === null) {
            return;
        }

        app(\App\Application\Loyalty\Actions\ReverseEarnedPointsForReferenceAction::class)->execute(
            user: $submission->user,
            reference: $submission,
            reason: 'gallery_submission',
            eventKey: 'gallery-unapproved:' . $submission->getKey() . ':' . now()->format('YmdHisu'),
            description: 'برگشت امتیاز رسانه (لغو تأیید)',
        );
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
        $settings->set('image_points', max(0, (int)$request->input('image_points', 80)));
        $settings->set('video_points', max(0, (int)$request->input('video_points', 200)));
        $settings->set('second_points_percent', max(0, min(100, (int)$request->input('second_points_percent', 50))));
        $settings->set('later_points_percent', max(0, min(100, (int)$request->input('later_points_percent', 25))));
        $settings->set('monthly_rewarded_limit', max(0, (int)$request->input('monthly_rewarded_limit', 3)));
        $settings->set('monthly_submission_limit', max(0, (int)$request->input('monthly_submission_limit', 10)));
        $settings->set('submit_enabled', $request->boolean('submit_enabled'));

        return redirect()->route('admin.gallery.index')->with('success', 'تنظیمات برگه ذخیره شد.');
    }
}
