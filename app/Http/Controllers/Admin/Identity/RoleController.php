<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
}
