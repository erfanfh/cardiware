<?php

namespace App\Livewire\Front\Account\Financial;

use App\Application\Financial\Actions\Activity\GetUserFinancialActivitiesAction;
use App\Domain\Financial\Enums\FinancialActivityDirectionEnum;
use App\Domain\Financial\Enums\FinancialActivityStatusEnum;
use App\Domain\Financial\Enums\FinancialActivityTypeEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class FinancialActivityList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $direction = '';

    #[Url(as: 'per_page', except: 10)]
    public int $perPage = 10;

    /**
     * Reset pagination after changing the activity type.
     */
    public function updatedType(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination after changing the activity status.
     */
    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination after changing the activity direction.
     */
    public function updatedDirection(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination after changing the page size.
     */
    public function updatedPerPage(): void
    {
        $this->perPage = $this->normalizedPerPage();

        $this->resetPage();
    }

    /**
     * Remove all selected filters.
     */
    public function resetFilters(): void
    {
        $this->reset([
            'type',
            'status',
            'direction',
        ]);

        $this->resetPage();
    }

    /**
     * Determine whether at least one filter is active.
     */
    public function hasActiveFilters(): bool
    {
        return $this->type !== ''
            || $this->status !== ''
            || $this->direction !== '';
    }

    public function render(
        GetUserFinancialActivitiesAction $getUserFinancialActivitiesAction,
    ): View
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        /*
         * Invalid values received from the URL or browser are ignored.
         * The query only receives valid enum instances.
         */
        $type = FinancialActivityTypeEnum::tryFrom(
            $this->type,
        );

        $status = FinancialActivityStatusEnum::tryFrom(
            $this->status,
        );

        $direction = FinancialActivityDirectionEnum::tryFrom(
            $this->direction,
        );

        $perPage = $this->normalizedPerPage();

        $activities = $getUserFinancialActivitiesAction->execute(
            user: $user,
            type: $type,
            status: $status,
            direction: $direction,
            perPage: $perPage,
            page: Paginator::resolveCurrentPage('page'),
        );

        return view(
            'livewire.front.account.financial.financial-activity-list',
            [
                'activities' => $activities,

                'typeOptions' =>
                    FinancialActivityTypeEnum::cases(),

                'statusOptions' =>
                    FinancialActivityStatusEnum::cases(),

                'directionOptions' =>
                    FinancialActivityDirectionEnum::cases(),

                'perPageOptions' => [
                    10,
                    20,
                    30,
                ],

                'hasActiveFilters' =>
                    $this->hasActiveFilters(),
            ],
        );
    }

    private function normalizedPerPage(): int
    {
        return in_array(
            $this->perPage,
            [
                10,
                20,
                30,
            ],
            true,
        )
            ? $this->perPage
            : 10;
    }
}
