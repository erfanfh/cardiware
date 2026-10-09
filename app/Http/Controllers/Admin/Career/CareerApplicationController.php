<?php

namespace App\Http\Controllers\Admin\Career;

use App\Domain\Career\Models\CareerApplication;
use App\Domain\Career\Models\CareerPosition;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CareerApplicationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('career-application.list');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:new,reviewing,interview,accepted,rejected'],
            'career_position_id' => ['nullable', 'integer', 'exists:career_positions,id'],
            'viewed' => ['nullable', 'in:unseen,seen'],
        ]);
        $applications = CareerApplication::query()->with('position')
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('full_name', 'like', '%'.$q.'%')->orWhere('phone', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['career_position_id'] ?? null, fn ($query, $id) => $query->where('career_position_id', $id))
            ->when($filters['viewed'] ?? null, fn ($query, $viewed) => $viewed === 'unseen' ? $query->whereNull('viewed_at') : $query->whereNotNull('viewed_at'))
            ->latest()->paginate(20)->withQueryString();
        $positions = CareerPosition::withTrashed()->orderBy('title')->pluck('title', 'id')->all();

        return view('admin.career.applications.index', compact('applications', 'positions', 'filters'));
    }

    public function show(CareerApplication $careerApplication): View
    {
        Gate::authorize('career-application.list');

        $this->markViewed($careerApplication);

        return view('admin.career.applications.show', ['application' => $careerApplication->load('position', 'reviewer', 'viewer')]);
    }

    public function update(Request $request, CareerApplication $careerApplication): RedirectResponse
    {
        Gate::authorize('career-application.review');

        $data = $request->validate([
            'status' => ['required', 'in:new,reviewing,interview,accepted,rejected'],
            'internal_notes' => ['nullable', 'string', 'max:10000'],
        ]);
        $careerApplication->update($data + [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'وضعیت درخواست ذخیره شد.');
    }

    public function resume(CareerApplication $careerApplication, string $resumeToken)
    {
        Gate::authorize('career-application.list');

        abort_unless(
            $careerApplication->resume_download_token &&
            hash_equals($careerApplication->resume_download_token, $resumeToken),
            404,
        );
        abort_unless($careerApplication->resume_path && Storage::disk('local')->exists($careerApplication->resume_path), 404);

        $this->markViewed($careerApplication);

        return Storage::disk('local')->download($careerApplication->resume_path, $careerApplication->resume_original_name ?: 'resume');
    }

    private function markViewed(CareerApplication $careerApplication): void
    {
        if ($careerApplication->viewed_at !== null) {
            return;
        }

        DB::transaction(function () use ($careerApplication): void {
            $viewedAt = now();
            $viewerId = auth()->id();
            $updated = CareerApplication::query()->whereKey($careerApplication->id)->whereNull('viewed_at')->update([
                'viewed_at' => $viewedAt,
                'viewed_by' => $viewerId,
            ]);

            if ($updated) {
                app(AuditService::class)->recordChange(
                    $careerApplication,
                    ['viewed_at' => null, 'viewed_by' => null],
                    ['viewed_at' => $viewedAt->toDateTimeString(), 'viewed_by' => $viewerId],
                );
            }
        });
        $careerApplication->refresh();
    }
}
