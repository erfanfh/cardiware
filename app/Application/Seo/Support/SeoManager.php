<?php

namespace App\Application\Seo\Support;

use App\Application\Seo\Actions\RenderSeoHeadAction;
use App\Application\Seo\DTOs\SeoDataDTO;
use Illuminate\Http\Request;

class SeoManager
{
    private ?SeoDataDTO $seoData = null;

    public function __construct(
        private readonly SeoResolverRegistry $resolverRegistry,
        private readonly Request             $request,
    )
    {
    }

    public function set(SeoDataDTO $seoData): static
    {
        $this->seoData = $seoData;

        return $this;
    }

    public function get(): ?SeoDataDTO
    {
        return $this->seoData;
    }

    public function hasData(): bool
    {
        return $this->seoData !== null;
    }

    public function clear(): void
    {
        $this->seoData = null;
    }

    public function resolveCurrent(): static
    {
        if ($this->hasData()) {
            return $this;
        }

        $context = SeoContext::fromRequest($this->request);

        $resolver = $this->resolverRegistry->resolve($context);

        if (!$resolver) {
            return $this;
        }

        $this->seoData = $resolver->resolve($context);

        return $this;
    }

    public function render(): string
    {
        $this->resolveCurrent();

        if (!$this->seoData) {
            return '';
        }

        return app(RenderSeoHeadAction::class)
            ->execute($this->seoData)
            ->render();
    }
}
