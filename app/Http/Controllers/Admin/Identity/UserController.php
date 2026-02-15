<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Application\Identity\Actions\Auth\RegisterUserAction;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Identity\StoreUserRequest;
use App\Http\Requests\Admin\Identity\UpdateUserRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
     * Show the form for creating a new user
     *
     * @return View
     */
    public function create(): View
    {
        $roles = Role::query()
            ->orderBy('title')
            ->get();

        return view('admin.identity.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage
     *
     * @param StoreUserRequest $request The validation request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request, RegisterUserAction $registerUserAction): RedirectResponse
    {
        $data = $request->validated();

        $registerUserAction->execute(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            mobile: $data['mobile'],
            email: $data['email'],
            password: $data['password'],
            roleId: $data['role_id']
        );

        return redirect()->route('admin.identity.users.index')
            ->with('success', "کاربر '{$data['first_name']}' با موفقیت ایجاد شد.")
            ->with('success_title', 'ایجاد موفق');
    }

    /**
     * Show the form for editing the specified user
     *
     * @param User $user The user to edit
     * @return View
     */
    public function edit(User $user): View
    {
        $roles = Role::query()
            ->orderBy('title')
            ->get();

        return view('admin.identity.users.edit', compact('user', 'roles'));
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
        $data = $request->validated();

        $isStatusToggle = $request->has('status') && !$request->has('first_name');

        if ($isStatusToggle) {
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

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['status'] = filter_var($data['status'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ? UserStatusEnum::ACTIVE->value : UserStatusEnum::INACTIVE->value;

        $user->update($data);

        return redirect()->route('admin.identity.users.index')
            ->with('success', "کاربر '{$user->first_name}' با موفقیت به‌روزرسانی شد.")
            ->with('success_title', 'به‌روزرسانی موفق');
    }
}
