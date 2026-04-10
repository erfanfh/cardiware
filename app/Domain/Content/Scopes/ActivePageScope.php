<?php

namespace App\Domain\Content\Scopes;

use App\Domain\Content\Enums\PageStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActivePageScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (request()->routeIs('admin.*'))
            return;
        else
            $builder->where('status', PageStatusEnum::ACTIVE);
    }
}
