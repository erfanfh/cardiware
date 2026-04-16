<?php

namespace App\Domain\Project\Scopes;

use App\Domain\Project\Enums\ProjectStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveProjectScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (request()->routeIs(['admin.*', 'livewire.*']))
            return;
        else
            $builder->where('status', ProjectStatusEnum::ACTIVE);
    }
}
