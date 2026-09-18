<?php

namespace App\Domain\Video\Scopes;

use App\Domain\Video\Enums\VideoStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveVideoScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (request()->routeIs(['admin.*', 'livewire.*'])) {
            return;
        }

        $builder
            ->where('status', VideoStatusEnum::ACTIVE)
            ->where('published_at', '<=', now());
    }
}
