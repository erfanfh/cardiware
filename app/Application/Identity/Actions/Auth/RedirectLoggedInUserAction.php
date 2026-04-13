<?php

namespace App\Application\Identity\Actions\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Livewire\Features\SupportRedirects\Redirector;

class RedirectLoggedInUserAction
{
    /**
     * @return RedirectResponse|Redirector
     */
    public function execute(): RedirectResponse|Redirector
    {
        if (!Gate::allows('admin.display'))
            return redirect()->route('account');
        else
            return redirect()->route('admin.dashboard');
    }
}
