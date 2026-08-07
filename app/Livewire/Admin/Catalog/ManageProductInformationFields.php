<?php

namespace App\Livewire\Admin\Catalog;

use App\Application\Catalog\Actions\ProductInformationField\CreateProductInformationFieldAction;
use App\Application\Catalog\Actions\ProductInformationField\DeleteProductInformationFieldAction;
use App\Application\Catalog\Actions\ProductInformationField\DeleteSelectedProductInformationFieldsAction;
use App\Application\Catalog\Actions\ProductInformationField\UpdateProductInformationFieldAction;
use App\Domain\Catalog\Models\ProductInformationField;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class ManageProductInformationFields extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedIds = [];

    public string $bulkAction = '';

    public ?int $editingId = null;

    public string $title = '';

    public ?int $iconId = null;

    public string $icon = '';

    public ?int $highlightedId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'performDeleteProductInformationField' => 'performDeleteProductInformationField',
    ];

    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_information_fields', 'title')
                    ->ignore($this->editingId),
            ],
            'iconId' => [
                'nullable',
                'integer',
                Rule::exists('icons', 'id'),
            ],
            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'عنوان فیلد اطلاعات محصول الزامی است.',
            'title.max' => 'عنوان فیلد اطلاعات محصول نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'title.unique' => 'فیلد اطلاعات محصول دیگری با این عنوان وجود دارد.',
            'iconId.exists' => 'آیکون انتخاب شده معتبر نیست.',
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

        $this->dispatch('product-information-fields-scroll-to-list');
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        Gate::authorize('product.information');

        $data = $this->validate();

        if ($this->isEditing) {
            $this->updateField($data);

            return;
        }

        $this->createField($data);
    }

    /**
     * @throws Throwable
     */
    protected function createField(array $data): void
    {
        $field = app(CreateProductInformationFieldAction::class)->execute($data);

        $this->highlightedId = $field->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'فیلد اطلاعات محصول با موفقیت ایجاد شد.');
    }

    /**
     * @throws Throwable
     */
    protected function updateField(array $data): void
    {
        abort_unless($this->editingId, 404);

        $field = ProductInformationField::query()->findOrFail($this->editingId);

        $field = app(UpdateProductInformationFieldAction::class)->execute($field, $data);

        $this->highlightedId = $field->id;

        $this->resetForm();

        session()->flash('success', 'فیلد اطلاعات محصول با موفقیت به‌روزرسانی شد.');
    }

    public function startEditing(int $id): void
    {
        Gate::authorize('product.information');

        $field = ProductInformationField::query()
            ->with('icon:id,full_name')
            ->findOrFail($id);

        $this->editingId = $field->id;
        $this->title = $field->title;
        $this->iconId = $field->icon_id;
        $this->icon = $field->icon?->full_name ?? '';

        $this->resetValidation();

        $this->dispatch('product-information-field-edit-started');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();

        $this->dispatch('product-information-fields-scroll-to-list');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->iconId = null;
        $this->icon = '';

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

        $this->dispatch('product-information-fields-scroll-to-list');
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
        Gate::authorize('product.information');

        if ($this->bulkAction === '' || empty($this->selectedIds)) {
            return;
        }

        if ($this->bulkAction !== 'delete') {
            return;
        }

        $selectedIds = $this->normalizedSelectedIds();
        $selectedBulkAction = $this->bulkAction;

        try {
            $deletedCount = app(DeleteSelectedProductInformationFieldsAction::class)->execute($selectedIds);
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

        session()->flash('success', "{$deletedCount} فیلد اطلاعات محصول با موفقیت حذف شد.");
    }

    /**
     * @throws Throwable
     */
    public function performDeleteProductInformationField($id = null): void
    {
        Gate::authorize('product.information');

        if (is_array($id)) {
            $id = $id['id'] ?? null;
        }

        $id = (int)$id;

        if (!$id) {
            return;
        }

        $field = ProductInformationField::query()->findOrFail($id);
        $fieldId = $field->id;

        try {
            app(DeleteProductInformationFieldAction::class)->execute($field);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            session()->flash('error', $exception->validator->errors()->first());

            return;
        }

        if ($this->editingId === $fieldId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$fieldId]));

        session()->flash('success', 'فیلد اطلاعات محصول با موفقیت حذف شد.');
    }

    public function nextPage($pageName = 'page'): void
    {
        $this->setPage(($this->getPage($pageName) ?? 1) + 1, $pageName);
        $this->dispatch('product-information-fields-scroll-to-list');
    }

    public function previousPage($pageName = 'page'): void
    {
        $currentPage = (int)($this->getPage($pageName) ?? 1);

        if ($currentPage > 1) {
            $this->setPage($currentPage - 1, $pageName);
        }

        $this->dispatch('product-information-fields-scroll-to-list');
    }

    public function gotoPage($page, $pageName = 'page'): void
    {
        $this->setPage($page, $pageName);
        $this->dispatch('product-information-fields-scroll-to-list');
    }

    protected function getFieldsQuery(): Builder
    {
        return ProductInformationField::query()
            ->with(['icon:id,full_name,svg'])
            ->withCount(['entries'])
            ->when($this->search !== '', function (Builder $query): void {
                $query->where('title', 'like', '%' . trim($this->search) . '%');
            });
    }

    protected function sortableColumns(): array
    {
        return [
            'title' => 'title',
            'entries_count' => 'entries_count',
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
        $items = $this->getFieldsQuery()
            ->orderBy($this->safeSortBy(), $this->safeSortDirection())
            ->paginate(20);

        return view('livewire.admin.catalog.manage-product-information-fields', [
            'items' => $items,
            'bulkActionOptions' => $this->bulkActionOptions,
        ]);
    }
}
