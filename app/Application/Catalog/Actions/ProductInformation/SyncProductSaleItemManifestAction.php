<?php

namespace App\Application\Catalog\Actions\ProductInformation;

use App\Domain\Catalog\Enums\ProductManifestAssemblyStrategyEnum;
use App\Domain\Catalog\Enums\ProductManifestDownloadTypeEnum;
use App\Domain\Catalog\Enums\ProductManifestValidationModeEnum;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemManifest;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use Illuminate\Support\Arr;

class SyncProductSaleItemManifestAction
{
    /**
     * Sync launcher manifest settings.
     *
     * Invalid or missing values are normalized and never prevent
     * the product from being saved.
     *
     * @param array<string, mixed> $data
     */
    public function execute(
        ProductSaleItem $saleItem,
        array           $data,
    ): void
    {
        $saleItem
            ->unsetRelation('files')
            ->load('files');

        $downloadType = $this->resolveDownloadType(
            submittedValue: Arr::get($data, 'download_type'),
            saleItem: $saleItem,
        );

        $mainExecutablePath = $this->nullableString(
            Arr::get($data, 'main_executable_path')
        );

        $requiresSider = $this->toBoolean(
            Arr::get($data, 'requires_sider', false)
        );

        $siderExecutablePath = $this->nullableString(
            Arr::get($data, 'sider_executable_path')
        );

        $supportsSider = $this->toBoolean(
            Arr::get($data, 'capabilities.supports_sider', $requiresSider)
        ) || $requiresSider;

        $capabilities = $this->normalizeCapabilities(
            value: Arr::get($data, 'capabilities'),
            hasFiles: $saleItem->files->isNotEmpty(),
            mainExecutablePath: $mainExecutablePath,
            supportsSider: $supportsSider,
        );

        $saleItem->manifest()->updateOrCreate(
            [],
            [
                'schema_version' => max(
                    2,
                    min(
                        65535,
                        $this->integer(
                            Arr::get($data, 'schema_version'),
                            1
                        )
                    )
                ),

                'download_type' => $downloadType->value,

                'assembly_strategy' =>
                    $downloadType === ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE
                        ? ProductManifestAssemblyStrategyEnum::ORDERED->value
                        : ProductManifestAssemblyStrategyEnum::INDEPENDENT->value,

                'archive_format' => $saleItem->files->isNotEmpty()
                    ? '7z'
                    : null,

                'assembled_file_name' =>
                    $this->resolveAssembledFileName(
                        data: $data,
                        saleItem: $saleItem,
                        downloadType: $downloadType,
                    ),

                'validation_mode' => $saleItem->files->isNotEmpty()
                && $saleItem->files->every(
                    fn(ProductSaleItemFile $file): bool => preg_match('/^[a-fA-F0-9]{64}$/', (string)$file->sha256) === 1
                )
                    ? ProductManifestValidationModeEnum::PER_FILE->value
                    : ProductManifestValidationModeEnum::NONE->value,

                'install_directory' => $this->nullableString(
                    Arr::get($data, 'install_directory')
                ),
                'install_folder_name' => $this->nullableString(
                    Arr::get($data, 'install_folder_name')
                ),

                'requires_administrator' => $this->toBoolean(
                    Arr::get(
                        $data,
                        'requires_administrator',
                        false
                    )
                ),
                'has_option_file' => $this->toBoolean(
                    Arr::get($data, 'has_option_file', false)
                ),
                'option_file_folder_name' => 'OPTION FILE',
                'package_root_mode' => 'merge_to_product_root',

                'main_executable_path' =>
                    $mainExecutablePath,

                'working_directory' => $this->nullableString(
                    Arr::get($data, 'working_directory')
                ),

                'launch_arguments' => $this->nullableString(
                    Arr::get($data, 'launch_arguments')
                ),

                'requires_sider' => $requiresSider,

                'sider_executable_path' =>
                    $siderExecutablePath,

                'sider_working_directory' =>
                    $this->nullableString(
                        Arr::get(
                            $data,
                            'sider_working_directory'
                        )
                    ),

                'sider_arguments' =>
                    $this->nullableString(
                        Arr::get($data, 'sider_arguments')
                    ),

                'run_sider_as_administrator' =>
                    $this->toBoolean(
                        Arr::get(
                            $data,
                            'run_sider_as_administrator',
                            false
                        )
                    ),

                'cpu_compatibility' =>
                    $this->normalizeCpuCompatibility(
                        Arr::get($data, 'cpu_compatibility')
                    ),

                'capabilities' => $capabilities,

                'install_options' =>
                    $this->normalizeInstallOptions(
                        Arr::get($data, 'install_options')
                    ),

                'runtime_notices' =>
                    $this->normalizeRuntimeNotices(
                        Arr::get($data, 'runtime_notices')
                    ),

                'metadata' => is_array(
                    Arr::get($data, 'metadata')
                )
                    ? Arr::get($data, 'metadata')
                    : [],
            ]
        );
    }

