<?php

namespace App\Application\Seo\Actions;

class GetSeoDefaultsAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $siteName = get_setting('seo', 'site_name') ?: get_setting('general', 'title') ?: config('app.name');

        return [
            'site_name' => $siteName,
            'site_url' => rtrim(url('/'), '/') . '/',
            'robots' => get_setting('seo', 'robots') ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'og_locale' => get_setting('seo', 'og_locale') ?: 'fa_IR',
            'og_image' => get_setting('seo', 'og_image') ?: null,
            'twitter_card' => get_setting('seo', 'twitter_card') ?: 'summary_large_image',
            'organization_logo' => app(GetOrganizationLogoAction::class)->execute(),
        ];
    }
}
