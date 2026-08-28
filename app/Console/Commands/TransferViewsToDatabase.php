<?php

namespace App\Console\Commands;

use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Project\Models\Project;
use App\Domain\System\Models\PageView;
use App\Domain\System\Models\PageViewDetail;
use App\Domain\Video\Models\Video;
use App\Infrastructure\Support\Counters\BufferedCounter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferViewsToDatabase extends Command
{
    protected $signature = 'app:transfer-views-to-database {--limit=5000}';

    protected $description = 'Transfer buffered page views from Redis to database';

    public function handle(): int
    {
        $map = [
            'Post' => Post::class,
            'Page' => Page::class,
            'Project' => Project::class,
            'Product' => Product::class,
            'Store' => Store::class,
            'Video' => Video::class,
        ];

        $limit = (int)$this->option('limit');

        for ($i = 0; $i < $limit; $i++) {
            $dirty = BufferedCounter::popDirty('page_views');

            if (!$dirty) {
                break;
            }

            [$type, $id] = BufferedCounter::parseDirtyValue($dirty);

            $modelClass = $map[$type] ?? null;

            if (!$modelClass || !$id) {
                continue;
            }

            $count = BufferedCounter::pull(
                namespace: 'page_views',
                type: $type,
                id: $id,
            );

            if ($count <= 0) {
                continue;
            }

            DB::transaction(function () use ($modelClass, $id, $count) {
                $pageView = PageView::query()->firstOrCreate(
                    [
                        'pageable_type' => $modelClass,
                        'pageable_id' => $id,
                    ],
                    [
                        'count' => 0,
                    ]
                );

                $pageView->increment('count', $count);

                $pageViewDetail = PageViewDetail::query()->firstOrCreate(
                    [
                        'pageable_type' => $modelClass,
                        'pageable_id' => $id,
                        'date' => today()->toDateString(),
                    ],
                    [
                        'count' => 0,
                    ]
                );

                $pageViewDetail->increment('count', $count);
            });
        }

        return self::SUCCESS;
    }
}
