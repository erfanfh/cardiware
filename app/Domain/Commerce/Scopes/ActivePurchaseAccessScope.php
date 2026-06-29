<?php

namespace App\Domain\Commerce\Scopes;

use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActivePurchaseAccessScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (request()->routeIs(['admin.*', 'livewire.*']))
            return;
        else
            $builder
                ->where('status', PurchaseAccessStatusEnum::ACTIVE);
    }
}
