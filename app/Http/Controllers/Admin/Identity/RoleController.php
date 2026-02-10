<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Identity\StoreRoleRequest;
use App\Http\Requests\Admin\Identity\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->when($request->search, fn($q, $search) =>
            $q->where('title', 'like', "%{$search}%")
            )
            ->when($request->id, fn($q, $id) => $q->where('id', $id))
            ->when($request->default, fn($q) => $q->where('is_default', true))
            ->when($request->super_admin, fn($q) => $q->where('is_super_admin', true))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.identity.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     *
     * @return View
     */
    public function create(): View
    {
        $permissionGroups = Permission::all()
            ->groupBy(fn($p) => explode('.', $p->title)[0])
            ->map(fn($group) => $group->sortBy('title'));

        return view('admin.identity.roles.create', compact('permissionGroups'));
    }

    /**
     * Store a newly created role in storage
     *
     * @param StoreRoleRequest $request The validated request
     * @return RedirectResponse
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create($request->validated());
        $role->permissions()->sync($request->permissions ?? []);

        return Redirect::route('admin.identity.roles.index')
            ->with('success', 'نقش با موفقیت ایجاد شد.')
            ->with('success_title', 'عملیات موفق');
    }

    /**
     * Show the form for editing the specified role
     *
     * @param Role $role The role to edit
     * @return View|RedirectResponse
     */
    public function edit(Role $role)
    {
        // Check if role is editable
        if ($role->is_default || $role->is_super_admin) {
            return Redirect::route('admin.identity.roles.index')
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
        // Check if role is editable
        if ($role->is_default || $role->is_super_admin) {
            return Redirect::route('admin.identity.roles.index')
                ->with('error', 'این نقش قابل ویرایش نیست.')
                ->with('error_title', 'خطا');
        }

        $role->update($request->validated());
        $role->permissions()->sync($request->permissions ?? []);

        return Redirect::route('admin.identity.roles.index')
            ->with('success', 'نقش با موفقیت به‌روزرسانی شد.')
            ->with('success_title', 'عملیات موفق');
    }
}
