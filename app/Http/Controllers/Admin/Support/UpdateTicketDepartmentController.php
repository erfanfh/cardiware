<?php

namespace App\Http\Controllers\Admin\Support;

use App\Application\Support\Actions\ChangeTicketDepartmentAction;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketDepartment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Support\UpdateTicketDepartmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UpdateTicketDepartmentController extends Controller
{
    public function __invoke(
        UpdateTicketDepartmentRequest $request,
        Ticket                        $ticket,
        ChangeTicketDepartmentAction  $changeTicketDepartmentAction,
    ): RedirectResponse
    {
        Gate::authorize('ticket.change-department');

        $department = TicketDepartment::query()
            ->where('is_active', true)
            ->findOrFail($request->integer('department_id'));

        $productId = $request->filled('product_id')
            ? $request->integer('product_id')
            : null;

        $changeTicketDepartmentAction->execute(
            ticket: $ticket,
            department: $department,
            productId: $productId,
        );

        return back()->with(
            'success',
            'دپارتمان و محصول مرتبط با تیکت با موفقیت تغییر کردند.'
        );
    }
}