    private function resolveDownloadType(
        mixed           $submittedValue,
        ProductSaleItem $saleItem,
    ): ProductManifestDownloadTypeEnum
    {
        $submittedType =
            ProductManifestDownloadTypeEnum::tryFrom(
                (string)$submittedValue
            );

        if ($submittedType !== null) {
            return $submittedType;
        }

        if ($saleItem->files->count() <= 1) {
            return ProductManifestDownloadTypeEnum::SINGLE_FILE;
        }

        $fileNames = $saleItem->files
            ->map(
                fn(ProductSaleItemFile $file): ?string => $this->nullableString($file->file_name)
                    ?: $this->resolveFileNameFromUrl($file->url)
            )
            ->filter();

        $hasSplitArchiveParts = $fileNames->contains(
            static fn(string $fileName): bool => preg_match(
                    '/\.\d{3,}$/',
                    $fileName
                ) === 1
        );

        return $hasSplitArchiveParts
            ? ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE
            : ProductManifestDownloadTypeEnum::MULTIPLE_FILES;
    }

    private function resolveArchiveFormat(
        array                           $data,
        ProductSaleItem                 $saleItem,
        ProductManifestDownloadTypeEnum $downloadType,
    ): ?string
    {
        if (
            $downloadType
            !== ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE
        ) {
            return null;
        }

        $submittedFormat = strtolower(
            trim(
                (string)Arr::get(
                    $data,
                    'archive_format',
                    ''
                )
            )
        );

        if (
            in_array(
                $submittedFormat,
                ['7z'],
                true
            )
        ) {
            return $submittedFormat;
        }

        $assembledFileName =
            $this->resolveAssembledFileName(
                data: $data,
                saleItem: $saleItem,
                downloadType: $downloadType,
            );

        if ($assembledFileName === null) {
            return null;
        }

        $extension = strtolower(
            pathinfo(
                $assembledFileName,
                PATHINFO_EXTENSION
            )
        );

        return in_array(
            $extension,
            ['7z'],
            true
        )
            ? $extension
            : null;
    }

    private function resolveAssembledFileName(
        array                           $data,
        ProductSaleItem                 $saleItem,
        ProductManifestDownloadTypeEnum $downloadType,
    ): ?string
    {
        if (
            $downloadType
            !== ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE
        ) {
            return null;
        }

        $submittedFileName = $this->nullableString(
            Arr::get($data, 'assembled_file_name')
        );

        if ($submittedFileName !== null) {
            return basename($submittedFileName);
        }

        $firstFile = $saleItem->files
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();

        if (!$firstFile instanceof ProductSaleItemFile) {
            return null;
        }

        $fileName = $this->nullableString($firstFile->file_name)
            ?: $this->resolveFileNameFromUrl($firstFile->url);

        if ($fileName === null) {
            return null;
        }

        return preg_replace(
            '/\.\d{3,}$/',
            '',
            $fileName
        ) ?: null;
    }

