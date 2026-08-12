<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Admin\AdminSearchController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class LauncherDeviceUserSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return User::class;
    }

    protected function permissionName(): string
    {
        return 'launcher-device.manage';
    }

    protected function selectColumns(): array
    {
        return ['id', 'email', 'mobile', 'display_name', 'first_name', 'last_name'];
    }

    protected function applySearchConditions(Builder $builder, string $query): void
    {
        $builder
            ->where('email', 'like', "%{$query}%")
            ->orWhere('mobile', 'like', "%{$query}%")
            ->orWhere('display_name', 'like', "%{$query}%")
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);

        if (ctype_digit($query)) {
            $builder->orWhere('id', (int)$query);
        }
    }

    protected function orderByColumn(): string
    {
        return 'mobile';
    }

    protected function formatLabel(Model $model): string
    {
        /** @var User $model */
        $displayName = $model->display_name ?: trim($model->first_name . ' ' . $model->last_name);
        $email = $model->email ?: 'بدون ایمیل';

        return "{$displayName} ({$model->mobile} | {$email})";
    }
}
