<?php

namespace App\Infrastructure\Support\BulkActions;

final class DefaultBulkActions
{
    /**
     * @return array<string, BulkActionDefinition>
     */
    public static function all(): array
    {
        return [
            'activate' => new BulkActionDefinition(
                value: 'activate',
                label: 'فعال سازی',
            ),

            'deactivate' => new BulkActionDefinition(
                value: 'deactivate',
                label: 'غیرفعال سازی',
            ),

            'delete' => new BulkActionDefinition(
                value: 'delete',
                label: 'حذف',
                destructive: true,
                requiresConfirmation: true,
            ),
        ];
    }

    /**
     * @param array<string, BulkActionDefinition> $actions
     * @return array<string, BulkActionDefinition>
     */
    public static function merge(array $actions = []): array
    {
        return array_merge(self::all(), $actions);
    }

    /**
     * @param array<string, BulkActionDefinition> $actions
     * @return array<string, string>
     */
    public static function toOptions(array $actions = []): array
    {
        $definitions = self::merge($actions);

        $options = [
            '' => 'کارهای دسته‌جمعی',
        ];

        foreach ($definitions as $definition) {
            $options[$definition->value] = $definition->label;
        }

        return $options;
    }

    /**
     * @param array<string, BulkActionDefinition> $actions
     * @return list<string>
     */
    public static function values(array $actions = []): array
    {
        return array_values(
            array_map(
                static fn(BulkActionDefinition $definition) => $definition->value,
                self::merge($actions)
            )
        );
    }

    /**
     * @param array<string, BulkActionDefinition> $actions
     */
    public static function find(string $value, array $actions = []): ?BulkActionDefinition
    {
        $definitions = self::merge($actions);

        foreach ($definitions as $definition) {
            if ($definition->value === $value) {
                return $definition;
            }
        }

        return null;
    }
}
