<?php

namespace Tests\Unit\Content;

use App\Application\Content\Actions\GetAboutUsPageDataAction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GetAboutUsPageDataActionTest extends TestCase
{
    #[DataProvider('contentUrlProvider')]
    public function test_content_links_are_limited_to_safe_local_or_http_urls(mixed $value, ?string $expected): void
    {
        $action = new class extends GetAboutUsPageDataAction
        {
            public function __construct() {}

            public function sanitize(mixed $value): ?string
            {
                return $this->contentUrl($value);
            }
        };

        self::assertSame($expected, $action->sanitize($value));
    }

    /** @return array<string, array{mixed, ?string}> */
    public static function contentUrlProvider(): array
    {
        return [
            'local path' => ['/projects', '/projects'],
            'secure external URL' => [' https://example.com/projects ', 'https://example.com/projects'],
            'insecure external URL' => ['http://example.com', 'http://example.com'],
            'protocol-relative URL' => ['//example.com', null],
            'javascript URL' => ['javascript:alert(1)', null],
            'empty URL' => ['', null],
            'non-string value' => [123, null],
        ];
    }
}
