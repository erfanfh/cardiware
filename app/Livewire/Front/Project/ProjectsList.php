<?php

namespace App\Livewire\Front\Project;

use App\Application\Identity\Actions\Auth\GenerateOtpAction;
use App\Application\Identity\Actions\Auth\RegisterUserAction;
use App\Application\System\RateLimiterAction;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
use App\Domain\Project\Enums\ProjectFilterEnum;
use App\Domain\Project\Enums\ProjectSortEnum;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use JetBrains\PhpStorm\NoReturn;
use Livewire\Component;
use Livewire\WithPagination;
use Random\RandomException;


class ProjectsList extends Component
{
    use WithPagination;

    public string $sort = ProjectSortEnum::MOST_PROGRESS->value;

    public string $filter = ProjectFilterEnum::ALL->value;

    public function mount(): void
    {
        $this->sort = request('sort') ?? ProjectSortEnum::MOST_PROGRESS->value;
    }

    #[NoReturn]
    public function updatedSort(): void
    {
        $this->resetPage();
    }

    #[NoReturn]
    public function noFilter(): void
    {
        $this->filter = ProjectFilterEnum::ALL->value;
        $this->resetPage();
    }

    #[NoReturn]
    public function notStartedFilter(): void
    {
        $this->filter = ProjectFilterEnum::NOT_STARTED->value;
        $this->resetPage();
    }

    #[NoReturn]
    public function inprogressFilter(): void
    {
        $this->filter = ProjectFilterEnum::INPROGRESS->value;
        $this->resetPage();
    }

    #[NoReturn]
    public function completedFilter(): void
    {
        $this->filter = ProjectFilterEnum::COMPLETED->value;
        $this->resetPage();
    }

    /**
     * @return View
     */
    public function render(): View
    {
        $projects = Project::query()->with('tags');

        if ($this->sort == ProjectSortEnum::MOST_PROGRESS->value)
            $projects->orderBy('progress_percent', 'desc');
        elseif ($this->sort == ProjectSortEnum::LEAST_PROGRESS->value)
            $projects->orderBy('progress_percent', 'asc');
        elseif ($this->sort == ProjectSortEnum::LATER_UPDATE->value)
            $projects->orderBy('last_updated_at', 'desc');
        elseif ($this->sort == ProjectSortEnum::OLDER_UPDATE->value)
            $projects->orderBy('last_updated_at', 'asc');

        if ($this->filter == ProjectFilterEnum::NOT_STARTED->value)
            $projects->where('progress_percent', 0);
        elseif ($this->filter == ProjectFilterEnum::INPROGRESS->value)
            $projects->whereBetween('progress_percent', [1, 99]);
        elseif ($this->filter == ProjectFilterEnum::COMPLETED->value)
            $projects->where('progress_percent', 100);

        $projects = $projects->paginate(10);

        return view('livewire.front.project.projects-list', compact('projects'));
    }
}
