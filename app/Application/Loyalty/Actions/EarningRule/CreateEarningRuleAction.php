<?php

namespace App\Application\Loyalty\Actions\EarningRule;

use App\Application\Loyalty\Earning\Actions\ValidateEarningRuleConfigAction;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateEarningRuleAction
{
    public function __construct(
        private readonly ValidateEarningRuleConfigAction $validateConfig,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @throws Throwable
     */
    public function execute(array $data): EarningRule
    {
        return DB::transaction(function () use ($data): EarningRule {
            $type = $this->type($data['type']);
            $config = $this->validateConfig->execute($type, $data['config'] ?? []);

            return EarningRule::query()->create([
                'code' => $data['code'],
                'title' => $data['title'],
                'type' => $type,
                'is_active' => (bool)$data['is_active'],
                'priority' => (int)$data['priority'],
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'config' => $config,
            ])->refresh();
        });
    }

    private function type(EarningRuleTypeEnum|string $type): EarningRuleTypeEnum
    {
        return $type instanceof EarningRuleTypeEnum
            ? $type
            : EarningRuleTypeEnum::from($type);
    }
}
