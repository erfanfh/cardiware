<?php

namespace Tests\Unit\Poll;

use App\Application\Slug\Services\SlugManager;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Infrastructure\Slug\Checkers\PollSlugChecker;
use ReflectionMethod;
use Tests\TestCase;

class PollSlugScopeTest extends TestCase
{
    public function test_poll_slug_checker_is_registered_for_the_poll_scope(): void
    {
        $resolveChecker = new ReflectionMethod(SlugManager::class, 'resolveChecker');
        $checker = $resolveChecker->invoke(app(SlugManager::class), SlugScopeEnum::POLL);

        $this->assertInstanceOf(PollSlugChecker::class, $checker);
        $this->assertTrue($checker->supports(SlugScopeEnum::POLL));
        $this->assertFalse($checker->supports(SlugScopeEnum::CONTENT));
    }
}
