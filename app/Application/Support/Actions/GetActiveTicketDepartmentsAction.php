<?php

namespace App\Application\Support\Actions;

use App\Domain\Support\Models\TicketDepartment;
use Illuminate\Database\Eloquent\Collection;

class GetActiveTicketDepartmentsAction
{
    public function execute(): Collection
    {
        return TicketDepartment::query()
            ->active()
            ->ordered()
            ->get();
    }
}
