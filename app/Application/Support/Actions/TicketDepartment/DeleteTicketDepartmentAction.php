<?php

namespace App\Application\Support\Actions\TicketDepartment;

use App\Domain\Support\Models\TicketDepartment;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteTicketDepartmentAction
{
    /**
     * @throws Throwable
     */
    public function execute(TicketDepartment $department): void
    {
        if ($department->tickets()->exists()) {
            throw ValidationException::withMessages([
                'department' => 'این دپارتمان در تیکت‌ها استفاده شده و امکان حذف آن وجود ندارد.',
            ]);
        }

        $department->delete();
    }
}
