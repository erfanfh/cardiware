<?php

namespace App\Infrastructure\Support\BulkActions;

final class BulkActionSet
{
    /**
     * @param array<string, BulkActionDefinition> $definitions
     */
    public function __construct(
        protected array $definitions = [],
    )
    {
    }

    /**
     * @param array<string, BulkActionDefinition> $definitions
     */
    public static function make(array $definitions = []): self
    {
        return new self($definitions);
    }

    /**
     * @return array<string, BulkActionDefinition>
     */
    public function all(): array
    {
        return $this->definitions;
    }

    /**
     * @param array<string, BulkActionDefinition> $definitions
     */
    public function merge(array $definitions): self
    {
        return new self(array_merge($this->definitions, $definitions));
    }

    /**
     * @param list<string> $keys
     */
    public function only(array $keys): self
    {
        return new self(
            array_intersect_key($this->definitions, array_flip($keys))
        );
    }

    /**
     * @param list<string> $keys
     */
    public function except(array $keys): self
    {
        return new self(
            array_diff_key($this->definitions, array_flip($keys))
        );
    }

    public function add(BulkActionDefinition $definition, ?string $key = null): self
    {
        $key ??= $definition->value;

        $definitions = $this->definitions;
        $definitions[$key] = $definition;

        return new self($definitions);
    }

    public function find(string $value): ?BulkActionDefinition
    {
        foreach ($this->definitions as $definition) {
            if ($definition->value === $value) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function values(): array
    {
        return array_values(
            array_map(
                static fn(BulkActionDefinition $definition) => $definition->value,
                $this->definitions
            )
        );
    }

    /**
     * @return array<string, string>
     */
    public function toOptions(string $placeholder = 'کارهای دسته‌جمعی'): array
    {
        $options = [
            '' => $placeholder,
        ];

        foreach ($this->definitions as $definition) {
            $options[$definition->value] = $definition->label;
        }

        return $options;
    }

    /**
     * @return list<array{
     *     value: string,
     *     label: string,
     *     destructive: bool,
     *     requires_confirmation: bool
     * }>
     */
    public function toArray(): array
    {
        return array_values(
            array_map(
                static fn(BulkActionDefinition $definition) => $definition->toArray(),
                $this->definitions
            )
        );
    }

    public function isEmpty(): bool
    {
        return empty($this->definitions);
    }
}
