<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProductManifestAssemblyStrategyEnum;
use App\Domain\Catalog\Enums\ProductManifestDownloadTypeEnum;
use App\Domain\Catalog\Enums\ProductManifestValidationModeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSaleItemManifest extends Model
{
    public const DEFAULT_CAPABILITIES = [
        'supports_install' => true,
        'supports_launch' => true,
        'supports_sider' => false,
        'supports_verify' => false,
        'supports_repair' => false,
        'supports_update' => false,
        'supports_settings' => false,
        'supports_profiles' => false,
        'supports_mod_manager' => false,
    ];

    public const DEFAULT_INSTALL_OPTIONS = [
        'extract_enabled' => true,
        'delete_archives_after_install' => true,
        'ask_before_delete_archives' => true,
        'clean_temp_after_install' => true,
        'create_desktop_shortcut' => false,
        'required_install_space_bytes' => null,
        'estimated_installed_size_bytes' => null,
    ];

    protected $fillable = [
        'product_sale_item_id',
        'schema_version',
        'download_type',
        'assembly_strategy',
        'archive_format',
        'assembled_file_name',
        'validation_mode',
        'install_directory',
        'install_folder_name',
        'requires_administrator',
        'has_option_file',
        'option_file_folder_name',
        'package_root_mode',
        'main_executable_path',
        'working_directory',
        'launch_arguments',
        'requires_sider',
        'sider_executable_path',
        'sider_working_directory',
        'sider_arguments',
        'run_sider_as_administrator',
        'capabilities',
        'install_options',
        'runtime_notices',
        'metadata',
    ];

    protected $casts = [
        'schema_version' => 'integer',
        'download_type' => ProductManifestDownloadTypeEnum::class,
        'assembly_strategy' => ProductManifestAssemblyStrategyEnum::class,
        'validation_mode' => ProductManifestValidationModeEnum::class,
        'requires_administrator' => 'boolean',
        'has_option_file' => 'boolean',
        'requires_sider' => 'boolean',
        'run_sider_as_administrator' => 'boolean',
        'capabilities' => 'array',
        'install_options' => 'array',
        'runtime_notices' => 'array',
        'metadata' => 'array',
    ];

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }

    /**
     * @return array<string, bool>
     */
    public function resolvedCapabilities(): array
    {
        return array_replace(self::DEFAULT_CAPABILITIES, $this->capabilities ?? []);
    }

    /**
     * @return array<string, bool|int|null>
     */
    public function resolvedInstallOptions(): array
    {
        return array_replace(self::DEFAULT_INSTALL_OPTIONS, $this->install_options ?? []);
    }
}
