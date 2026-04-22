<?php

namespace App\Application\Seo\Support\VariableBuilders;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Identity\Models\User;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

class AuthorSeoVariableDataBuilder
{
    /**
     * @return array<string, string>
     */
    public function build(User $author): array
    {
        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)app(GetSeoDefaultsAction::class)->execute()['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            SeoTemplateVariableEnum::AUTHOR_NAME->value => (string)$this->resolveAuthorName($author),
        ];
    }

    protected function resolveAuthorName(User $author): string
    {
        return (string)(
            $author->name
            ?? $author->display_name
            ?? $author->full_name
            ?? $author->username
            ?? $author->email
            ?? 'نویسنده'
        );
    }
}
