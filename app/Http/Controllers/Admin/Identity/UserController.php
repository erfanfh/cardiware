<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Identity\UpdateUserRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing users in admin panel
 * Handles listing and displaying users with filters
 */
class UserController extends Controller
{
    /**
     * Display paginated list of users with filters
     *
     * @param Request $request HTTP request with filter parameters
     * @return View
     */
    public function index(Request $request): View
    {
        // Get all roles for filter dropdown
        $roles = Role::query()
            ->orderBy('title')
            ->get();

        $users = User::query()
            ->with('role')
            ->when($request->search, fn($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            })
            )
            ->when($request->role_id, fn($q, $roleId) => $q->where('role_id', $roleId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.identity.users.index', compact('users', 'roles'));
    }

    /**
     * Update user status (activate/deactivate)
     * Only status field is allowed to be updated via this endpoint
     *
     * @param UpdateUserRequest $request The validation request
     * @param User $user The user to update
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        // Prevent deactivating super admin users
        if ($user->role->is_super_admin && !$request->boolean('status')) {
            return back()->with('error', 'نمی‌توانید کاربر سوپر ادمین را غیرفعال کنید.')
                ->with('error_title', 'خطا');
        }

        $user->update([
            'status' => $request->boolean('status') ? UserStatusEnum::ACTIVE : UserStatusEnum::INACTIVE,
        ]);

        $name = $user->display_name ?? $user->first_name;

        $message = $request->boolean('status')
            ? "کاربر '{$name}' فعال شد."
            : "کاربر '{$name}' غیرفعال شد.";


        return back()->with('success', $message)
            ->with('success_title', 'عملیات موفق');
    }
}
