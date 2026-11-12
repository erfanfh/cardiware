<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * مرکز بررسی معرفی‌های (بیو) باشگاه VIP: لیست همهٔ درخواست‌ها با فیلتر وضعیت
 * و امکان تایید/رد درجا (اکشن روی UserController::moderateClubBio سوار است).
 */
final class ClubBioModerationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('user.edit');

        $status = $request->query('status', 'pending');

        if (! in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $counts = User::query()
            ->whereNotNull('club_bio')
            ->orWhere('club_bio_status', 'pending')
            ->selectRaw("club_bio_status, count(*) as total")
            ->groupBy('club_bio_status')
            ->pluck('total', 'club_bio_status');

        $bios = User::query()
            ->when($status !== 'all', fn ($query) => $query->where('club_bio_status', $status))
            ->where(function ($query) {
                $query->whereNotNull('club_bio')->orWhere('club_bio_status', 'pending');
            })
            ->orderByDesc('club_bio_edited_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.identity.users.club-bios', [
            'bios' => $bios,
            'status' => $status,
            'counts' => [
                'pending' => (int) ($counts['pending'] ?? 0),
                'approved' => (int) ($counts['approved'] ?? 0),
                'rejected' => (int) ($counts['rejected'] ?? 0),
                'all' => (int) $counts->sum(),
            ],
        ]);
    }
}
