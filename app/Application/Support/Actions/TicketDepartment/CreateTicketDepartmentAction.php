<?php

namespace App\Application\Support\Actions\TicketDepartment;

use App\Domain\Support\Models\TicketDepartment;
use Throwable;

class CreateTicketDepartmentAction
{
    /**
     * @throws Throwable
     */
    public function execute(array $data): TicketDepartment
    {
        $title = trim($data['title']);

        return TicketDepartment::query()->create([
            'title' => $title,
            'requires_product' => (bool)($data['requires_product'] ?? false),
            'is_active' => (bool)($data['is_active'] ?? true),
            'sort_order' => (int)($data['sort_order'] ?? 0),
        ]);
    }
}
