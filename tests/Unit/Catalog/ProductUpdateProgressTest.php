<?php

namespace Tests\Unit\Catalog;

use App\Domain\Catalog\Models\ProductUpdateProgress;
use PHPUnit\Framework\TestCase;

class ProductUpdateProgressTest extends TestCase
{
    public function test_it_uses_the_saved_box_content(): void
    {
        $progress = new ProductUpdateProgress([
            'title' => 'وضعیت انتشار نسخه ۲',
            'description' => 'جزئیات این نسخه در حال تکمیل است.',
        ]);

        self::assertSame('وضعیت انتشار نسخه ۲', $progress->display_title);
        self::assertSame('جزئیات این نسخه در حال تکمیل است.', $progress->display_description);
    }

    public function test_it_preserves_the_previous_content_as_the_default(): void
    {
        $progress = new ProductUpdateProgress([
            'title' => '  ',
            'description' => null,
        ]);

        self::assertSame(ProductUpdateProgress::DEFAULT_TITLE, $progress->display_title);
        self::assertSame(ProductUpdateProgress::DEFAULT_DESCRIPTION, $progress->display_description);
    }
}
