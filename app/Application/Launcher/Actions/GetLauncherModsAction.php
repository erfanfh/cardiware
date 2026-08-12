<?php

namespace App\Application\Launcher\Actions;

use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;

class GetLauncherModsAction
{
    public function __construct(
        private readonly RemoteDownloadFileMetadataResolver $metadataResolver,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(PurchaseAccess $access): array
    {
        $baseVersion = (string) $access->productSaleItem()->value('version');

        return LauncherMod::query()
            ->where('product_sale_item_id', $access->product_sale_item_id)
            ->published()
            ->with([
                'group',
                'image.primaryVariant',
                'files',
            ])
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->map(fn (LauncherMod $mod): array => $this->modPayload($mod, $baseVersion))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function modPayload(LauncherMod $mod, string $baseVersion): array
    {
        $baseVersions = array_values((array) ($mod->compatible_base_versions ?? []));
        $updateVersions = array_values((array) ($mod->compatible_update_versions ?? []));
        $isBaseCompatible = $baseVersions === [] || in_array($baseVersion, $baseVersions, true);

        $files = $mod->files
            ->values()
            ->map(fn (LauncherModFile $file): array => $this->filePayload($file));

        $totalSizeBytes = (int) $files->sum('size_bytes');

        if ((int) $mod->size_bytes !== $totalSizeBytes) {
            $mod->forceFill(['size_bytes' => $totalSizeBytes])->saveQuietly();
        }

        $variant = $mod->image?->primaryVariant;
        $imageUrl = $mod->image?->url;

        return [
            'id' => $mod->id,
            'name' => $mod->name,
            'slug' => $mod->slug,
            'short_description' => $mod->short_description,
            'description' => $mod->description,
            'version' => $mod->version,
            'image_url' => $imageUrl,
            'image' => $imageUrl ? [
                'url' => $imageUrl,
                'width' => $variant?->width !== null ? (int) $variant->width : null,
                'height' => $variant?->height !== null ? (int) $variant->height : null,
            ] : null,
            'size_bytes' => $totalSizeBytes,
            'priority' => (int) $mod->priority,
            'is_required' => (bool) $mod->is_required,
            'group' => $mod->group ? [
                'key' => $mod->group->key,
                'title' => $mod->group->title,
                'is_exclusive' => (bool) $mod->group->is_exclusive,
            ] : null,
            'compatibility' => [
                'base_version' => $baseVersion !== '' ? $baseVersion : null,
                'base_versions' => $baseVersions,
                'update_versions' => $updateVersions,
                'is_compatible' => $isBaseCompatible,
            ],
            'package' => [
                'archive_format' => '7z',
                'merge_mode' => 'merge_to_product_root',
                'total_size_bytes' => $totalSizeBytes,
                'files' => $files->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filePayload(LauncherModFile $file): array
    {
        $metadata = $this->metadataResolver->resolve(
            sourceType: 'mod',
            file: $file,
            url: (string) $file->url,
        );

        return [
            'file_id' => $file->id,
            'file_name' => basename(str_replace('\\', '/', (string) $file->file_name)),
            'title' => $file->title,
            'size_bytes' => $metadata['size_bytes'],
            'sha256' => $metadata['sha256'],
            'mime_type' => $metadata['mime_type'] ?: $file->mime_type,
            'part_number' => $file->part_number !== null ? (int) $file->part_number : null,
            'is_required' => (bool) $file->is_required,
            'order' => (int) $file->sort_order,
        ];
    }
}
