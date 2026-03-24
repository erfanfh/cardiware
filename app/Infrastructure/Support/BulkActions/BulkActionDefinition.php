<?php

namespace App\Infrastructure\Support\BulkActions;

final class BulkActionDefinition
{
    public function __construct(
        public readonly string $value,
        public readonly string $label,
        public readonly bool   $destructive = false,
        public readonly bool   $requiresConfirmation = false,
    )
    {
    }

    /**
     * @return array{value: string, label: string, destructive: bool, requires_confirmation: bool}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'destructive' => $this->destructive,
            'requires_confirmation' => $this->requiresConfirmation,
        ];
    }
}
