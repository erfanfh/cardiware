<?php

namespace App\Application\Seo\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Facades\Cache;

class GetOrganizationLogoAction
{
    public function execute(): ?MediaFile
    {
        $logoSetting = get_setting('seo', 'organization_logo');

        if (!$logoSetting?->id) {
            return null;
        }

        return Cache::tags([
            CacheTag::SEO,
            CacheTag::SCHEMA,
            CacheTag::MEDIA,
        ])->remember(
            CacheKey::organizationLogo($logoSetting->id),
            CacheTtl::long(),
            fn() => MediaFile::query()
                ->with('primaryVariant')
                ->find($logoSetting->id)
        );
    }
}
