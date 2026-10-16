<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Identity\UpdateRoleLoginAlertsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class RoleLoginAlertsController extends Controller
{
    public function edit(Role $role): View
    {
        Gate::authorize('role.edit');

        return view('admin.identity.roles.login-alerts', compact('role'));
    }

    public function update(UpdateRoleLoginAlertsRequest $request, Role $role): RedirectResponse
    {
        // Only notification preferences may change here, including for protected roles.
        $role->update($request->validated());

        return redirect()->route('admin.roles.login-alerts.edit', $role)
            ->with('success', 'تنظیمات اعلان ورود نقش ذخیره شد.');
    }
}
