<?php

namespace Tests\Unit\Poll;

use App\Application\Poll\Listings\PollListing;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PollListingTest extends TestCase
{
    #[DataProvider('filterProvider')]
    public function test_poll_listing_exposes_admin_filters(string $filter): void
    {
        $this->assertContains($filter, app(PollListing::class)->filters());
    }

    public static function filterProvider(): array
    {
        return [
            ['id'],
            ['search'],
            ['status'],
            ['audience'],
            ['results_visibility'],
            ['timing'],
            ['starts_at_from'],
            ['ends_at_to'],
            ['created_at_from'],
        ];
    }

    public function test_poll_listing_supports_statistical_and_scheduling_sorts(): void
    {
        $sorts = app(PollListing::class)->sortable();

        $this->assertArrayHasKey('responses_count', $sorts);
        $this->assertArrayHasKey('views_count', $sorts);
        $this->assertArrayHasKey('starts_at', $sorts);
    }
}
