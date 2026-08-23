<?php

namespace Tests\Feature\Poll;

use App\Application\Poll\Actions\SubmitPollVoteAction;
use App\Application\Poll\Support\PollVoterIdentity;
use App\Domain\Identity\Models\User;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Enums\PollResultsVisibilityEnum;
use App\Domain\Poll\Enums\PollStatusEnum;
use App\Domain\Poll\Models\Poll;
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

    public function test_public_page_and_widget_api_expose_published_poll_without_list_route(): void
    {
        [$poll] = $this->makePoll();

        $this->get(route('polls.show', $poll->slug))->assertOk()->assertSee($poll->title);
        $this->getJson(route('api.v1.polls.show', $poll->slug))
            ->assertOk()->assertJsonPath('slug', $poll->slug)->assertJsonCount(2, 'options');

        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('polls.index'));
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
