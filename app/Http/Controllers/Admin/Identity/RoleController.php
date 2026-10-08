<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Application\Identity\Listings\RoleListing;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Identity\IndexRoleRequest;
use App\Http\Requests\Admin\Identity\UpdateRoleRequest;
use App\Infrastructure\Support\BulkActions\DeleteBulkActions;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

/**
 * Controller for managing roles in admin panel
 * Handles listing and displaying roles with their statistics
 */
class RoleController extends Controller
{
    /**
     * Display a listing of roles
     *
     * @param IndexRoleRequest $request
     * @param RoleListing $roleListing
     * @return View
     */
    public function index(IndexRoleRequest $request, RoleListing $roleListing): View
    {
        Gate::authorize('role.list');

        $roles = $roleListing
            ->handle(Role::query(), $request->validated())
            ->paginate($roleListing->perPage())
            ->withQueryString();

        $hasActiveFilters = $roleListing->hasActiveFilters($request->validated());

        $selectedPermissions = collect(request('permission_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values();

        $selectedPermissionOptions = Permission::query()
            ->whereIn('id', $selectedPermissions)
            ->get(['id', 'label'])
            ->map(fn($tag) => [
                'value' => (string)$tag->id,
                'label' => $tag->label,
            ])
            ->values()
            ->all();

        $bulkActionOptions = DeleteBulkActions::set()->toOptions();

        return view('admin.identity.roles.index', compact('roles', 'hasActiveFilters', 'bulkActionOptions', 'selectedPermissionOptions'));
    }

    /**
     * Store a newly created role in storage
     *
     * @param UpdateRoleRequest $request
     * @return RedirectResponse
     */
    public function store(UpdateRoleRequest $request): RedirectResponse
    {
        Gate::authorize('role.create');

        $role = Role::create($request->validated());
        app(AuditService::class)->sync($role, 'permissions', $request->permissions ?? []);

        return Redirect::route('admin.roles.index')
            ->with('success', 'نقش با موفقیت ایجاد شد.')
            ->with('success_title', 'عملیات موفق');
    }

    /**
     * Show the form for creating a new role
     *
     * @return View
     */
    public function create(): View
    {
        Gate::authorize('role.create');

        $permissionGroups = Permission::all()
            ->groupBy(fn($p) => explode('.', $p->title)[0])
            ->map(fn($group) => $group->sortBy('title'));

        return view('admin.identity.roles.create', compact('permissionGroups'));
    }

    /**
     * Show the form for editing the specified role
     *
     * @param Role $role The role to edit
     * @return View|RedirectResponse
     */
    public function edit(Role $role)
    {
        Gate::authorize('role.edit');

        // Check if role is editable
        if ($role->is_default || $role->is_super_admin) {
            return Redirect::route('admin.roles.index')
                ->with('error', 'این نقش قابل ویرایش نیست.')
                ->with('error_title', 'خطا');
        }

        $permissionGroups = Permission::all()
            ->groupBy(fn($p) => explode('.', $p->title)[0])
            ->map(fn($group) => $group->sortBy('title'));

        $selectedPermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.identity.roles.edit', compact('role', 'permissionGroups', 'selectedPermissions'));
    }

    /**
     * Update the specified role in storage
     *
     * @param UpdateRoleRequest $request The validated request
     * @param Role $role The role to update
     * @return RedirectResponse
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('role.edit');

        // Check if role is editable
        if ($role->is_default || $role->is_super_admin) {
            return Redirect::route('admin.roles.index')
                ->with('error', 'این نقش قابل ویرایش نیست.')
                ->with('error_title', 'خطا');
        }

        $role->update($request->validated());
        app(AuditService::class)->sync($role, 'permissions', $request->permissions ?? []);

        return Redirect::route('admin.roles.index')
            ->with('success', 'نقش با موفقیت به‌روزرسانی شد.')
            ->with('success_title', 'عملیات موفق');
    }

    /**
     * Delete a role after validation
     * Prevents deletion of default or super admin roles
     * Redirects to assign new role page if role has users
     *
     * @param Role $role The role to delete
     * @return RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('role.delete');

        if ($role->is_default) {
            return back()->with('error', 'نقش پیش‌فرض قابل حذف نیست.')
                ->with('error_title', 'خطا');
        }

        if ($role->is_super_admin) {
            return back()->with('error', 'نقش سوپر ادمین قابل حذف نیست.')
                ->with('error_title', 'خطا');
        }

        if ($role->users()->count() > 0) {
            return back()
                ->with('error', "نقش '{$role->title}' دارای کاربر می باشد و امکان حذف آن وجود ندارد.")
                ->with('error_title', 'خطا');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "نقش '{$role->title}' با موفقیت حذف شد.")
            ->with('success_title', 'حذف موفق');
    }
}
