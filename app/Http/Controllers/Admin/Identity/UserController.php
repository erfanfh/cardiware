<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Application\Identity\Actions\Auth\RegisterUserAction;
use App\Application\Identity\Listings\UserListing;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Identity\IndexUserRequest;
use App\Http\Requests\Admin\Identity\StoreUserRequest;
use App\Http\Requests\Admin\Identity\UpdateUserRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
     * @param IndexUserRequest $request HTTP request with filter parameters
     * @param UserListing $userListing
     * @return View
     */
    public function index(IndexUserRequest $request, UserListing $userListing): View
    {
        $users = $userListing
            ->handle(User::query(), $request->validated())
            ->paginate($userListing->perPage())
            ->withQueryString();

        $statuses = collect(UserStatusEnum::cases())
            ->mapWithKeys(fn(UserStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $roles = Role::pluck('title', 'id')->all();

        $hasActiveFilters = $userListing->hasActiveFilters($request->validated());

        $bulkActionOptions = DefaultBulkActions::set()->toOptions();

        return view('admin.identity.users.index', compact('users', 'roles', 'bulkActionOptions', 'hasActiveFilters', 'statuses'));
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
     * @param RegisterUserAction $registerUserAction
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request, RegisterUserAction $registerUserAction): RedirectResponse
    {
        $data = $request->validated();

        $registerUserAction->execute(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            displayName: $data['display_name'],
            mobile: $data['mobile'],
            email: $data['email'],
            password: $data['password'],
            roleId: $data['role'],
            status: $data['status'],
        );

        return redirect()->route('admin.users.index')
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

        $data['role_id'] = $data['role'];
        unset($data['role']);

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "کاربر '{$user->first_name}' با موفقیت به‌روزرسانی شد.")
            ->with('success_title', 'به‌روزرسانی موفق');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function usersFetch(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $users = User::whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%$query%"])
            ->get(['id', 'full_name']);

        return response()->json($users);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'کاربر با موفقیت حذف شد.');
    }
}
