<?php

namespace App\Application\Catalog\Actions;

use App\Application\Catalog\Exceptions\ProductManifestUnavailableException;
use App\Domain\Catalog\Enums\ProductManifestDownloadTypeEnum;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BuildPurchaseAccessManifestAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(
        PurchaseAccess $purchaseAccess,
    ): array
    {
        $purchaseAccess->loadMissing([
            'product:id,title,slug',
            'productSaleItem:id,product_id,sku,title,edition_key,version,manifest_revision,launcher_title,launcher_excerpt',
            'productSaleItem.manifest',
            'productSaleItem.files' => function (
                HasMany $relation,
            ): void {
                $relation
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        $product = $purchaseAccess->product;
        $saleItem = $purchaseAccess->productSaleItem;

        if ($product === null || $saleItem === null) {
            throw new ProductManifestUnavailableException(
                'The purchase access does not have a valid product or sale item.'
            );
        }

        $manifest = $saleItem->manifest;

        if ($manifest === null) {
            throw new ProductManifestUnavailableException(
                'No manifest settings are configured for the sale item.'
            );
        }

        if (blank($saleItem->version)) {
            throw new ProductManifestUnavailableException(
                'The sale item version has not been configured.'
            );
        }

        $capabilities = $manifest->resolvedCapabilities();
        $installOptions = $manifest->resolvedInstallOptions();
        $cpuCompatibility = $manifest->resolvedCpuCompatibility();

        $files = $this->sortFiles(
            $saleItem->files,
            $manifest->download_type
        );

        $this->validateManifest(
            files: $files,
            manifest: $manifest,
            capabilities: $capabilities,
        );

        $totalSizeBytes = $files->every(
            static fn(ProductSaleItemFile $file): bool => $file->size_bytes !== null
        )
            ? (int)$files->sum('size_bytes')
            : null;

        return [
            'schema_version' => $manifest->schema_version,
            'manifest_revision' => $saleItem->manifest_revision,
            'manifest_id' => sprintf(
                '%s+%d',
                $saleItem->version,
                $saleItem->manifest_revision,
            ),
            'product' => [
                'id' => $product->id,
                'slug' => $product->slug,
                'title' => $product->title,
            ],
            'access' => [
                'id' => $purchaseAccess->id,
                'is_usable' => true,
                'starts_at' => $purchaseAccess->starts_at?->toISOString(),
                'expires_at' => $purchaseAccess->expires_at?->toISOString(),
            ],
            'sale_item' => [
                'id' => $saleItem->id,
                'sku' => $saleItem->sku,
                'title' => $saleItem->launcher_title ?: $saleItem->title,
                'excerpt' => $saleItem->launcher_excerpt,
                'edition_key' => $saleItem->edition_key,
                'version' => $saleItem->version,
            ],
            'capabilities' => $capabilities,
            'download' => [
                'type' => $manifest->download_type->value,
                'assembly_strategy' => $manifest->assembly_strategy->value,
                'archive_format' => $manifest->archive_format,
                'assembled_file_name' => $manifest->assembled_file_name,
                'validation_mode' => $manifest->validation_mode->value,
                'total_size_bytes' => $totalSizeBytes,

                /*
                 * No direct or temporary URLs are returned here.
                 * URLs are resolved using the download preparation API.
                 */
                'files' => $files
                    ->map(
                        fn(ProductSaleItemFile $file): array => [
                            'file_id' => $file->id,

                            'title' => $file->title,

                            'file_name' => $file->file_name
                                ?: ($file->title ?? "file-{$file->id}"),
                            'size_bytes' => $file->size_bytes,
                            'sha256' => strtolower((string)$file->sha256),
                            'mime_type' => $file->mime_type,
                            'part_number' => $file->part_number,
                            'archive_group' => $file->archive_group,
                            'is_required' => $file->is_required,

                            /*
                             * sort_order is the actual download and
                             * split archive assembly order.
                             */
                            'order' => $file->sort_order,
                        ]
                    )
                    ->values()
                    ->all(),
            ],

            'installation' => [
                'install_folder_name' => $manifest->install_folder_name
                    ?: $manifest->install_directory,
                'requires_administrator' => $manifest->requires_administrator,
                'merge_mode' => $manifest->package_root_mode,
                'option_file' => [
                    'enabled' => $manifest->has_option_file,
                    'folder_name' => $manifest->option_file_folder_name ?: 'OPTION FILE',
                    'target' => 'windows_documents',
                ],
                ...$installOptions,
            ],

            'launch' => [
                'main_executable_path' => $manifest->main_executable_path,
                'working_directory' => $manifest->working_directory,
                'arguments' => $manifest->launch_arguments,
                'requires_sider' => $manifest->requires_sider,
                'sider_executable_path' => $manifest->sider_executable_path,
                'sider_working_directory' => $manifest->sider_working_directory,
                'sider_arguments' => $manifest->sider_arguments,
                'run_sider_as_administrator' => $manifest->run_sider_as_administrator,
                'cpu_compatibility' => $cpuCompatibility,
            ],

            'runtime_notices' => $manifest->runtime_notices ?? [],

            'metadata' => $manifest->metadata ?: new \stdClass(),
        ];
    }

    /**
     * @param Collection<int, ProductSaleItemFile> $files
     *
     * @return Collection<int, ProductSaleItemFile>
     */
    private function sortFiles(
        Collection                      $files,
        ProductManifestDownloadTypeEnum $downloadType,
    ): Collection
    {
        if ($downloadType !== ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE) {
            return $files
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();
        }

        return $files
            ->sort(
                static fn(
                    ProductSaleItemFile $left,
                    ProductSaleItemFile $right,
                ): int => [
                        $left->part_number ?? PHP_INT_MAX,
                        $left->sort_order,
                        $left->id,
                    ] <=> [
                        $right->part_number ?? PHP_INT_MAX,
                        $right->sort_order,
                        $right->id,
                    ]
            )
            ->values();
    }

    /**
     * @param Collection<int, ProductSaleItemFile> $files
     * @param array<string, bool> $capabilities
     */
    private function validateManifest(
        Collection $files,
        mixed      $manifest,
        array      $capabilities,
    ): void
    {
        if ($capabilities['supports_install'] && $files->isEmpty()) {
            throw new ProductManifestUnavailableException(
                'Install is enabled, but the sale item has no files.'
            );
        }

        if ($capabilities['supports_install']
            && blank($manifest->install_folder_name)
            && blank($manifest->install_directory)) {
            throw new ProductManifestUnavailableException(
                'Install is enabled, but the install folder name is missing.'
            );
        }

        if ($capabilities['supports_launch'] && blank($manifest->main_executable_path)) {
            throw new ProductManifestUnavailableException(
                'Launch is enabled, but the main executable path is missing.'
            );
        }

        if ($capabilities['supports_sider']) {
            if (blank($manifest->sider_executable_path)) {
                throw new ProductManifestUnavailableException(
                    'Sider is supported, but its executable path is missing.'
                );
            }

            if (blank($manifest->sider_working_directory)) {
                throw new ProductManifestUnavailableException(
                    'Sider is supported, but its working directory is missing.'
                );
            }
        }

        foreach ($files as $file) {
            if (blank($file->file_name)) {
                throw new ProductManifestUnavailableException(
                    "File {$file->id} does not have a file name."
                );
            }

            if (
                $capabilities['supports_verify']
                && !preg_match(
                    '/^[a-fA-F0-9]{64}$/',
                    (string)$file->sha256
                )
            ) {
                throw new ProductManifestUnavailableException(
                    "File {$file->id} does not have a valid SHA-256 hash."
                );
            }
        }

        if ($manifest->download_type !== ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE) {
            return;
        }

        if (blank($manifest->archive_format)) {
            throw new ProductManifestUnavailableException(
                'Split archive format has not been configured.'
            );
        }

        if (strtolower((string)$manifest->archive_format) !== '7z') {
            throw new ProductManifestUnavailableException(
                'MW Launcher only accepts 7z packages.'
            );
        }

        if (blank($manifest->assembled_file_name)) {
            throw new ProductManifestUnavailableException(
                'The assembled archive file name is missing.'
            );
        }

        $partNumbers = $files
            ->pluck('part_number')
            ->filter()
            ->values();

        if ($partNumbers->count() !== $files->count()) {
            throw new ProductManifestUnavailableException(
                'Every split archive file must have a part number.'
            );
        }

        if ($partNumbers->unique()->count() !== $files->count()) {
            throw new ProductManifestUnavailableException(
                'Split archive part numbers must be unique.'
            );
        }
    }
}
