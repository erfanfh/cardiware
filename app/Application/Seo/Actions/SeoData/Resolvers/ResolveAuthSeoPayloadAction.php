<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\AuthSeoVariableDataBuilder;

final class ResolveAuthSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction       $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction  $resolveCanonicalUrlAction,
        protected SeoTemplateParser          $seoTemplateParser,
        protected AuthSeoVariableDataBuilder $authSeoVariableDataBuilder,
    )
    {
    }

    public function execute(): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $pageTitle = 'ورود / ثبت نام';
        $variables = $this->authSeoVariableDataBuilder->build($pageTitle);

        $titleTemplate = 'ورود / ثبت نام {separator} {site_title}';

        $descriptionTemplate = 'برای ورود به حساب کاربری یا ثبت نام در {site_title} از این صفحه استفاده کنید.';

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);
        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: front_route('auth.index')
        );

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: 'noindex, follow',

            title: $title,
            description: $description,
            headline: $pageTitle,

            ogTitle: $title,
            ogDescription: $description,

            xTitle: $title,
            xDescription: $description,
        );
    }
}
