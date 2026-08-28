<?php

namespace App\Application\Loyalty\Earning\Actions;

use App\Application\Loyalty\Earning\Support\EarningRuleDefinitionCatalog;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidateEarningRuleConfigAction
{
    public function __construct(
        private readonly EarningRuleDefinitionCatalog $definitions,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function execute(EarningRuleTypeEnum $type, array $config): array
    {
        $config = array_merge(
            $this->definitions->defaultConfig($type),
            $config,
        );

        return Validator::make(
            $config,
            $this->definitions->validationRules($type),
        )->validate();
    }
}
