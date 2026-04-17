<?php

namespace App\Application\Seo\Support;

use Illuminate\Http\Request;

readonly class SeoContext
{
    public function __construct(
        public Request $request,
        public ?string $routeName = null,
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            request: $request,
            routeName: $request->route()?->getName(),
        );
    }

    public function routeIs(string ...$patterns): bool
    {
        if (!$this->routeName) {
            return false;
        }

        return $this->request->routeIs(...$patterns);
    }
}
