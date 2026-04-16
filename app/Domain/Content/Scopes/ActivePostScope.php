<?php

namespace App\Domain\Content\Scopes;

use App\Domain\Content\Enums\PostStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActivePostScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (request()->routeIs(['admin.*', 'livewire.*']))
            return;
        else
            $builder->where('status', PostStatusEnum::ACTIVE);
    }
}
