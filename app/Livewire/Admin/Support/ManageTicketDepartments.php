<?php

namespace App\Livewire\Admin\Support;

use App\Application\Support\Actions\TicketDepartment\CreateTicketDepartmentAction;
use App\Application\Support\Actions\TicketDepartment\DeleteSelectedTicketDepartmentsAction;
use App\Application\Support\Actions\TicketDepartment\DeleteTicketDepartmentAction;
use App\Application\Support\Actions\TicketDepartment\UpdateTicketDepartmentAction;
use App\Domain\Support\Models\TicketDepartment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class ManageTicketDepartments extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedIds = [];

    public string $bulkAction = '';

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public bool $requiresProduct = false;

    public bool $isActive = true;

    public int $sortOrder = 0;

    public ?int $highlightedId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'performDeleteTicketDepartment' => 'performDeleteTicketDepartment',
    ];

    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:120',
                Rule::unique('ticket_departments', 'title')
                    ->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'requiresProduct' => ['boolean'],
            'isActive' => ['boolean'],
            'sortOrder' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'عنوان دپارتمان الزامی است.',
            'title.max' => 'عنوان دپارتمان نباید بیشتر از ۱۲۰ کاراکتر باشد.',
            'title.unique' => 'دپارتمان دیگری با این عنوان وجود دارد.',

            'description.max' => 'توضیحات دپارتمان نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',

            'sortOrder.required' => 'ترتیب نمایش الزامی است.',
            'sortOrder.integer' => 'ترتیب نمایش باید عدد معتبر باشد.',
            'sortOrder.min' => 'ترتیب نمایش نمی‌تواند کمتر از صفر باشد.',
            'sortOrder.max' => 'ترتیب نمایش بیش از حد مجاز است.',
        ];
    }

    public function getIsEditingProperty(): bool
    {
        return filled($this->editingId);
    }

    public function getBulkActionOptionsProperty(): array
    {
        return [
            '' => 'انتخاب عملیات',
            'delete' => 'حذف گروهی',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();

        $this->dispatch('ticket-departments-scroll-to-list');
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        Gate::authorize('ticket.manage-departments');

        $data = $this->validate();

        $payload = [
            'title' => $data['title'],
            'description' => filled($data['description']) ? $data['description'] : null,
            'requires_product' => (bool)$data['requiresProduct'],
            'is_active' => (bool)$data['isActive'],
            'sort_order' => (int)$data['sortOrder'],
        ];

        if ($this->isEditing) {
            $this->updateDepartment($payload);

            return;
        }

        $this->createDepartment($payload);
    }

    /**
     * @throws Throwable
     */
    protected function createDepartment(array $data): void
    {
        $department = app(CreateTicketDepartmentAction::class)->execute($data);

        $this->highlightedId = $department->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'دپارتمان پشتیبانی با موفقیت ایجاد شد.');
    }

    /**
     * @throws Throwable
     */
    protected function updateDepartment(array $data): void
    {
        abort_unless($this->editingId, 404);

        $department = TicketDepartment::query()->findOrFail($this->editingId);

        $department = app(UpdateTicketDepartmentAction::class)->execute($department, $data);

        $this->highlightedId = $department->id;

        $this->resetForm();

        session()->flash('success', 'دپارتمان پشتیبانی با موفقیت به‌روزرسانی شد.');
    }

    public function startEditing(int $id): void
    {
        Gate::authorize('ticket.manage-departments');

        $department = TicketDepartment::query()->findOrFail($id);

        $this->editingId = $department->id;
        $this->title = $department->title;
        $this->description = (string)$department->description;
        $this->requiresProduct = (bool)$department->requires_product;
        $this->isActive = (bool)$department->is_active;
        $this->sortOrder = (int)$department->sort_order;

        $this->resetValidation();

        $this->dispatch('ticket-department-edit-started');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();

        $this->dispatch('ticket-departments-scroll-to-list');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->requiresProduct = false;
        $this->isActive = true;
        $this->sortOrder = 0;

        $this->resetValidation();
    }

    public function sortByColumn(string $column, string $direction = 'asc'): void
    {
        if (!array_key_exists($column, $this->sortableColumns())) {
            return;
        }

        if ($this->sortBy === $column) {
            if ($this->sortDirection === $direction) {
                $this->sortBy = 'created_at';
                $this->sortDirection = 'desc';
            } else {
                $this->sortDirection = $direction;
            }
        } else {
            $this->sortBy = $column;
            $this->sortDirection = $direction;
        }

        $this->resetPage();
        $this->clearSelection();

        $this->dispatch('ticket-departments-scroll-to-list');
    }

    public function clearSelection(): void
    {
        $this->selectedIds = [];
    }

    /**
     * @throws Throwable
     */
    public function applyBulkAction(): void
    {
        Gate::authorize('ticket.manage-departments');

        if ($this->bulkAction === '' || empty($this->selectedIds)) {
            return;
        }

        if ($this->bulkAction !== 'delete') {
            return;
        }

        $selectedIds = $this->normalizedSelectedIds();
        $selectedBulkAction = $this->bulkAction;

        try {
            $deletedCount = app(DeleteSelectedTicketDepartmentsAction::class)->execute($selectedIds);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            session()->flash('error', $exception->validator->errors()->first());

            return;
        }

        if (
            $this->editingId !== null &&
            in_array($this->editingId, $selectedIds, true) &&
            $selectedBulkAction === 'delete'
        ) {
            $this->resetForm();
        }

        $this->clearSelection();
        $this->bulkAction = '';

        session()->flash('success', "{$deletedCount} دپارتمان پشتیبانی با موفقیت حذف شد.");
    }

    /**
     * @throws Throwable
     */
    public function performDeleteTicketDepartment($id = null): void
    {
        Gate::authorize('ticket.manage-departments');

        if (is_array($id)) {
            $id = $id['id'] ?? null;
        }

        $id = (int)$id;

        if (!$id) {
            return;
        }

        $department = TicketDepartment::query()->findOrFail($id);
        $departmentId = $department->id;

        try {
            app(DeleteTicketDepartmentAction::class)->execute($department);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            session()->flash('error', $exception->validator->errors()->first());

            return;
        }

        if ($this->editingId === $departmentId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$departmentId]));

        session()->flash('success', 'دپارتمان پشتیبانی با موفقیت حذف شد.');
    }

    public function nextPage($pageName = 'page'): void
    {
        $this->setPage(($this->getPage($pageName) ?? 1) + 1, $pageName);
        $this->dispatch('ticket-departments-scroll-to-list');
    }

    public function previousPage($pageName = 'page'): void
    {
        $currentPage = (int)($this->getPage($pageName) ?? 1);

        if ($currentPage > 1) {
            $this->setPage($currentPage - 1, $pageName);
        }

        $this->dispatch('ticket-departments-scroll-to-list');
    }

    public function gotoPage($page, $pageName = 'page'): void
    {
        $this->setPage($page, $pageName);
        $this->dispatch('ticket-departments-scroll-to-list');
    }

    protected function getDepartmentsQuery(): Builder
    {
        return TicketDepartment::query()
            ->withCount(['tickets'])
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                });
            });
    }

    protected function sortableColumns(): array
    {
        return [
            'title' => 'title',
            'requires_product' => 'requires_product',
            'is_active' => 'is_active',
            'tickets_count' => 'tickets_count',
            'sort_order' => 'sort_order',
            'created_at' => 'created_at',
        ];
    }

    protected function safeSortBy(): string
    {
        return array_key_exists($this->sortBy, $this->sortableColumns())
            ? $this->sortBy
            : 'created_at';
    }

    protected function safeSortDirection(): string
    {
        return $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }

    protected function normalizedSelectedIds(): array
    {
        return collect($this->selectedIds)
            ->map(fn($id) => (int)$id)
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function render(): View
    {
        $items = $this->getDepartmentsQuery()
            ->orderBy($this->safeSortBy(), $this->safeSortDirection())
            ->paginate(20);

        return view('livewire.admin.support.manage-ticket-departments', [
            'items' => $items,
            'bulkActionOptions' => $this->bulkActionOptions,
        ]);
    }
}
