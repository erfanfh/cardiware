<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildPollSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Domain\Poll\Models\Poll;
use App\Domain\Seo\Enums\OgTypeEnum;
use Illuminate\Support\Str;

final class PollSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly GetSeoDefaultsAction $defaultsAction,
        private readonly ResolveCanonicalUrlAction $canonicalUrlAction,
        private readonly BuildPollSchemaGraphAction $schemaGraphAction,
        private readonly RenderSchemaGraphAction $renderSchemaGraphAction,
    ) {}

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('polls.index', 'polls.show');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        if ($context->routeIs('polls.index')) {
            return $this->resolveIndex();
        }

        $poll = $context->request->attributes->get('poll');

        if (! $poll instanceof Poll) {
            $poll = Poll::query()
                ->publiclyVisible()
                ->where('slug', (string) $context->request->route('pollSlug'))
                ->firstOrFail();
        }

        $defaults = $this->defaultsAction->execute();
        $question = $this->plainText($poll->question);
        $pageName = $this->plainText($poll->title) ?: Str::limit($question, 70, '…') ?: 'نظرسنجی';
        $title = $pageName.' | '.$defaults['site_name'];
        $description = $this->description($poll, $pageName, $question);
        $canonicalUrl = $this->canonicalUrlAction->execute(
            fallbackUrl: route('polls.show', $poll->slug),
        );
        $image = $defaults['organization_logo']?->url;

        return new SeoDataDTO(
            robots: $defaults['robots'],
            title: $title,
            description: $description,
            canonicalUrl: $canonicalUrl,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::WEBSITE,
                title: $pageName,
                description: $description,
                url: $canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $image,
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $pageName,
                description: $description,
                image: $image,
            ),
            schemaJson: $this->renderSchemaGraphAction->execute(
                $this->schemaGraphAction->execute($poll, $canonicalUrl, $pageName, $description),
            ),
        );
    }

    private function resolveIndex(): SeoDataDTO
    {
        $defaults = $this->defaultsAction->execute();
        $title = 'نظرسنجی‌ها | '.$defaults['site_name'];
        $description = 'نظرسنجی‌های Cardiware را ببینید، در موضوعات مورد علاقه خود رأی دهید و نظر جامعه را دنبال کنید.';
        $url = $this->canonicalUrlAction->execute(fallbackUrl: route('polls.index'));
        $image = $defaults['organization_logo']?->url;

        return new SeoDataDTO(
            robots: $defaults['robots'],
            title: $title,
            description: $description,
            canonicalUrl: $url,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::WEBSITE,
                title: 'نظرسنجی‌ها',
                description: $description,
                url: $url,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $image,
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: 'نظرسنجی‌ها',
                description: $description,
                image: $image,
            ),
            schemaJson: $this->renderSchemaGraphAction->execute([
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'CollectionPage',
                        'name' => 'نظرسنجی‌ها',
                        'url' => $url,
                        'description' => $description,
                        'breadcrumb' => ['@id' => rtrim($url, '/').'/#breadcrumb'],
                    ],
                    [
                        '@type' => 'BreadcrumbList',
                        '@id' => rtrim($url, '/').'/#breadcrumb',
                        'itemListElement' => [
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => $defaults['site_url']],
                            ['@type' => 'ListItem', 'position' => 2, 'name' => 'نظرسنجی‌ها'],
                        ],
                    ],
                ],
            ]),
        );
    }

    private function description(Poll $poll, string $pageName, string $question): string
    {
        $description = $this->plainText($poll->description) ?: $question;

        if ($description === '') {
            $description = 'در نظرسنجی «'.$pageName.'» شرکت کنید و نظر خود را ثبت کنید.';
        }

        return Str::limit($description, 160, '…');
    }

    private function plainText(?string $value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
