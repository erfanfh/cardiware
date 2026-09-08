<?php

namespace App\Http\Controllers\Admin\System;

use App\Domain\Organization\Models\TeamMember;
use App\Http\Controllers\Admin\Organization\TeamMemberSearchController;
use Illuminate\Database\Eloquent\Model;

class TeamSettingMemberSearchController extends TeamMemberSearchController
{
    protected function permissionName(): string
    {
        return 'setting.team';
    }

    /** @return array<int, string> */
    protected function selectColumns(): array
    {
        return [
            'id',
            'email',
            'mobile',
            'first_name',
            'last_name',
            'job_title',
        ];
    }

    protected function formatLabel(Model $model): string
    {
        /** @var TeamMember $model */
        $detail = $model->job_title ?: ($model->email ?: $model->mobile);

        return $model->full_name.($detail ? " — {$detail}" : '');
    }
}
