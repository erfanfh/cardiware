<?php

namespace App\Livewire\Admin\Catalog;

use App\Application\Catalog\Actions\ProductAttribute\CreateProductAttributeAction;
use App\Application\Catalog\Actions\ProductAttribute\CreateProductAttributeValueAction;
use App\Application\Catalog\Actions\ProductAttribute\DeleteProductAttributeAction;
use App\Application\Catalog\Actions\ProductAttribute\DeleteProductAttributeValueAction;
use App\Application\Catalog\Actions\ProductAttribute\DeleteSelectedProductAttributesAction;
use App\Application\Catalog\Actions\ProductAttribute\UpdateProductAttributeAction;
use App\Application\Catalog\Actions\ProductAttribute\UpdateProductAttributeValueAction;
use App\Domain\Catalog\Models\ProductAttribute;
use App\Domain\Catalog\Models\ProductAttributeValue;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class ManageProductAttributes extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'admin';

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedIds = [];

    public string $bulkAction = '';

    public ?int $editingId = null;

    public string $title = '';

    public ?int $highlightedId = null;

    public bool $valuesModalOpen = false;

    public ?int $valuesAttributeId = null;

    public string $valuesAttributeTitle = '';

    public string $valueTitle = '';

    public ?int $editingValueId = null;

    public ?int $highlightedValueId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'performDeleteAttribute' => 'performDeleteAttribute',
        'performDeleteAttributeValue' => 'performDeleteAttributeValue',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_attributes', 'title')
                    ->whereNull('deleted_at')
                    ->ignore($this->editingId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'title.required' => 'عنوان ویژگی الزامی است.',
            'title.max' => 'عنوان ویژگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'title.unique' => 'ویژگی دیگری با این عنوان وجود دارد.',

            'valueTitle.required' => 'عنوان مقدار الزامی است.',
            'valueTitle.max' => 'عنوان مقدار نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'valueTitle.unique' => 'برای این ویژگی، مقدار دیگری با این عنوان وجود دارد.',
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

        $this->dispatch('product-attributes-scroll-to-list');
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        Gate::authorize('product.attribute');

        $data = $this->validate();

        if ($this->isEditing) {
            $this->updateAttribute($data);

            return;
        }

        $this->createAttribute($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     *
     * @throws Throwable
     */
    protected function createAttribute(array $data): void
    {
        $attribute = app(CreateProductAttributeAction::class)->execute($data);

        $this->highlightedId = $attribute->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'ویژگی محصول با موفقیت ایجاد شد.');
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     *
     * @throws Throwable
     */
    protected function updateAttribute(array $data): void
    {
        abort_unless($this->editingId, 404);

        $attribute = ProductAttribute::query()->findOrFail($this->editingId);

        $attribute = app(UpdateProductAttributeAction::class)->execute($attribute, $data);

        $this->highlightedId = $attribute->id;

        $this->resetForm();

        session()->flash('success', 'ویژگی محصول با موفقیت به‌روزرسانی شد.');
    }

    public function startEditing(int $id): void
    {
        Gate::authorize('product.attribute');

        $attribute = ProductAttribute::query()->findOrFail($id);

        $this->editingId = $attribute->id;
        $this->title = $attribute->title;

        $this->resetValidation();

        $this->dispatch('product-attribute-edit-started');
        $this->dispatch('admin-inline-edit-started');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();

        $this->dispatch('product-attributes-scroll-to-list');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';

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

        $this->dispatch('product-attributes-scroll-to-list');
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
        Gate::authorize('product.attribute');

        if ($this->bulkAction === '' || empty($this->selectedIds)) {
            return;
        }

        if ($this->bulkAction !== 'delete') {
            return;
        }

        $selectedIds = $this->normalizedSelectedIds();
        $selectedBulkAction = $this->bulkAction;

        try {
            $deletedCount = app(DeleteSelectedProductAttributesAction::class)->execute($selectedIds);
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

        session()->flash('success', "{$deletedCount} ویژگی محصول با موفقیت حذف شد.");
    }

    /**
     * @throws Throwable
     */
    public function performDeleteAttribute($id = null): void
    {
        Gate::authorize('product.attribute');

        if (is_array($id)) {
            $id = $id['id'] ?? null;
        }

        $id = (int)$id;

        if (!$id) {
            return;
        }

        $attribute = ProductAttribute::query()->findOrFail($id);
        $attributeId = $attribute->id;

        try {
            app(DeleteProductAttributeAction::class)->execute($attribute);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            session()->flash('error', $exception->validator->errors()->first());

            return;
        }

        if ($this->editingId === $attributeId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$attributeId]));

        session()->flash('success', 'ویژگی محصول با موفقیت حذف شد.');
    }

    public function openValuesModal(int $attributeId): void
    {
        Gate::authorize('product.attribute');

        $attribute = ProductAttribute::query()->findOrFail($attributeId);

        $this->valuesAttributeId = $attribute->id;
        $this->valuesAttributeTitle = $attribute->title;
        $this->valuesModalOpen = true;

        $this->resetValueForm();
    }

    public function closeValuesModal(): void
    {
        $this->valuesModalOpen = false;
        $this->valuesAttributeId = null;
        $this->valuesAttributeTitle = '';

        $this->resetValueForm();
    }

    /**
     * @throws Throwable
     */
    public function saveValue(): void
    {
        Gate::authorize('product.attribute');

        abort_unless($this->valuesAttributeId, 404);

        $data = $this->validate($this->valueRules());

        if ($this->editingValueId) {
            $this->updateValue($data);

            return;
        }

        $this->createValue($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     *
     * @throws Throwable
     */
    protected function createValue(array $data): void
    {
        $attribute = $this->currentValuesAttribute();

        $value = app(CreateProductAttributeValueAction::class)->execute($attribute, [
            'title' => $data['valueTitle'],
        ]);

        $this->highlightedValueId = $value->id;

        $this->resetValueForm();

        session()->flash('success', 'مقدار ویژگی با موفقیت ایجاد شد.');
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     *
     * @throws Throwable
     */
    protected function updateValue(array $data): void
    {
        $attribute = $this->currentValuesAttribute();

        $value = ProductAttributeValue::query()
            ->where('product_attribute_id', $attribute->id)
            ->findOrFail($this->editingValueId);

        $value = app(UpdateProductAttributeValueAction::class)->execute($attribute, $value, [
            'title' => $data['valueTitle'],
        ]);

        $this->highlightedValueId = $value->id;

        $this->resetValueForm();

        session()->flash('success', 'مقدار ویژگی با موفقیت به‌روزرسانی شد.');
    }

    public function startEditingValue(int $valueId): void
    {
        Gate::authorize('product.attribute');

        $attribute = $this->currentValuesAttribute();

        $value = ProductAttributeValue::query()
            ->where('product_attribute_id', $attribute->id)
            ->findOrFail($valueId);

        $this->editingValueId = $value->id;
        $this->valueTitle = $value->title;

        $this->resetValidation();

        $this->dispatch('product-attribute-value-edit-started');
    }

    public function cancelEditingValue(): void
    {
        $this->resetValueForm();
    }

    /**
     * @throws Throwable
     */
    public function performDeleteAttributeValue($id = null): void
    {
        Gate::authorize('product.attribute');

        if (is_array($id)) {
            $id = $id['id'] ?? null;
        }

        $id = (int)$id;

        if (!$id || !$this->valuesAttributeId) {
            return;
        }

        $attribute = $this->currentValuesAttribute();

        $value = ProductAttributeValue::query()
            ->where('product_attribute_id', $attribute->id)
            ->findOrFail($id);

        $valueId = $value->id;

        try {
            app(DeleteProductAttributeValueAction::class)->execute($attribute, $value);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            session()->flash('error', $exception->validator->errors()->first());

            return;
        }

        if ($this->editingValueId === $valueId) {
            $this->resetValueForm();
        }

        session()->flash('success', 'مقدار ویژگی با موفقیت حذف شد.');
    }

    public function resetValueForm(): void
    {
        $this->editingValueId = null;
        $this->valueTitle = '';

        $this->resetValidation(['valueTitle']);
    }

    public function nextPage($pageName = 'page'): void
    {
        $this->setPage(($this->getPage($pageName) ?? 1) + 1, $pageName);
        $this->dispatch('product-attributes-scroll-to-list');
    }

    public function previousPage($pageName = 'page'): void
    {
        $currentPage = (int)($this->getPage($pageName) ?? 1);

        if ($currentPage > 1) {
            $this->setPage($currentPage - 1, $pageName);
        }

        $this->dispatch('product-attributes-scroll-to-list');
    }

    public function gotoPage($page, $pageName = 'page'): void
    {
        $this->setPage($page, $pageName);
        $this->dispatch('product-attributes-scroll-to-list');
    }

    /**
     * @return array<string, mixed>
     */
    protected function valueRules(): array
    {
        return [
            'valueTitle' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_attribute_values', 'title')
                    ->where('product_attribute_id', $this->valuesAttributeId)
                    ->whereNull('deleted_at')
                    ->ignore($this->editingValueId),
            ],
        ];
    }

    protected function currentValuesAttribute(): ProductAttribute
    {
        abort_unless($this->valuesAttributeId, 404);

        return ProductAttribute::query()->findOrFail($this->valuesAttributeId);
    }

    protected function getAttributesQuery(): Builder
    {
        return ProductAttribute::query()
            ->withCount(['values', 'assignments'])
            ->when($this->search !== '', function (Builder $query): void {
                $query->where('title', 'like', '%' . trim($this->search) . '%');
            });
    }

    /**
     * @return array<string, string>
     */
    protected function sortableColumns(): array
    {
        return [
            'title' => 'title',
            'values_count' => 'values_count',
            'assignments_count' => 'assignments_count',
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

    /**
     * @return array<int, int>
     */
    protected function normalizedSelectedIds(): array
    {
        return collect($this->selectedIds)
            ->map(fn($id) => (int)$id)
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function values(): Collection
    {
        if (!$this->valuesAttributeId) {
            return collect();
        }

        return ProductAttributeValue::query()
            ->where('product_attribute_id', $this->valuesAttributeId)
            ->latest('id')
            ->get();
    }

    public function render(): View
    {
        $items = $this->getAttributesQuery()
            ->orderBy($this->safeSortBy(), $this->safeSortDirection())
            ->paginate(20);

        return view('livewire.admin.catalog.manage-product-attributes', [
            'items' => $items,
            'values' => $this->values(),
            'bulkActionOptions' => $this->bulkActionOptions,
        ]);
    }
}
