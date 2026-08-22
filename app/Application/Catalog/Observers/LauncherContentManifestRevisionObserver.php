<?php

namespace App\Application\Catalog\Observers;

use App\Application\Catalog\Actions\BumpLauncherManifestRevisionAction;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherModGroup;
use App\Domain\Launcher\Models\LauncherDownloadFileIntegrity;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use Illuminate\Database\Eloquent\Model;

final class LauncherContentManifestRevisionObserver
{
    public function __construct(
        private readonly BumpLauncherManifestRevisionAction $bumpRevisionAction,
    ) {
    }

    public function created(Model $model): void
    {
        $this->invalidateIntegrity($model);
        $this->bumpForModel($model);
    }

    public function updated(Model $model): void
    {
        $this->invalidateIntegrity($model);
        $this->bumpForModel($model, includeOriginalOwner: true);
    }

    public function deleted(Model $model): void
    {
        $this->invalidateIntegrity($model);
        $this->bumpForModel($model);
    }

    private function invalidateIntegrity(Model $model): void
    {
        $sourceType = match (true) {
            $model instanceof LauncherUpdateFile => 'update',
            $model instanceof LauncherModFile => 'mod',
            default => null,
        };

        if ($sourceType === null || !is_numeric($model->getKey())) {
            return;
        }

        LauncherDownloadFileIntegrity::query()
            ->where('source_type', $sourceType)
            ->where('source_file_id', (int) $model->getKey())
            ->delete();
    }

    private function bumpForModel(Model $model, bool $includeOriginalOwner = false): void
    {
        $saleItemIds = $this->saleItemIdsForModel($model, $includeOriginalOwner);

        foreach ($saleItemIds as $saleItemId) {
            $this->bumpRevisionAction->execute($saleItemId);
        }
    }

    /**
     * @return array<int, int>
     */
    private function saleItemIdsForModel(Model $model, bool $includeOriginalOwner): array
    {
        $ids = [];

        if ($model instanceof LauncherUpdate || $model instanceof LauncherMod || $model instanceof LauncherModGroup) {
            $this->pushId($ids, $model->product_sale_item_id);

            if ($includeOriginalOwner) {
                $this->pushId($ids, $model->getRawOriginal('product_sale_item_id'));
            }

            return array_values(array_unique($ids));
        }

        if ($model instanceof LauncherUpdateFile) {
            $this->pushId(
                $ids,
                $this->saleItemIdForUpdate($model->launcher_update_id)
            );

            if ($includeOriginalOwner) {
                $this->pushId(
                    $ids,
                    $this->saleItemIdForUpdate($model->getRawOriginal('launcher_update_id'))
                );
            }

            return array_values(array_unique($ids));
        }

        if ($model instanceof LauncherModFile) {
            $this->pushId(
                $ids,
                $this->saleItemIdForMod($model->launcher_mod_id)
            );

            if ($includeOriginalOwner) {
                $this->pushId(
                    $ids,
                    $this->saleItemIdForMod($model->getRawOriginal('launcher_mod_id'))
                );
            }
        }

        return array_values(array_unique($ids));
    }

    private function saleItemIdForUpdate(mixed $updateId): ?int
    {
        if (!is_numeric($updateId) || (int) $updateId <= 0) {
            return null;
        }

        $saleItemId = LauncherUpdate::query()
            ->whereKey((int) $updateId)
            ->value('product_sale_item_id');

        return is_numeric($saleItemId) ? (int) $saleItemId : null;
    }

    private function saleItemIdForMod(mixed $modId): ?int
    {
        if (!is_numeric($modId) || (int) $modId <= 0) {
            return null;
        }

        $saleItemId = LauncherMod::query()
            ->whereKey((int) $modId)
            ->value('product_sale_item_id');

        return is_numeric($saleItemId) ? (int) $saleItemId : null;
    }

    /**
     * @param array<int, int> $ids
     */
    private function pushId(array &$ids, mixed $value): void
    {
        if (is_numeric($value) && (int) $value > 0) {
            $ids[] = (int) $value;
        }
    }
}
