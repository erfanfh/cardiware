<?php

namespace App\Application\Seo\Support\VariableBuilders;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Project\Models\Project;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

class ProjectSeoVariableDataBuilder
{
    /**
     * @return array<string, string>
     */
    public function build(Project $project): array
    {
        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)app(GetSeoDefaultsAction::class)->execute()['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            SeoTemplateVariableEnum::PROJECT_TITLE->value => (string)$project->title,
        ];
    }
}
