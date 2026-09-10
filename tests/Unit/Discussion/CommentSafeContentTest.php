<?php

namespace Tests\Unit\Discussion;

use App\Domain\Discussion\Models\Comment;
use Tests\TestCase;

class CommentSafeContentTest extends TestCase
{
    public function test_it_preserves_lists_without_their_attributes(): void
    {
        $comment = new Comment([
            'content' => "<ul class=\"legacy\">\n<li onclick=\"alert(1)\">مورد اول</li>\n<li>مورد دوم</li>\n</ul>",
        ]);

        self::assertSame(
            '<ul><li>مورد اول</li><li>مورد دوم</li></ul>',
            $comment->safe_content,
        );
    }

    public function test_it_still_escapes_every_other_html_tag(): void
    {
        $comment = new Comment([
            'content' => '<ul><li>امن</li></ul><script>alert("xss")</script><img src=x onerror=alert(1)>',
        ]);

        self::assertStringContainsString('<ul><li>امن</li></ul>', $comment->safe_content);
        self::assertStringContainsString('&lt;script&gt;', $comment->safe_content);
        self::assertStringContainsString('&lt;img', $comment->safe_content);
        self::assertStringNotContainsString('<script>', $comment->safe_content);
        self::assertStringNotContainsString('<img', $comment->safe_content);
    }

    public function test_plain_text_line_breaks_are_unchanged(): void
    {
        $comment = new Comment([
            'content' => "خط اول\nخط دوم",
        ]);

        self::assertSame("خط اول<br />\nخط دوم", $comment->safe_content);
    }

    public function test_list_markup_is_removed_cleanly_from_compact_previews(): void
    {
        $comment = new Comment([
            'content' => '<ul><li>مورد اول</li><li>مورد دوم</li></ul>',
        ]);

        self::assertSame('مورد اول مورد دوم', $comment->plain_content);
    }
}
