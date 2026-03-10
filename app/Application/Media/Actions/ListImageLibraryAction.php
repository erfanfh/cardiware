<?php

namespace App\Application\Media\Actions;

use App\Domain\Media\Enums\MediaFileTypeEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListImageLibraryAction
{
    /**
     * Retrieves a paginated list of image media files.
     *
     * @param string|null $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function execute(?string $search = null, int $perPage = 24): LengthAwarePaginator
    {
        $q = MediaFile::query()
            ->with('primaryVariant')
            ->where('type', MediaFileTypeEnum::IMAGE)
            ->whereNotNull('primary_variant_id')
            ->latest('id');

        // فعلاً سرچ ساده: با id یا checksum یا path
        if (!empty($search)) {
            $search = trim($search);

            $q->where(function ($sub) use ($search) {
                if (ctype_digit($search)) {
                    $sub->orWhere('id', (int)$search);
                }

                $sub->orWhereHas('primaryVariant', function ($v) use ($search) {
                    $v->where('path', 'like', "%{$search}%")
                        ->orWhere('checksum', 'like', "%{$search}%");
                });
            });
        }

        return $q->paginate($perPage);
    }
}
