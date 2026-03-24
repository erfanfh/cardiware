<?php

namespace App\Console\Commands;

use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\System\Models\PageView;
use App\Domain\System\Models\PageViewDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TransferViewsToDatabase extends Command
{
    protected $signature = 'app:transfer-views-to-database';
    protected $description = 'Transfer page views from cache to database in batches';

    public function handle(): void
    {
        $this->info('Starting transfer of page views...');

        $models = [
            Post::class,
            Page::class,
        ];

        $batchSize = config('cache.page_views_batch_size');

        foreach ($models as $model) {
            $className = class_basename($model);
            $this->info("Processing $className views...");

            $model::chunk($batchSize, function ($items) use ($className, $model) {
                foreach ($items as $item) {
                    $cacheKey = "temp_views:$className:$item->id";
                    $cache = Cache::pull($cacheKey);
                    if (!$cache) {
                        continue;
                    }

                    $pageView = PageView::firstOrNew([
                        'pageable_type' => $model,
                        'pageable_id' => $item->id,
                    ]);
                    $pageView->count = ($pageView->count ?? 0) + $cache;
                    $pageView->save();

                    $pageViewDetail = PageViewDetail::firstOrNew([
                        'pageable_type' => $model,
                        'pageable_id' => $item->id,
                        'date' => today(),
                    ]);
                    $pageViewDetail->count = ($pageViewDetail->count ?? 0) + $cache;
                    $pageViewDetail->save();
                }
            });

            $this->info("Finished processing ✅");
        }

        $this->info('All views transferred successfully!');
    }
}
