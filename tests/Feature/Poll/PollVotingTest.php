<?php

namespace Tests\Feature\Poll;

use App\Application\Poll\Actions\SubmitPollVoteAction;
use App\Application\Poll\Support\PollVoterIdentity;
use App\Domain\Identity\Models\User;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Enums\PollResultsVisibilityEnum;
use App\Domain\Poll\Enums\PollStatusEnum;
use App\Domain\Poll\Models\Poll;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PollVotingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_identified_by_ip_and_cannot_vote_twice_by_default(): void
    {
        [$poll, $first] = $this->makePoll();
        $identity = PollVoterIdentity::resolve(null, '203.0.113.10');

        app(SubmitPollVoteAction::class)->execute($poll, $identity, [$first->id]);

        $this->assertSame(1, $poll->fresh()->responses_count);
        $this->assertSame(1, $first->fresh()->responses_count);
        $this->assertDatabaseMissing('poll_responses', ['ip_hash' => '203.0.113.10']);

        $this->expectException(ValidationException::class);
        app(SubmitPollVoteAction::class)->execute($poll->fresh(), $identity, [$first->id]);
    }

    public function test_member_identity_uses_user_id_even_when_ip_changes(): void
    {
        [$poll, $first] = $this->makePoll();
        $user = User::factory()->create();

        app(SubmitPollVoteAction::class)->execute($poll, PollVoterIdentity::resolve($user, '203.0.113.11'), [$first->id]);

        $this->expectException(ValidationException::class);
        app(SubmitPollVoteAction::class)->execute($poll->fresh(), PollVoterIdentity::resolve($user, '203.0.113.99'), [$first->id]);
    }

    public function test_vote_change_keeps_response_total_and_updates_option_counters(): void
    {
        [$poll, $first, $second] = $this->makePoll(['allow_vote_changes' => true]);
        $identity = PollVoterIdentity::resolve(null, '203.0.113.12');
        $action = app(SubmitPollVoteAction::class);

        $action->execute($poll, $identity, [$first->id]);
        $action->execute($poll->fresh(), $identity, [$second->id]);

        $this->assertSame(1, $poll->fresh()->responses_count);
        $this->assertSame(0, $first->fresh()->responses_count);
        $this->assertSame(1, $second->fresh()->responses_count);
    }

    public function test_members_only_poll_rejects_guest_vote(): void
    {
        [$poll, $first] = $this->makePoll(['audience' => PollAudienceEnum::MEMBERS]);

        $this->expectException(ValidationException::class);
        app(SubmitPollVoteAction::class)->execute($poll, PollVoterIdentity::resolve(null, '203.0.113.13'), [$first->id]);
    }

    public function test_public_page_and_widget_api_expose_published_poll(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        [$poll] = $this->makePoll();

        $this->get(route('polls.show', $poll->slug))->assertOk()->assertSee($poll->title);
        $this->getJson(route('api.v1.polls.show', $poll->slug))
            ->assertOk()->assertJsonPath('slug', $poll->slug)->assertJsonCount(2, 'options');

        $this->assertTrue(app('router')->getRoutes()->hasNamedRoute('polls.index'));
    }

    public function test_public_poll_page_renders_complete_unique_seo_metadata_and_schema(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        [$poll] = $this->makePoll([
            'title' => 'بهترین بازی نقش‌آفرینی سال',
            'question' => '<p>به نظر شما کدام بازی نقش‌آفرینی شایسته انتخاب شدن است؟</p>',
            'description' => '<p>در این نظرسنجی، بهترین بازی نقش‌آفرینی سال را با رأی کاربران انتخاب می‌کنیم.</p>',
        ]);

        $response = $this->get(route('polls.show', $poll->slug).'/')->assertOk();
        $html = $response->getContent();
        $canonical = rtrim(route('polls.show', $poll->slug), '/').'/';

        self::assertSame(1, substr_count($html, '<title>'));
        $response->assertSee('<title>'.$poll->title.' | ', false);
        $response->assertSee('<meta name="description" content="در این نظرسنجی، بهترین بازی نقش‌آفرینی سال را با رأی کاربران انتخاب می‌کنیم.">', false);
        $response->assertSee('<link rel="canonical" href="'.$canonical.'">', false);
        $response->assertSee('<meta property="og:title" content="'.$poll->title.'">', false);
        $response->assertSee('<meta property="og:url" content="'.$canonical.'">', false);
        $response->assertSee('<meta name="twitter:title" content="'.$poll->title.'">', false);

        self::assertSame(1, preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches));
        $schema = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        $types = array_column($schema['@graph'], '@type');

        self::assertContains('WebPage', $types);
        self::assertContains('BreadcrumbList', $types);
        self::assertNotContains('QAPage', $types);

        $breadcrumbNode = collect($schema['@graph'])->firstWhere('@type', 'BreadcrumbList');
        self::assertSame(['خانه', 'نظرسنجی‌ها', $poll->title], array_column($breadcrumbNode['itemListElement'], 'name'));
        self::assertSame(1, preg_match('/<nav[^>]*aria-label="مسیر صفحه"[^>]*>(.*?)<\/nav>/s', $html, $breadcrumbMatches));
        self::assertStringContainsString('نظرسنجی‌ها', $breadcrumbMatches[1]);
        self::assertStringContainsString($poll->title, $breadcrumbMatches[1]);

        $pageNode = collect($schema['@graph'])->firstWhere('@type', 'WebPage');
        self::assertSame($canonical, $pageNode['url']);
        self::assertSame($poll->title, $pageNode['name']);
    }

    private function makePoll(array $attributes = []): array
    {
        $poll = Poll::query()->create(array_merge([
            'title' => 'نظرسنجی تست',
            'slug' => 'test-poll-'.str()->random(6),
            'question' => '<p>کدام گزینه را انتخاب می‌کنید؟</p>',
            'status' => PollStatusEnum::PUBLISHED,
            'audience' => PollAudienceEnum::EVERYONE,
            'results_visibility' => PollResultsVisibilityEnum::AFTER_VOTE,
            'allows_multiple_selections' => false,
            'min_selections' => 1,
            'max_selections' => 1,
            'allow_vote_changes' => false,
            'randomize_options' => false,
            'show_response_count' => true,
        ], $attributes));

        $first = $poll->options()->create(['label' => 'گزینه اول', 'color' => '#f97316', 'sort_order' => 0, 'is_active' => true]);
        $second = $poll->options()->create(['label' => 'گزینه دوم', 'color' => '#0ea5e9', 'sort_order' => 1, 'is_active' => true]);

        return [$poll, $first, $second];
    }
}
