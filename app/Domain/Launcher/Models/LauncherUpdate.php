<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemManifest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LauncherUpdate extends Model
{
    protected $fillable = [
        'product_sale_item_id', 'version', 'title', 'description', 'changelog',
        'is_enabled', 'is_staging', 'is_mandatory', 'has_option_file', 'archive_format', 'archive_password',
        'published_at', 'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_staging' => 'boolean',
        'is_mandatory' => 'boolean',
        'has_option_file' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
        'archive_password' => 'encrypted',
    ];

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(LauncherUpdateFile::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_enabled', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeVisibleToLauncherUser(Builder $query, ?int $userId, array $testerIds): Builder
    {
        $isTester = $userId !== null && $userId > 0
            && in_array($userId, array_map('intval', $testerIds), true);

        return $isTester ? $query : $query->where('is_staging', false);
    }

    /**
     * @return array{enabled: bool, folder_name: string, target: string}
     */
    public function resolvedOptionFile(): array
    {
        return [
            'enabled' => (bool) $this->has_option_file,
            'folder_name' => ProductSaleItemManifest::OPTION_FILE_FOLDER_NAME,
            'target' => ProductSaleItemManifest::OPTION_FILE_TARGET,
        ];
    }

    public function hasDownloadablePackage(): bool
    {
        return $this->files->isNotEmpty()
            && $this->files->every(
                static fn(LauncherUpdateFile $file): bool => $file->hasValidIntegrityMetadata()
            );
    }
}
