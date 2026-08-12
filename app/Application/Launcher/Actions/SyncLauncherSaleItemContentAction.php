<?php

namespace App\Application\Launcher\Actions;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherModGroup;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use Illuminate\Database\Eloquent\Collection;

class SyncLauncherSaleItemContentAction
{
    public function execute(ProductSaleItem $saleItem, array $payload): void
    {
        if (array_key_exists('launcher_updates', $payload)) {
            $this->updates($saleItem, (array)$payload['launcher_updates']);
        }
        if (array_key_exists('launcher_mod_groups', $payload)) {
            $this->groups($saleItem, (array)$payload['launcher_mod_groups']);
        }
        if (array_key_exists('launcher_mods', $payload)) {
            $this->mods($saleItem, (array)$payload['launcher_mods']);
        }
    }

    private function updates(ProductSaleItem $saleItem, array $items): void
    {
        $existing = $saleItem->launcherUpdates()->with('files')->get();
        $kept = [];
        foreach (array_values($items) as $index => $data) {
            if (!is_array($data) || blank($data['version'] ?? null) || blank($data['title'] ?? null)) continue;
            $update = $this->resolve($existing, $data, LauncherUpdate::class);
            $update->fill([
                'product_sale_item_id' => $saleItem->id,
                'version' => trim((string)$data['version']),
                'title' => trim((string)$data['title']),
                'description' => $data['description'] ?? null,
                'changelog' => $data['changelog'] ?? null,
                'is_enabled' => (bool)($data['is_enabled'] ?? false),
                'is_mandatory' => (bool)($data['is_mandatory'] ?? false),
                'has_option_file' => (bool)($data['has_option_file'] ?? false),
                'archive_format' => '7z',
                'published_at' => filled($data['published_at'] ?? null)
                    ? $data['published_at']
                    : ((bool)($data['is_enabled'] ?? false) ? now() : null),
                'sort_order' => (int)($data['sort_order'] ?? $index),
            ])->save();
            $this->files($update->files()->get(), (array)($data['files'] ?? []),
                LauncherUpdateFile::class, 'launcher_update_id', $update->id);
            $kept[] = $update->id;
        }
        $saleItem->launcherUpdates()
            ->when($kept !== [], fn($q) => $q->whereNotIn('id', $kept))
            ->get()
            ->each(static fn(LauncherUpdate $update) => $update->delete());
    }

    private function groups(ProductSaleItem $saleItem, array $items): void
    {
        $existing = $saleItem->launcherModGroups()->get();
        $kept = [];
        foreach (array_values($items) as $index => $data) {
            if (!is_array($data) || blank($data['key'] ?? null) || blank($data['title'] ?? null)) continue;
            $group = $this->resolve($existing, $data, LauncherModGroup::class);
            $group->fill([
                'product_sale_item_id' => $saleItem->id,
                'key' => trim((string)$data['key']),
                'title' => trim((string)$data['title']),
                'is_exclusive' => (bool)($data['is_exclusive'] ?? false),
                'sort_order' => (int)($data['sort_order'] ?? $index),
            ])->save();
            $kept[] = $group->id;
        }
        $saleItem->launcherModGroups()
            ->when($kept !== [], fn($q) => $q->whereNotIn('id', $kept))
            ->get()
            ->each(static fn(LauncherModGroup $group) => $group->delete());
    }

    private function mods(ProductSaleItem $saleItem, array $items): void
    {
        $existing = $saleItem->launcherMods()->with('files')->get();
        $groups = $saleItem->launcherModGroups()->get()->keyBy('key');
        $kept = [];
        foreach (array_values($items) as $index => $data) {
            if (!is_array($data) || blank($data['name'] ?? null)
                || blank($data['slug'] ?? null) || blank($data['version'] ?? null)) continue;
            $mod = $this->resolve($existing, $data, LauncherMod::class);
            $mod->fill([
                'product_sale_item_id' => $saleItem->id,
                'launcher_mod_group_id' => $groups->get($data['group_key'] ?? '')?->id,
                'name' => trim((string)$data['name']),
                'slug' => trim((string)$data['slug']),
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'version' => trim((string)$data['version']),
                'image_media_id' => filled($data['image_media_id'] ?? null) ? (int)$data['image_media_id'] : null,
                'size_bytes' => filled($data['size_bytes'] ?? null) ? (int)$data['size_bytes'] : null,
                'priority' => (int)($data['priority'] ?? $index),
                'is_enabled' => (bool)($data['is_enabled'] ?? false),
                'is_required' => (bool)($data['is_required'] ?? false),
                'compatible_base_versions' => array_values((array)($data['compatible_base_versions'] ?? [])),
                'compatible_update_versions' => array_values((array)($data['compatible_update_versions'] ?? [])),
                'published_at' => filled($data['published_at'] ?? null)
                    ? $data['published_at']
                    : ((bool)($data['is_enabled'] ?? false) ? now() : null),
            ])->save();
            $this->files($mod->files()->get(), (array)($data['files'] ?? []),
                LauncherModFile::class, 'launcher_mod_id', $mod->id);
            $kept[] = $mod->id;
        }
        $saleItem->launcherMods()
            ->when($kept !== [], fn($q) => $q->whereNotIn('id', $kept))
            ->get()
            ->each(static fn(LauncherMod $mod) => $mod->delete());
    }

    private function files(
        Collection $existing, array $items, string $model, string $foreignKey, int $foreignId
    ): void
    {
        $kept = [];
        foreach (array_values($items) as $index => $data) {
            if (!is_array($data) || blank($data['url'] ?? null) || blank($data['file_name'] ?? null)) continue;
            $file = $this->resolve($existing, $data, $model);
            $file->fill([
                $foreignKey => $foreignId,
                'title' => $data['title'] ?? null,
                'file_name' => trim((string)$data['file_name']),
                'url' => trim((string)$data['url']),
                'size_bytes' => (int)($data['size_bytes'] ?? 0),
                'sha256' => strtolower(trim((string)($data['sha256'] ?? ''))),
                'mime_type' => $data['mime_type'] ?? 'application/octet-stream',
                'part_number' => filled($data['part_number'] ?? null) ? (int)$data['part_number'] : null,
                'is_required' => (bool)($data['is_required'] ?? true),
                'sort_order' => (int)($data['sort_order'] ?? $index),
            ])->save();
            $kept[] = $file->id;
        }
        $model::query()->where($foreignKey, $foreignId)
            ->when($kept !== [], fn($q) => $q->whereNotIn('id', $kept))
            ->get()
            ->each(static fn($file) => $file->delete());
    }

    private function resolve(Collection $existing, array $data, string $model): mixed
    {
        $id = filled($data['id'] ?? null) ? (int)$data['id'] : null;
        $item = $id ? $existing->firstWhere('id', $id) : null;
        return $item instanceof $model ? $item : new $model();
    }
}
