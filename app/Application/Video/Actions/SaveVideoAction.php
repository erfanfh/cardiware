<?php

namespace App\Application\Video\Actions;

use App\Application\Seo\Actions\SyncSeoMetaAction;
use App\Application\Seo\Actions\Analysis\BuildVideoSeoAnalysisInputAction;
use App\Application\Seo\Actions\Analysis\StoreSeoAnalysisAction;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Video\Services\VideoSourceResolver;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Video\Models\Video;
use Illuminate\Support\Facades\DB;

final class SaveVideoAction
{
    public function __construct(
        private readonly AssignSlugAction $assignSlug,
        private readonly VideoSourceResolver $sourceResolver,
        private readonly SyncVideoTaxonomiesAction $syncTaxonomies,
        private readonly SyncSeoMetaAction $syncSeoMeta,
        private readonly StoreSeoAnalysisAction $storeSeoAnalysis,
        private readonly BuildVideoSeoAnalysisInputAction $seoAnalysisInput,
    ) {}

    public function execute(?Video $video, array $data): Video
    {
        $data['title'] = first_filled($data['title'] ?? null, 'بدون عنوان');
        $data['slug'] = first_filled($data['slug'] ?? null, $data['title'], 'untitled-video');
        $data['excerpt'] = (string)($data['excerpt'] ?? '');
        $data['body'] = (string)($data['body'] ?? '');

        return DB::transaction(function () use ($video, $data): Video {
            $source = $this->sourceResolver->resolve($data['source_type'] ?? null, $data['video_url'] ?? null);
            $payload = $data;
            unset($payload['slug'], $payload['category_ids'], $payload['tags'], $payload['seo']);
            $payload['source_type'] = $source['source_type'];
            $payload['video_url'] = $source['video_url'];
            $payload['aparat_video_id'] = $source['aparat_video_id'];

            $video ??= new Video();
            $video->fill($payload)->save();
            $this->assignSlug->execute(new AssignSlugDTO(
                scope: SlugScopeEnum::VIDEO,
                model: $video,
                desiredSlug: first_filled($data['slug'] ?? null, $data['title']),
            ));
            $this->syncTaxonomies->execute($video, $data['category_ids'] ?? [], $data['tags'] ?? []);
            $this->syncSeoMeta->execute($video, $data['seo'] ?? []);
            $this->storeSeoAnalysis->execute($video, $this->seoAnalysisInput);

            return $video->refresh();
        });
    }
}
