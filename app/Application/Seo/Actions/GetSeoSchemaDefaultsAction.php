<?php

namespace App\Application\Seo\Actions;

class GetSeoSchemaDefaultsAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $homeUrl = rtrim(url('/'), '/') . '/';

        $siteName = get_setting('seo', 'site_name')
            ?: get_setting('general', 'title')
                ?: config('app.name');

        return [
            'home_url' => $homeUrl,
            'site_name' => $siteName,
            'organization_name' => get_setting('seo', 'organization_name') ?: $siteName,
            'organization_alternate_name' => get_setting('seo', 'organization_alternate_name') ?: null,
            'organization_url' => get_setting('seo', 'organization_url') ?: $homeUrl,
            'organization_logo' => get_setting('seo', 'organization_logo') ?: null,
            'organization_email' => get_setting('seo', 'organization_email') ?: null,
            'organization_phone' => get_setting('seo', 'organization_phone') ?: null,
            'organization_same_as' => $this->resolveSameAs(
                get_setting('seo', 'organization_same_as')
            ),
            'organization_id' => $homeUrl . '#organization',
            'website_id' => $homeUrl . '#website',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveSameAs(mixed $value): array
    {
        if (!is_string($value) || blank($value)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn(?string $item) => trim((string)$item))
            ->filter(fn(string $item) => filled($item))
            ->values()
            ->all();
    }
}
