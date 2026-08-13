<?php

namespace App\Application\Seo\Actions\Redirect;

use App\Application\Shared\Cache\Invalidators\SeoRedirectCacheInvalidator;
use App\Domain\Seo\Enums\RedirectStatusCodeEnum;
use App\Domain\Seo\Models\Redirect;

class CreateRedirectAction
{
    public function __construct(
        private readonly NormalizeRedirectPathAction $normalizeRedirectPathAction,
        private readonly SeoRedirectCacheInvalidator $cacheInvalidator,
    ) {
    }

    public function execute(array $data): Redirect
    {
        $statusCode = $data['status_code'] instanceof RedirectStatusCodeEnum
            ? $data['status_code']
            : RedirectStatusCodeEnum::from((int) $data['status_code']);

        $redirect = Redirect::query()->create([
            'source_path' => $this->normalizeRedirectPathAction->execute($data['source_path']),
            'target_url' => $statusCode->isGone()
                ? null
                : trim((string) $data['target_url']),
            'status_code' => $statusCode,
            'format' => 'plain',
            'is_active' => (bool) $data['is_active'],
        ]);

        $this->cacheInvalidator->invalidate();

        return $redirect->refresh();
    }
}
