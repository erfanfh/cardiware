<?php

namespace App\Application\Support\Actions\TicketDepartment;

use App\Domain\Support\Models\TicketDepartment;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteSelectedTicketDepartmentsAction
{
    /**
     * @throws Throwable
     */
    public function execute(array $ids): int
    {
        $ids = collect($ids)
            ->map(fn($id) => (int)$id)
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        $usedDepartment = TicketDepartment::query()
            ->whereIn('id', $ids)
            ->whereHas('tickets')
            ->first();

        if ($usedDepartment) {
            throw ValidationException::withMessages([
                'department' => "دپارتمان «{$usedDepartment->title}» در تیکت‌ها استفاده شده و امکان حذف گروهی وجود ندارد.",
            ]);
        }

        return TicketDepartment::query()
            ->whereIn('id', $ids)
            ->delete();
    }
}
