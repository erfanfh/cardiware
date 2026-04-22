<?php

namespace App\Application\Seo\Actions;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class SyncSeoMetaAction
{
    /**
     * @param Model $seoable
     * @param array<string, mixed> $data
     */
    public function execute(Model $seoable, array $data): void
    {
        if (!method_exists($seoable, 'seoMeta')) {
            throw new RuntimeException('The given model does not support seoMeta relation.');
        }

        $seoable->seoMeta()->updateOrCreate([], $this->buildPayload($data));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function buildPayload(array $data): array
    {
        return [
            'focus_keyword' => $data['focus_keyword'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'robots' => $data['robots'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,

            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'og_image_id' => data_get($data, 'og_image.id'),

            'x_title' => $data['x_title'] ?? null,
            'x_description' => $data['x_description'] ?? null,
            'x_image_id' => data_get($data, 'x_image.id'),

            'schema_type' => $data['schema_type'] ?? null,
            'schema_settings' => $data['schema_settings'] ?? null,
        ];
    }
}
