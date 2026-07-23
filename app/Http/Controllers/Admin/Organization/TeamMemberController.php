<?php

namespace App\Http\Controllers\Admin\Organization;

use App\Application\Organization\Actions\TeamMember\BulkDeleteTeamMembersAction;
use App\Application\Organization\Actions\TeamMember\CreateTeamMemberAction;
use App\Application\Organization\Actions\TeamMember\DeleteTeamMemberAction;
use App\Application\Organization\Actions\TeamMember\UpdateTeamMemberAction;
use App\Application\Organization\Listings\TeamMemberListing;
use App\Domain\Organization\Models\TeamMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Organization\BulkActionTeamMemberRequest;
use App\Http\Requests\Admin\Organization\IndexTeamMemberRequest;
use App\Http\Requests\Admin\Organization\SaveTeamMemberRequest;
use App\Infrastructure\Support\BulkActions\BulkActionDefinition;
use App\Infrastructure\Support\BulkActions\BulkActionSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(
        IndexTeamMemberRequest $request,
        TeamMemberListing      $teamMemberListing
    ): View
    {
        Gate::authorize('team-member.index');

        $validated = $request->validated();

        $teamMembers = $teamMemberListing
            ->handle(
                TeamMember::query()->with('picture'),
                $validated
            )
            ->paginate($teamMemberListing->perPage())
            ->withQueryString();

        $hasActiveFilters = $teamMemberListing->hasActiveFilters(
            $validated
        );

        $bulkActionOptions = BulkActionSet::make([
            'delete' => new BulkActionDefinition(
                value: 'delete',
                label: 'حذف',
                destructive: true,
                requiresConfirmation: true,
            ),
        ])->toOptions();

        return view(
            'admin.organization.team-members.index',
            compact(
                'teamMembers',
                'hasActiveFilters',
                'bulkActionOptions',
            )
        );
    }

    public function create(): View
    {
        Gate::authorize('team-member.create');

        return view(
            'admin.organization.team-members.create',
            [
                'teamMember' => null,
                'selectedPicture' => [],
            ]
        );
    }

    public function store(
        SaveTeamMemberRequest $request
    ): RedirectResponse
    {
        Gate::authorize('team-member.create');

        $teamMember = app(CreateTeamMemberAction::class)->execute(
            $request->validated()
        );

        return redirect()
            ->route('admin.team-members.edit', $teamMember)
            ->with(
                'success',
                'عضو تیم با موفقیت ایجاد شد.'
            );
    }

    public function edit(TeamMember $teamMember): View
    {
        Gate::authorize('team-member.edit');

        $teamMember->load('picture');

        return view(
            'admin.organization.team-members.edit',
            [
                'teamMember' => $teamMember,
                'selectedPicture' => $this->selectedPicture(
                    $teamMember
                ),
            ]
        );
    }

    public function update(
        SaveTeamMemberRequest $request,
        TeamMember            $teamMember
    ): RedirectResponse
    {
        Gate::authorize('team-member.edit');

        $teamMember = app(UpdateTeamMemberAction::class)->execute(
            teamMember: $teamMember,
            data: $request->validated()
        );

        return redirect()
            ->route('admin.team-members.edit', $teamMember)
            ->with(
                'success',
                'اطلاعات عضو تیم با موفقیت بروزرسانی شد.'
            );
    }

    public function destroy(
        TeamMember $teamMember
    ): RedirectResponse
    {
        Gate::authorize('team-member.delete');

        app(DeleteTeamMemberAction::class)->execute(
            $teamMember
        );

        return redirect()
            ->route('admin.team-members.index')
            ->with(
                'success',
                'عضو تیم با موفقیت حذف شد.'
            );
    }

    public function bulkAction(
        BulkActionTeamMemberRequest $request
    ): RedirectResponse
    {
        $data = $request->validated();

        $deletedCount = app(
            BulkDeleteTeamMembersAction::class
        )->execute($data['ids']);

        return redirect()
            ->route('admin.team-members.index')
            ->with(
                'success',
                "{$deletedCount} عضو تیم با موفقیت حذف شد."
            );
    }

    private function selectedPicture(
        TeamMember $teamMember
    ): array
    {
        $picture = $teamMember->picture;

        if (!$picture) {
            return [];
        }

        $label = $picture->title
            ?? $picture->original_name
            ?? $picture->file_name
            ?? "رسانه شماره {$picture->getKey()}";

        return [
            $picture->getKey() => $label,
        ];
    }
}
