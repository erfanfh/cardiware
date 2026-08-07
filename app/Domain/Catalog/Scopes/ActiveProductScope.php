<?php

namespace App\Domain\Catalog\Scopes;

use App\Domain\Catalog\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Gate;

class ActiveProductScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (request()->routeIs(['admin.*', 'livewire.*'])) {
            return;
        }

        if (Gate::allows('product.edit')) {
            return;
        }

        $builder
            ->where('status', ProductStatusEnum::ACTIVE)
            ->where('published_at', '<=', now());
    }
}
