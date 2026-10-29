<?php

namespace Tests\Feature\Cache;

use App\Application\Content\Actions\GetAboutUsPageDataAction;
use App\Application\Content\Actions\GetCareerPageDataAction;
use App\Application\Content\Actions\GetTeamPageDataAction;
use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Application\Notification\GetAccountAnnouncementsAction;
use App\Application\Notification\GetVisiblePopupsAction;
use App\Application\Poll\Actions\BuildPollResultsAction;
use App\Application\Poll\Actions\RecordPollViewAction;
use App\Application\Poll\Actions\SubmitPollVoteAction;
use App\Application\Poll\Support\PollVoterIdentity;
use App\Application\Support\Actions\GetActiveTicketDepartmentsAction;
use App\Application\System\Actions\Footer\GetFooterNewsAction;
use App\Domain\Career\Models\CareerPosition;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Notification\Models\AccountAnnouncement;
use App\Domain\Notification\Models\Popup;
use App\Domain\Organization\Models\TeamMember;
use App\Domain\Poll\Models\Poll;
use App\Domain\Support\Models\TicketDepartment;
use App\Domain\Video\Models\Video;
use App\Domain\Vip\Models\VipPlan;
use App\Http\Controllers\Front\Poll\PollController;
use App\Http\Controllers\Front\Poll\PollIndexController;
use App\Http\Controllers\Front\Search\SearchController;
use App\Http\Controllers\Front\Video\VideoController;
use App\Http\Controllers\Front\Vip\VipPlanPageController;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\TeamSettings;
use App\Infrastructure\Services\Setting\Groups\VipSettings;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicPageCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
    }

    public function test_video_pages_reuse_queries_and_refresh_after_video_changes(): void
    {
        $video = $this->video();
        $controller = app(VideoController::class);
        $request = $this->request('/videos');
        self::assertSame($video->id, $controller->index($request)->getData()['videos']->first()->id);
        $this->assertNoQueries(fn () => $controller->index($request));

        $video->update(['title' => 'Changed video', 'is_featured' => true]);
        $data = $controller->index($request)->getData();
        self::assertSame('Changed video', $data['videos']->first()->title);
        self::assertSame('Changed video', $data['featured']->title);
        $video->delete();
        self::assertSame(0, $controller->index($request)->getData()['totalVideos']);
    }

    public function test_scheduled_videos_appear_without_an_update(): void
    {
        $this->freezeTime();
        $video = $this->video(['published_at' => now()->addSeconds(10)]);
        $controller = app(VideoController::class);
        $request = $this->request('/videos');
        self::assertSame(0, $controller->index($request)->getData()['totalVideos']);
        $this->travel(10)->seconds();
        self::assertSame($video->id, $controller->index($request)->getData()['videos']->first()->id);
    }

    public function test_search_caches_counts_and_results_but_updates_and_filters_stay_correct(): void
    {
        $post = $this->createPost();
        $controller = app(SearchController::class);
        $request = $this->request('/search?q=Original&type=posts');
        $data = $controller->index($request)->getData();
        self::assertSame(1, $data['postsCount']);
        self::assertSame($post->id, $data['posts']->first()->id);
        $this->assertNoQueries(fn () => $controller->index($request));

        $post->update(['title' => 'Different title']);
        self::assertSame(0, $controller->index($request)->getData()['postsCount']);
        $request = $this->request('/search?q=Different&type=posts');
        self::assertSame($post->id, $controller->index($request)->getData()['posts']->first()->id);
        $request = $this->request('/search?q=Different&type=videos');
        self::assertSame(0, $controller->index($request)->getData()['videos']->total());
    }

    public function test_footer_news_is_cached_and_refreshes_after_post_changes(): void
    {
        $post = $this->createPost();
        $action = app(GetFooterNewsAction::class);
        self::assertSame('Original title', $action->execute()[0]['title']);
        $this->assertNoQueries(fn () => $action->execute());
        $post->update(['title' => 'New headline']);
        self::assertSame('New headline', $action->execute()[0]['title']);
    }

    public function test_career_selection_is_per_request_and_publication_and_edits_refresh_the_cache(): void
    {
        $this->freezeTime();
        $position = CareerPosition::query()->create([
            'title' => 'Developer', 'slug' => 'developer', 'summary' => 'Build games',
            'status' => 'active', 'published_at' => now()->addSeconds(10),
        ]);
        $action = app(GetCareerPageDataAction::class);
        self::assertCount(0, $action->execute()['positions']);
        $this->travel(10)->seconds();
        self::assertSame($position->id, $action->execute($position->id)['selectedPositionId']);
        self::assertNull($action->execute(99999)['selectedPositionId']);
        $this->assertNoQueries(fn () => $action->execute());
        $position->update(['status' => 'closed']);
        self::assertCount(0, $action->execute()['positions']);
    }

    public function test_team_and_about_pages_reuse_queries_and_refresh_member_data(): void
    {
        $member = TeamMember::query()->create(['first_name' => 'First', 'last_name' => 'Member']);
        app(TeamSettings::class)->set('member_ids', [$member->id]);
        $team = app(GetTeamPageDataAction::class);
        $about = app(GetAboutUsPageDataAction::class);
        self::assertSame('First', $team->execute()['teamMembers']->first()->first_name);
        self::assertSame('First', $about->execute()['aboutUsTeamMembers']->first()->first_name);
        $this->assertNoQueries(fn () => [$team->execute(), $about->execute()]);
        $member->update(['first_name' => 'Updated']);
        self::assertSame('Updated', $team->execute()['teamMembers']->first()->first_name);
        self::assertSame('Updated', $about->execute()['aboutUsTeamMembers']->first()->first_name);
        app(TeamSettings::class)->set('member_ids', []);
        self::assertCount(0, $team->execute()['teamMembers']);
    }

    public function test_vip_plan_prices_and_availability_refresh_after_editing(): void
    {
        $plan = VipPlan::query()->create([
            'code' => 'monthly', 'title' => 'Monthly', 'duration_days' => 30,
            'price_toman' => 1000, 'is_active' => true,
        ]);
        $controller = app(VipPlanPageController::class);
        $settings = app(VipSettings::class);
        self::assertSame(1000, $controller($settings)->getData()['plans']->first()->price_toman);
        $this->assertNoQueries(fn () => $controller($settings));
        $plan->update(['price_toman' => 2000]);
        self::assertSame(2000, $controller($settings)->getData()['plans']->first()->price_toman);
        $plan->update(['is_active' => false]);
        self::assertCount(0, $controller($settings)->getData()['plans']);
    }

    public function test_leaderboard_does_not_share_current_user_data_and_points_invalidate_public_results(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $account = new PointAccount;
        $account->forceFill(['user_id' => $first->id, 'balance' => 100, 'lifetime_earned' => 100])->save();
        $other = new PointAccount;
        $other->forceFill(['user_id' => $second->id, 'balance' => 50, 'lifetime_earned' => 50])->save();
        $action = app(GetAudienceClubLeaderboardAction::class);

        self::assertSame(1, $action->execute($first)['currentMember']['rank']);
        self::assertSame(2, $action->execute($second)['currentMember']['rank']);
        self::assertNull($action->execute(null)['currentMember']['rank']);
        $this->assertNoQueries(fn () => $action->execute(null));
        $other->forceFill(['balance' => 200, 'lifetime_earned' => 200])->save();
        self::assertSame($second->id, $action->execute(null)['leaders']->first()->user_id);
        $second->delete();
        self::assertSame([$first->id], $action->execute(null)['leaders']->pluck('user_id')->all());
        $second->restore();
        self::assertSame($second->id, $action->execute(null)['leaders']->first()->user_id);
    }

    public function test_poll_filters_and_counts_change_exactly_at_the_start_and_end(): void
    {
        $this->freezeTime();
        $poll = $this->poll(['starts_at' => now()->addSeconds(10), 'ends_at' => now()->addSeconds(20)]);
        $controller = app(PollIndexController::class);
        $request = $this->request('/polls?status=active');
        self::assertSame(0, $controller($request)->getData()['stats']['active']);
        $this->travel(10)->seconds();
        $data = $controller($request)->getData();
        self::assertSame(1, $data['stats']['active']);
        self::assertSame($poll->id, $data['polls']->first()->id);
        $this->assertNoQueries(fn () => $controller($request));
        $this->travel(10)->seconds();
        $data = $controller($request)->getData();
        self::assertSame(1, $data['stats']['ended']);
        self::assertSame(0, $data['polls']->total());
    }

    public function test_poll_votes_refresh_shared_results_without_sharing_the_voters_response(): void
    {
        $poll = $this->poll();
        $option = $poll->options()->create(['label' => 'First option', 'is_active' => true, 'sort_order' => 0]);
        $controller = app(PollController::class);
        $read = function (string $ip) use ($controller, $poll): array {
            $request = $this->request('/polls/'.$poll->slug, $ip);

            return $controller($request, $poll->slug, app(RecordPollViewAction::class), app(BuildPollResultsAction::class))->getData();
        };
        self::assertFalse($read('203.0.113.10')['hasVoted']);
        app(SubmitPollVoteAction::class)->execute($poll, PollVoterIdentity::resolve(null, '203.0.113.10'), [$option->id]);
        $voter = $read('203.0.113.10');
        self::assertTrue($voter['hasVoted']);
        self::assertSame(1, $voter['results']['responses']);
        self::assertSame(1, $voter['results']['options'][0]['count']);
        $guest = $read('203.0.113.11');
        self::assertFalse($guest['hasVoted']);
        self::assertNull($guest['response']);
        self::assertNull($guest['results']);
    }

    public function test_announcements_follow_their_schedule_and_edits_without_repeated_queries(): void
    {
        $this->freezeTime();
        $announcement = AccountAnnouncement::query()->create([
            'title' => 'News', 'body' => 'Announcement', 'type' => 'info', 'is_active' => true,
            'starts_at' => now()->addSeconds(10), 'ends_at' => now()->addSeconds(20),
        ]);
        $action = app(GetAccountAnnouncementsAction::class);
        self::assertCount(0, $action->execute());
        $this->travel(10)->seconds();
        self::assertSame('News', $action->execute()->first()->title);
        $this->assertNoQueries(fn () => $action->execute());
        $announcement->update(['title' => 'Updated news']);
        self::assertSame('Updated news', $action->execute()->first()->title);
        $this->travel(10)->seconds();
        self::assertCount(0, $action->execute());
    }

    public function test_popup_audiences_and_targets_have_separate_cache_entries(): void
    {
        $user = User::factory()->create();
        $popup = Popup::query()->create([
            'title' => 'Guests only', 'body' => 'Hello', 'target_type' => 'product',
            'target_id' => 42, 'audience' => 'guest', 'is_active' => true,
        ]);
        $action = app(GetVisiblePopupsAction::class);
        self::assertSame($popup->id, $action->execute('product', 42, null)->first()->id);
        $this->assertNoQueries(fn () => $action->execute('product', 42, null));
        self::assertCount(0, $action->execute('product', 43, null));
        self::assertCount(0, $action->execute('product', 42, $user));
        $popup->update(['is_active' => false]);
        self::assertCount(0, $action->execute('product', 42, null));
    }

    public function test_ticket_department_changes_invalidate_the_shared_form_options(): void
    {
        $department = TicketDepartment::query()->create(['title' => 'Support', 'is_active' => true]);
        $action = app(GetActiveTicketDepartmentsAction::class);
        self::assertSame('Support', $action->execute()->first()->title);
        $this->assertNoQueries(fn () => $action->execute());
        $department->update(['title' => 'New department']);
        self::assertSame('New department', $action->execute()->first()->title);
        $department->update(['is_active' => false]);
        self::assertCount(0, $action->execute());
    }

    private function assertNoQueries(Closure $callback): void
    {
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $callback();
            self::assertSame([], DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
    }

    private function request(string $url, string $ip = '203.0.113.10'): Request
    {
        $request = Request::create($url, server: ['REMOTE_ADDR' => $ip]);
        $this->app->instance('request', $request);

        return $request;
    }

    private function createPost(): Post
    {
        return Post::query()->create([
            'author_id' => User::factory()->create()->id, 'title' => 'Original title',
            'slug' => 'post-'.str()->random(8), 'excerpt' => '', 'body' => '',
            'status' => 'active', 'published_at' => now()->subMinute(),
        ]);
    }

    private function video(array $attributes = []): Video
    {
        return Video::query()->create(array_merge([
            'title' => 'Video', 'slug' => 'video-'.str()->random(8), 'status' => 'active',
            'source_type' => 'direct', 'video_url' => 'https://example.test/video.mp4',
            'published_at' => now()->subMinute(),
        ], $attributes));
    }

    private function poll(array $attributes = []): Poll
    {
        return Poll::query()->create(array_merge([
            'title' => 'Poll', 'slug' => 'poll-'.str()->random(8), 'question' => 'Which?',
            'status' => 'published', 'audience' => 'everyone', 'results_visibility' => 'after_vote',
            'min_selections' => 1, 'max_selections' => 1,
        ], $attributes));
    }
}
