<?php

namespace Tests\Feature\Poll;

use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Enums\PollStatusEnum;
use App\Domain\Poll\Models\Poll;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ForceTrailingSlash::class);
    }

    public function test_index_only_lists_published_polls_and_filters_by_timing(): void
    {
        $active = $this->makePoll('نظرسنجی فعال', ['ends_at' => now()->addDay()]);
        $upcoming = $this->makePoll('نظرسنجی آینده', ['starts_at' => now()->addDay()]);
        $ended = $this->makePoll('نظرسنجی پایان‌یافته', ['ends_at' => now()->subDay()]);
        $this->makePoll('پیش‌نویس محرمانه', ['status' => PollStatusEnum::DRAFT]);
        $this->makePoll('بایگانی محرمانه', ['status' => PollStatusEnum::ARCHIVED]);

        $index = $this->get($this->indexUrl())
            ->assertOk()
            ->assertSee($active->title)
            ->assertSee($upcoming->title)
            ->assertSee($ended->title)
            ->assertDontSee('پیش‌نویس محرمانه')
            ->assertDontSee('بایگانی محرمانه');

        self::assertSame(1, substr_count($index->getContent(), '<title>'));
        $index->assertSee('<title>نظرسنجی‌ها | ', false);

        $this->get($this->indexUrl(['status' => 'active']))
            ->assertOk()
            ->assertSee($active->title)
            ->assertDontSee($upcoming->title)
            ->assertDontSee($ended->title);

        $this->get($this->indexUrl(['status' => 'upcoming']))
            ->assertOk()->assertSee($upcoming->title)->assertDontSee($active->title);

        $this->get($this->indexUrl(['status' => 'ended']))
            ->assertOk()->assertSee($ended->title)->assertDontSee($active->title);
    }

    public function test_index_search_and_response_count_visibility(): void
    {
        $poll = $this->makePoll('بهترین بازی سال', [
            'question' => '<p>کدام بازی را بیشتر دوست دارید؟</p>',
            'responses_count' => 143,
            'show_response_count' => false,
        ]);
        $this->makePoll('موضوع دیگر');

        $this->get($this->indexUrl(['search' => 'بهترین']))
            ->assertOk()
            ->assertSee($poll->title)
            ->assertDontSee('موضوع دیگر')
            ->assertDontSee('143 پاسخ');
    }

    private function makePoll(string $title, array $attributes = []): Poll
    {
        return Poll::query()->create(array_merge([
            'title' => $title,
            'slug' => 'poll-'.str()->random(8),
            'question' => '<p>نظر شما چیست؟</p>',
            'status' => PollStatusEnum::PUBLISHED,
            'audience' => PollAudienceEnum::EVERYONE,
        ], $attributes));
    }

    private function indexUrl(array $query = []): string
    {
        $url = rtrim(route('polls.index'), '/').'/';

        return $query ? $url.'?'.http_build_query($query) : $url;
    }
}
