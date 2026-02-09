<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
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
        $users = User::query()
            ->with('role')
            ->when($request->search, fn($q, $search) =>
            $q->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            )
            ->when($request->role_id, fn($q, $roleId) => $q->where('role_id', $roleId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.identity.users.index', compact('users'));
    }
}
