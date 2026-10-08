<?php

namespace Tests\Feature\Raffle;

use App\Application\Raffle\Actions\Campaign\CreateDrawCampaignAction;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaffleBreadcrumbTest extends TestCase
{
    use RefreshDatabase;

    public function test_raffle_index_uses_the_same_breadcrumb_in_schema_and_page(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);

        $response = $this->get(route('raffles.index'))->assertOk();
        $html = $response->getContent();

        self::assertSame(1, preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $schemaMatches));
        $schema = json_decode($schemaMatches[1], true, flags: JSON_THROW_ON_ERROR);
        $breadcrumb = collect($schema['@graph'])->firstWhere('@type', 'BreadcrumbList');

        self::assertSame(['خانه', 'قرعه کشی ها'], array_column($breadcrumb['itemListElement'], 'name'));
        self::assertSame(1, preg_match('/<nav[^>]*aria-label="مسیر صفحه"[^>]*>(.*?)<\/nav>/s', $html, $breadcrumbMatches));
        self::assertStringContainsString('قرعه کشی ها', $breadcrumbMatches[1]);
    }

    public function test_raffle_detail_uses_the_same_breadcrumb_in_schema_and_page(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        $campaign = app(CreateDrawCampaignAction::class)->execute([
            'title' => 'قرعه کشی نمونه',
            'slug' => 'sample-raffle',
        ]);
        $campaign->update(['status' => DrawCampaignStatusEnum::OPEN]);

        $response = $this->get(route('raffles.show', $campaign->slug))->assertOk();
        $html = $response->getContent();

        self::assertSame(1, preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $schemaMatches));
        $schema = json_decode($schemaMatches[1], true, flags: JSON_THROW_ON_ERROR);
        $breadcrumb = collect($schema['@graph'])->firstWhere('@type', 'BreadcrumbList');

        self::assertSame(['خانه', 'قرعه کشی ها', $campaign->title], array_column($breadcrumb['itemListElement'], 'name'));
        self::assertSame(1, preg_match('/<nav[^>]*aria-label="مسیر صفحه"[^>]*>(.*?)<\/nav>/s', $html, $breadcrumbMatches));
        self::assertStringContainsString($campaign->title, $breadcrumbMatches[1]);
    }
}
