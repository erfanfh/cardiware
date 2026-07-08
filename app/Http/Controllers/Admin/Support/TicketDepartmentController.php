<?php

namespace App\Http\Controllers\Admin\Support;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketDepartmentController
{
    public function __invoke(): View
    {
        Gate::authorize('ticket.manage-departments');

        return view('admin.support.tickets.departments');
    }
}
