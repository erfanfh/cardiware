<?php

namespace App\Http\Controllers\Admin\Raffle;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Admin\AdminSearchController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DrawCampaignUserSearchController extends AdminSearchController
{
    protected function modelClass(): string { return User::class; }
    protected function permissionName(): string { return 'raffle-ticket.issue'; }
    protected function selectColumns(): array { return ['id','email','mobile','display_name','first_name','last_name']; }
    protected function applySearchConditions(Builder $builder, string $query): void
    {
        $builder->where('email','like',"%{$query}%")
            ->orWhere('mobile','like',"%{$query}%")
            ->orWhere('display_name','like',"%{$query}%")
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
        if (ctype_digit($query)) $builder->orWhere('id', (int)$query);
    }
    protected function orderByColumn(): string { return 'mobile'; }
    protected function formatLabel(Model $model): string
    {
        $name = $model->display_name ?: trim($model->first_name.' '.$model->last_name);
        return $name.' ('.($model->mobile ?: 'بدون موبایل').' | '.($model->email ?: 'بدون ایمیل').')';
    }
}