    /**
     * @return array<string, bool>
     */
    private function normalizeCapabilities(
        mixed   $value,
        bool    $hasFiles,
        ?string $mainExecutablePath,
        bool    $supportsSider,
    ): array
    {
        $value = is_array($value) ? $value : [];

        $supportsInstall = $hasFiles
            && $this->toBoolean(
                $value['supports_install'] ?? true
            );

        $supportsLaunch = $mainExecutablePath !== null
            && $this->toBoolean(
                $value['supports_launch'] ?? true
            );

        return [
            'supports_install' => $supportsInstall,
            'supports_launch' => $supportsLaunch,
            'supports_sider' => $supportsSider,

            'supports_verify' => $hasFiles,
            'supports_repair' => false,
            'supports_update' => $this->toBoolean(
                $value['supports_update'] ?? false
            ),
            'supports_mod_manager' => $this->toBoolean(
                $value['supports_mod_manager'] ?? false
            ),

            'supports_settings' => $this->toBoolean(
                $value['supports_settings'] ?? false
            ),

            'supports_profiles' => $this->toBoolean(
                $value['supports_profiles'] ?? false
            ),
        ];
    }

    /**
     * @return array{enabled: bool, default_mode: string, apply_to_game: bool, apply_to_sider: bool, allow_user_override: bool}
     */
    private function normalizeCpuCompatibility(mixed $value): array
    {
        $value = is_array($value) ? $value : [];

        $defaultMode = strtolower(trim((string)($value['default_mode'] ?? 'off')));

        if (!in_array(
            $defaultMode,
            ProductSaleItemManifest::CPU_COMPATIBILITY_MODES,
            true
        )) {
            $defaultMode = ProductSaleItemManifest::DEFAULT_CPU_COMPATIBILITY['default_mode'];
        }

        return [
            'enabled' => $this->toBoolean($value['enabled'] ?? false),
            'default_mode' => $defaultMode,
            'apply_to_game' => $this->toBoolean($value['apply_to_game'] ?? false),
            'apply_to_sider' => $this->toBoolean($value['apply_to_sider'] ?? false),
            'allow_user_override' => $this->toBoolean($value['allow_user_override'] ?? false),
        ];
    }

    /**
     * @return array<string, bool|int|null>
     */
    private function normalizeInstallOptions(
        mixed $value,
    ): array
    {
        $value = is_array($value) ? $value : [];

        return [
            'extract_enabled' => $this->toBoolean(
                $value['extract_enabled'] ?? true
            ),

            'delete_archives_after_install' =>
                $this->toBoolean(
                    $value['delete_archives_after_install'] ?? true
                ),

            'ask_before_delete_archives' =>
                $this->toBoolean(
                    $value['ask_before_delete_archives'] ?? true
                ),

            'clean_temp_after_install' =>
                $this->toBoolean(
                    $value['clean_temp_after_install'] ?? true
                ),

            'create_desktop_shortcut' =>
                $this->toBoolean(
                    $value['create_desktop_shortcut'] ?? false
                ),

            'required_install_space_bytes' =>
                $this->nullablePositiveInteger(
                    $value['required_install_space_bytes'] ?? null
                ),

            'estimated_installed_size_bytes' =>
                $this->nullablePositiveInteger(
                    $value['estimated_installed_size_bytes'] ?? null
                ),
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function normalizeRuntimeNotices(
        mixed $value,
    ): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(
                static fn(mixed $notice): bool => is_array($notice)
            )
            ->map(function (array $notice): ?array {
                $message = $this->nullableString(
                    $notice['message'] ?? null
                );

                if ($message === null) {
                    return null;
                }

                $type = in_array(
                    $notice['type'] ?? null,
                    ['info', 'warning', 'error'],
                    true
                )
                    ? $notice['type']
                    : 'info';

                return [
                    'type' => $type,
                    'title' => $this->nullableString(
                        $notice['title'] ?? null
                    ),
                    'message' => $message,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveFileNameFromUrl(
        string $url,
    ): ?string
    {
        $path = parse_url(
            $url,
            PHP_URL_PATH
        );

        if (!is_string($path) || $path === '') {
            return null;
        }

        $fileName = basename(
            rawurldecode($path)
        );

        return $fileName !== ''
            ? $fileName
            : null;
    }

    private function nullableString(
        mixed $value,
    ): ?string
    {
        $value = trim((string)$value);

        return $value !== ''
            ? $value
            : null;
    }

    private function integer(
        mixed $value,
        int   $default = 0,
    ): int
    {
        return is_numeric($value)
            ? (int)$value
            : $default;
    }

    private function nullablePositiveInteger(
        mixed $value,
    ): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        return max(0, (int)$value);
    }

    private function toBoolean(
        mixed $value,
    ): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
