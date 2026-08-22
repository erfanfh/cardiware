<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Seo\Enums\OgTypeEnum;
use Illuminate\Support\Str;

class RaffleSeoResolver implements SeoResolverInterface
{
    public function __construct(private readonly GetSeoDefaultsAction $defaultsAction) {}

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('raffles.index', 'raffles.show');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $defaults = $this->defaultsAction->execute();

        if ($context->routeIs('raffles.index')) {
            $title = 'قرعه‌کشی‌ها | '.$defaults['site_name'];
            $description = 'مشاهده قرعه‌کشی‌های فعال، جوایز، روش‌های دریافت بلیط و نتایج قابل صحت‌سنجی.';
            $url = route('raffles.index');
            $image = $defaults['organization_logo']['url'] ?? null;
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'قرعه‌کشی‌ها',
                'url' => $url,
                'description' => $description,
            ];
        } else {
            $slug = (string) $context->request->route('raffleSlug');
            $campaign = DrawCampaign::query()
                ->where('slug', $slug)
                ->with('coverMediaFile.primaryVariant')
                ->firstOrFail();
            $title = $campaign->title.' | قرعه‌کشی '.$defaults['site_name'];
            $description = Str::limit(trim(strip_tags((string) ($campaign->short_description ?: $campaign->body))), 155, '…');
            $url = route('raffles.show', $campaign->slug);
            $image = $campaign->coverMediaFile?->url ?: ($defaults['organization_logo']['url'] ?? null);
            $schema = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => $campaign->title,
                'description' => $description,
                'url' => $url,
                'image' => $image ? [$image] : null,
                'startDate' => $campaign->starts_at?->toIso8601String(),
                'endDate' => $campaign->draw_at?->toIso8601String() ?: $campaign->closes_at?->toIso8601String(),
                'eventStatus' => $campaign->status->value === 'cancelled'
                    ? 'https://schema.org/EventCancelled'
                    : 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode',
                'location' => [
                    '@type' => 'VirtualLocation',
                    'url' => $url,
                ],
            ]);
        }

        return new SeoDataDTO(
            robots: $defaults['robots'],
            title: $title,
            description: $description,
            canonicalUrl: $url,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::WEBSITE,
                title: $title,
                description: $description,
                url: $url,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $image,
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $title,
                description: $description,
                image: $image,
            ),
            schemaJson: json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );
    }
}
