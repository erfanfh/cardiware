<?php

namespace App\Application\Seo\Support\VariableBuilders;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;
use App\Domain\Video\Models\Video;

class VideoSeoVariableDataBuilder
{
    public function build(Video $video): array
    {
        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)app(GetSeoDefaultsAction::class)->execute()['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            SeoTemplateVariableEnum::VIDEO_TITLE->value => (string)$video->title,
        ];
    }
}
