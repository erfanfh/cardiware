<?php

namespace App\Application\System\Actions;

use App\Domain\System\Interfaces\PageViewableInterface;
use Illuminate\Support\Facades\Cache;

class StoreViewAction
{
    /**
     * @param PageViewableInterface $model
     * @return void
     */
    public function execute(PageViewableInterface $model): void
    {
        $modelClass = class_basename($model);

        if (Cache::has("temp_views:$modelClass:$model->id"))
            Cache::increment("temp_views:$modelClass:$model->id");
        else
            Cache::add("temp_views:$modelClass:$model->id", 1);
    }
}
