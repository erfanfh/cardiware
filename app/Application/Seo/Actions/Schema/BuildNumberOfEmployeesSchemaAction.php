<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;

final class BuildNumberOfEmployeesSchemaAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(SchemaContextDTO $context): array
    {
        $employees = explode('-', $context->organizationNumberOfEmployees);

        if (2 !== count($employees)) {
            return [];
        }

        return [
            '@type' => 'QuantitativeValue',
            'minValue' => trim($employees[0]),
            'maxValue' => trim($employees[1]),
        ];
    }
}
