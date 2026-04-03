<?php

namespace App\Application\Content\DTOs;

use App\Domain\Content\Enums\ResolvedContentTypeEnum;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;

final class ResolvedContentDTO
{
    public function __construct(
        public readonly ResolvedContentTypeEnum $type,
        public readonly Page|Post|null          $model = null,
        public readonly ?string                 $redirectUrl = null,
    )
    {
    }

    public static function notFound(): self
    {
        return new self(
            type: ResolvedContentTypeEnum::NOT_FOUND,
        );
    }

    public static function redirect(string $redirectUrl): self
    {
        return new self(
            type: ResolvedContentTypeEnum::REDIRECT,
            redirectUrl: $redirectUrl,
        );
    }

    public static function page(Page $page): self
    {
        return new self(
            type: ResolvedContentTypeEnum::PAGE,
            model: $page,
        );
    }

    public static function post(Post $post): self
    {
        return new self(
            type: ResolvedContentTypeEnum::POST,
            model: $post,
        );
    }

    public function isNotFound(): bool
    {
        return $this->type === ResolvedContentTypeEnum::NOT_FOUND;
    }

    public function shouldRedirect(): bool
    {
        return $this->type === ResolvedContentTypeEnum::REDIRECT;
    }

    public function isPage(): bool
    {
        return $this->type === ResolvedContentTypeEnum::PAGE;
    }

    public function isPost(): bool
    {
        return $this->type === ResolvedContentTypeEnum::POST;
    }
}
