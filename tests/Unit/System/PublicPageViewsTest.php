<?php

namespace Tests\Unit\System;

use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Project\Models\Project;
use App\Domain\Video\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class PublicPageViewsTest extends TestCase
{
    public function test_public_count_adds_offset_without_changing_real_count(): void
    {
        Cache::shouldReceive('tags')->times(8)->andReturnSelf();
        Cache::shouldReceive('remember')->times(8)->andReturn(125);
        Redis::shouldReceive('get')->times(8)->andReturn('7');

        $models = [
            new Product(['display_views_offset' => 1000]),
            new Project(['display_views_offset' => 1000]),
            new Video(['display_views_offset' => 1000]),
            new Post(['display_views_offset' => 1000]),
        ];

        foreach ($models as $index => $model) {
            $model->id = $index + 1;

            self::assertSame(132, page_views($model));
            self::assertSame(1132, public_page_views($model));
        }
    }

    public function test_public_count_never_applies_a_negative_offset(): void
    {
        Cache::shouldReceive('tags')->once()->andReturnSelf();
        Cache::shouldReceive('remember')->once()->andReturn(125);
        Redis::shouldReceive('get')->once()->andReturn('7');

        $product = new Product(['display_views_offset' => -1000]);
        $product->id = 1;

        self::assertSame(132, public_page_views($product));
    }
}
