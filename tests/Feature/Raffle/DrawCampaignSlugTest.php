<?php

namespace Tests\Feature\Raffle;

use App\Application\Raffle\Actions\Campaign\CreateDrawCampaignAction;
use App\Application\Raffle\Actions\Campaign\UpdateDrawCampaignAction;
use App\Application\Slug\DTOs\CheckSlugDTO;
use App\Application\Slug\Services\SlugManager;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrawCampaignSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_slugs_use_the_shared_normalizer_and_collision_suggestions(): void
    {
        $first = app(CreateDrawCampaignAction::class)->execute([
            'title' => 'قرعه کشی تابستان',
            'slug' => 'Summer_Draw',
        ]);

        $second = app(CreateDrawCampaignAction::class)->execute([
            'title' => 'قرعه کشی دوم',
            'slug' => 'Summer Draw',
        ]);

        $this->assertSame('summer-draw', $first->fresh()->slug);
        $this->assertSame('summer-draw-2', $second->fresh()->slug);
    }

    public function test_updating_a_campaign_releases_the_previous_slug_without_history(): void
    {
        $campaign = app(CreateDrawCampaignAction::class)->execute([
            'title' => 'قرعه کشی نمونه',
            'slug' => 'old-slug',
        ]);

        app(UpdateDrawCampaignAction::class)->execute($campaign, [
            'title' => 'قرعه کشی نمونه',
            'slug' => 'New / Slug',
        ]);

        $result = app(SlugManager::class)->check(new CheckSlugDTO(
            scope: SlugScopeEnum::RAFFLE,
            slug: 'old-slug',
        ));

        $this->assertSame('new-slug', $campaign->fresh()->slug);
        $this->assertTrue($result->available);
    }
}
