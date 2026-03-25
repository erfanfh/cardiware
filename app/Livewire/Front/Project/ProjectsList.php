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
    public function activeFilter(): void
    {
        $this->filter = 'active';
        $this->resetPage();
    }

    #[NoReturn]
    public function inactiveFilter(): void
    {
        $this->filter = 'inactive';
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

        if ($this->filter == 'active')
            $projects->where('status', ProjectStatusEnum::ACTIVE);
        elseif ($this->filter == 'inactive')
            $projects->where('status', ProjectStatusEnum::INACTIVE);

        $projects = $projects->paginate(10);

        return view('livewire.front.project.projects-list', compact('projects'));
    }
}
