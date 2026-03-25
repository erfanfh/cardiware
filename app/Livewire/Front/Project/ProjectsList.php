<?php

namespace App\Livewire\Front\Project;

use App\Application\Identity\Actions\Auth\GenerateOtpAction;
use App\Application\Identity\Actions\Auth\RegisterUserAction;
use App\Application\System\RateLimiterAction;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
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

    public string $search = '';

    public string $sort = 'most-progress';

    public string $filter = 'all';

    public function mount(): void
    {
        $this->sort = request('sort') ?? 'most-progress';
    }

    #[NoReturn]
    public function updatedSort(): void
    {
        $this->resetPage();
    }

    #[NoReturn]
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[NoReturn]
    public function noFilter(): void
    {
        $this->filter = 'all';
        $this->resetPage();
    }

    #[NoReturn]
    public function notStartedFilter(): void
    {
        $this->filter = 'not-started';
        $this->resetPage();
    }

    #[NoReturn]
    public function inprogressFilter(): void
    {
        $this->filter = 'inprogress';
        $this->resetPage();
    }

    #[NoReturn]
    public function completedFilter(): void
    {
        $this->filter = 'completed';
        $this->resetPage();
    }

    /**
     * @return View
     */
    public function render(): View
    {
        $projects = Project::query();

        if (!empty($this->search)) {
            $projects->where('title', 'like', '%' . $this->search . '%');
        }

        if ($this->sort == 'most-progress')
            $projects->orderBy('progress_percent', 'desc');
        elseif ($this->sort == 'least-progress')
            $projects->orderBy('progress_percent', 'asc');
        elseif ($this->sort == 'later-update')
            $projects->orderBy('last_updated_at', 'desc');
        elseif ($this->sort == 'older-update')
            $projects->orderBy('last_updated_at', 'asc');

        if ($this->filter == 'not-started')
            $projects->where('progress_percent', 0);
        elseif ($this->filter == 'inprogress')
            $projects->whereBetween('progress_percent', [1, 99]);
        elseif ($this->filter == 'completed')
            $projects->where('progress_percent', 100);

        $projects = $projects->paginate(10);

        return view('livewire.front.project.projects-list', compact('projects'));
    }
}
