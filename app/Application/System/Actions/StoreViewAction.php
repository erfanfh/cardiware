<?php

namespace App\Application\System\Actions;

use App\Domain\System\Interfaces\PageViewableInterface;
use App\Infrastructure\Support\Counters\BufferedCounter;

class StoreViewAction
{
    /**
     * @param PageViewableInterface $model
     * @return void
     */
    public function execute(PageViewableInterface $model): void
    {
        BufferedCounter::increment(
            namespace: 'page_views',
            type: class_basename($model),
            id: $model->id,
        );
    }
}
