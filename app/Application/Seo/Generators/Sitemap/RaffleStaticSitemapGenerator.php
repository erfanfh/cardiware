<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RaffleStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'raffle';
    }

    protected function query(): Builder
    {
        return DrawCampaign::query()
            ->whereIn('status', [
                DrawCampaignStatusEnum::SCHEDULED->value,
                DrawCampaignStatusEnum::OPEN->value,
                DrawCampaignStatusEnum::CLOSED->value,
                DrawCampaignStatusEnum::DRAWING->value,
                DrawCampaignStatusEnum::COMPLETED->value,
            ])
            ->whereNotNull('slug')
            ->with('coverMediaFile')
            ->select(['id', 'slug', 'title', 'updated_at', 'cover_media_file_id']);
    }

    protected function firstPageStaticUrls(): array
    {
        $lastUpdated = DrawCampaign::query()->max('updated_at');

        return [new SitemapUrlDTO(route('raffles.index'), $lastUpdated ? Carbon::parse($lastUpdated) : null)];
    }

    protected function filenameForPage(int $page): string
    {
        return $page === 1 ? 'raffle-sitemap.xml' : "raffle-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return url($page === 1 ? '/raffle-sitemap.xml' : "/raffle-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var DrawCampaign $model */
        $image = $model->coverMediaFile?->url;

        return new SitemapUrlDTO(
            route('raffles.show', $model->slug),
            $model->updated_at,
            $image ? [new SitemapImageDTO($this->absoluteUrl($image), $model->title)] : [],
        );
    }

    private function absoluteUrl(string $path): string
    {
        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : url('/'.ltrim($path, '/'));
    }
}
