<?php

namespace App\Http\Controllers\Admin\Organization;

use App\Domain\Organization\Models\TeamMember;
use App\Http\Controllers\Admin\AdminSearchController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TeamMemberSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return TeamMember::class;
    }

    protected function permissionName(): string
    {
        return 'team-member.list';
    }

    protected function orderByColumn(): string
    {
        return 'last_name';
    }

    /**
     * @return array<int, string>
     */
    protected function selectColumns(): array
    {
        return [
            'id',
            'email',
            'mobile',
            'first_name',
            'last_name',
        ];
    }

    protected function applySearchConditions(Builder $builder, string $query): void
    {
        $builder
            ->where('email', 'like', "%{$query}%")
            ->orWhere('mobile', 'like', "%{$query}%")
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"])
            ->orWhereRaw("CONCAT(first_name, last_name) LIKE ?", ["%{$query}%"]);

        if (ctype_digit($query)) {
            $builder->orWhere('id', (int)$query);
        }
    }

    protected function formatLabel(Model $model): string
    {
        /** @var TeamMember $model */

        return "$model->first_name $model->last_name ($model->mobile)";
    }
}
