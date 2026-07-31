<?php

namespace App\Livewire\Admin\Catalog;

use App\Application\Catalog\Actions\ProductMedal\CreateProductMedalAction;
use App\Application\Catalog\Actions\ProductMedal\DeleteProductMedalAction;
use App\Application\Catalog\Actions\ProductMedal\DeleteSelectedProductMedalsAction;
use App\Application\Catalog\Actions\ProductMedal\UpdateProductMedalAction;
use App\Domain\Catalog\Models\ProductMedal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class ManageProductMedals extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedIds = [];

    public string $bulkAction = '';

    public ?int $editingId = null;

    public string $title = '';

    public ?int $pictureId = null;

    public ?string $picturePath = null;

    public string $isActive = '1';

    public ?int $highlightedId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'performDeleteProductMedal' => 'performDeleteProductMedal',
    ];

    public function mount(): void
    {
        Gate::authorize('product.medals');
    }

    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_medals', 'title')
                    ->ignore($this->editingId),
            ],

            'pictureId' => [
                'required',
                'integer',
                Rule::exists('media_files', 'id'),
            ],

            'picturePath' => [
                'nullable',
                'string',
            ],

            'isActive' => [
                'required',
                'boolean',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' =>
                'عنوان مدال الزامی است.',

            'title.max' =>
                'عنوان مدال نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'title.unique' =>
                'مدال دیگری با این عنوان وجود دارد.',

            'pictureId.required' =>
                'انتخاب تصویر مدال الزامی است.',

            'pictureId.integer' =>
                'تصویر انتخاب‌شده معتبر نیست.',

            'pictureId.exists' =>
                'تصویر انتخاب‌شده در رسانه‌ها پیدا نشد.',

            'isActive.required' =>
                'وضعیت مدال را مشخص کنید.',

            'isActive.boolean' =>
                'وضعیت انتخاب‌شده برای مدال معتبر نیست.',
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

        $this->dispatch('product-medals-scroll-to-list');
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        Gate::authorize('product.medals');

        $validated = $this->validate();

        $data = [
            'title' => $validated['title'],
            'picture_id' => (int)$validated['pictureId'],
            'is_active' => (bool)((int)$validated['isActive']),
        ];

        if ($this->isEditing) {
            $this->updateMedal($data);

            return;
        }

        $this->createMedal($data);
    }

    /**
     * @throws Throwable
     */
    protected function createMedal(array $data): void
    {
        $medal = app(CreateProductMedalAction::class)
            ->execute($data);

        $this->highlightedId = $medal->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash(
            'success',
            'مدال محصول با موفقیت ایجاد شد.'
        );
    }

    /**
     * @throws Throwable
     */
    protected function updateMedal(array $data): void
    {
        abort_unless($this->editingId, 404);

        $medal = ProductMedal::query()
            ->findOrFail($this->editingId);

        $medal = app(UpdateProductMedalAction::class)
            ->execute($medal, $data);

        $this->highlightedId = $medal->id;

        $this->resetForm();

        session()->flash(
            'success',
            'مدال محصول با موفقیت به‌روزرسانی شد.'
        );
    }

    public function startEditing(int $id): void
    {
        Gate::authorize('product.medals');

        $medal = ProductMedal::query()
            ->with('picture')
            ->findOrFail($id);

        $this->editingId = $medal->id;
        $this->title = $medal->title;
        $this->pictureId = $medal->picture_id;
        $this->picturePath = $medal->picture?->url;
        $this->isActive = $medal->is_active ? '1' : '0';

        $this->resetValidation();

        $this->dispatch('product-medal-edit-started');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();

        $this->dispatch('product-medals-scroll-to-list');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->pictureId = null;
        $this->picturePath = null;
        $this->isActive = '1';

        $this->resetValidation();
    }

    public function sortByColumn(
        string $column,
        string $direction = 'asc'
    ): void
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

        $this->dispatch('product-medals-scroll-to-list');
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
        Gate::authorize('product.medals');

        if ($this->bulkAction === '' || empty($this->selectedIds)) {
            return;
        }

        if ($this->bulkAction !== 'delete') {
            return;
        }

        $selectedIds = $this->normalizedSelectedIds();

        try {
            $deletedCount = app(
                DeleteSelectedProductMedalsAction::class
            )->execute($selectedIds);
        } catch (ValidationException $exception) {
            $this->setErrorBag(
                $exception->validator->errors()
            );

            session()->flash(
                'error',
                $exception->validator->errors()->first()
            );

            return;
        }

        if (
            $this->editingId !== null
            && in_array($this->editingId, $selectedIds, true)
        ) {
            $this->resetForm();
        }

        $this->clearSelection();
        $this->bulkAction = '';

        session()->flash(
            'success',
            "{$deletedCount} مدال محصول با موفقیت حذف شد."
        );
    }

    /**
     * @throws Throwable
     */
    public function performDeleteProductMedal($id = null): void
    {
        Gate::authorize('product.medals');

        if (is_array($id)) {
            $id = $id['id'] ?? null;
        }

        $id = (int)$id;

        if (!$id) {
            return;
        }

        $medal = ProductMedal::query()
            ->findOrFail($id);

        $medalId = $medal->id;

        try {
            app(DeleteProductMedalAction::class)
                ->execute($medal);
        } catch (ValidationException $exception) {
            $this->setErrorBag(
                $exception->validator->errors()
            );

            session()->flash(
                'error',
                $exception->validator->errors()->first()
            );

            return;
        }

        if ($this->editingId === $medalId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(
            array_diff($this->selectedIds, [$medalId])
        );

        session()->flash(
            'success',
            'مدال محصول با موفقیت حذف شد.'
        );
    }

    public function nextPage($pageName = 'page'): void
    {
        $this->setPage(
            ($this->getPage($pageName) ?? 1) + 1,
            $pageName
        );

        $this->dispatch('product-medals-scroll-to-list');
    }

    public function previousPage($pageName = 'page'): void
    {
        $currentPage = (int)(
            $this->getPage($pageName) ?? 1
        );

        if ($currentPage > 1) {
            $this->setPage(
                $currentPage - 1,
                $pageName
            );
        }

        $this->dispatch('product-medals-scroll-to-list');
    }

    public function gotoPage(
        $page,
        $pageName = 'page'
    ): void
    {
        $this->setPage($page, $pageName);

        $this->dispatch('product-medals-scroll-to-list');
    }

    protected function getMedalsQuery(): Builder
    {
        return ProductMedal::query()
            ->with('picture')
            ->withCount('assignments')
            ->when(
                $this->search !== '',
                function (Builder $query): void {
                    $query->where(
                        'title',
                        'like',
                        '%' . trim($this->search) . '%'
                    );
                }
            );
    }

    protected function sortableColumns(): array
    {
        return [
            'title' => 'title',
            'assignments_count' => 'assignments_count',
            'is_active' => 'is_active',
            'created_at' => 'created_at',
        ];
    }

    protected function safeSortBy(): string
    {
        return array_key_exists(
            $this->sortBy,
            $this->sortableColumns()
        )
            ? $this->sortBy
            : 'created_at';
    }

    protected function safeSortDirection(): string
    {
        return $this->sortDirection === 'asc'
            ? 'asc'
            : 'desc';
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
        $items = $this->getMedalsQuery()
            ->orderBy(
                $this->safeSortBy(),
                $this->safeSortDirection()
            )
            ->paginate(20);

        return view(
            'livewire.admin.catalog.manage-product-medals',
            [
                'items' => $items,
                'bulkActionOptions' =>
                    $this->bulkActionOptions,
            ]
        );
    }
}
