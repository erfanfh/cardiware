<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\CreateCategoryAction;
use App\Application\Taxonomy\Actions\HandleCategoryBulkAction;
use App\Application\Taxonomy\Actions\UpdateCategoryAction;
use App\Application\Taxonomy\Support\CategoryBulkActions;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Livewire\Admin\Taxonomy\Base\ManageTaxonomyTerms;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Throwable;

class ManageCategories extends ManageTaxonomyTerms
{
    public string $parentId = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'parentId' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $parentId = $this->normalizeParentId($value);

                    if ($parentId === null) {
                        return;
                    }

                    $parent = TaxonomyTerm::categories()->find($parentId);

                    if (!$parent) {
                        $fail('دسته‌بندی والد معتبر نیست.');
                        return;
                    }

                    if ($this->editingId !== null && $parentId === $this->editingId) {
                        $fail('دسته‌بندی نمی‌تواند والد خودش باشد.');
                        return;
                    }

                    if (
                        $this->editingId !== null &&
                        $this->createsCircularHierarchy(
                            categoryId: $this->editingId,
                            parentId: $parentId,
                        )
                    ) {
                        $fail('انتخاب این والد باعث ایجاد چرخه در سلسله‌مراتب دسته‌بندی‌ها می‌شود.');
                    }
                },
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    protected function createTerm(): void
    {
        $term = app(CreateCategoryAction::class)->execute($this->validatedCategoryData());

        $this->highlightedId = $term->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function startEditing(int $id): void
    {
        $term = TaxonomyTerm::categories()->findOrFail($id);

        $this->editingId = $term->id;
        $this->title = $term->title;
        $this->slug = $term->slug ?? '';
        $this->description = $term->description ?? '';
        $this->parentId = $term->parent_id ? (string)$term->parent_id : '';

        $this->resetValidation();

        $this->dispatch('taxonomy-edit-started');
    }

    public function updateTerm(): void
    {
        abort_unless($this->editingId, 404);

        $term = TaxonomyTerm::categories()->findOrFail($this->editingId);
        $term = app(UpdateCategoryAction::class)->execute($term, $this->validatedCategoryData());

        $updatedId = $term->id;

        $this->resetForm();
        $this->highlightedId = $updatedId;

        session()->flash('success', 'دسته‌بندی با موفقیت به‌روزرسانی شد.');
    }

    public function deleteTerm(): void
    {
        abort_unless($this->deletingId, 404);

        $term = TaxonomyTerm::categories()->findOrFail($this->deletingId);
        $termId = $term->id;

        $term->delete();

        if ($this->editingId === $termId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$termId]));
        $this->deletingId = null;

        session()->flash('success', 'دسته‌بندی با موفقیت حذف شد.');

        $this->dispatch('taxonomy-list-updating-stop');
    }

    protected function getTermsQuery(): Builder
    {
        return $this->taxonomyTermListing->handle(
            TaxonomyTerm::categories()->with('parent')->withCount('posts'),
            [
                'search' => $this->search,
            ]
        );
    }

    protected function handleBulkAction(array $selectedIds): mixed
    {
        return app(HandleCategoryBulkAction::class)->execute(
            action: $this->bulkAction,
            ids: $selectedIds,
        );
    }

    protected function bulkActionSuccessMessage(mixed $result): string
    {
        return app(HandleCategoryBulkAction::class)->successMessage($result);
    }

    protected function taxonomyLabel(): string
    {
        return 'دسته‌بندی';
    }

    protected function taxonomyLabelPlural(): string
    {
        return 'دسته‌بندی‌ها';
    }

    protected function taxonomyTypeValue(): string|int
    {
        return TaxonomyTermTypeEnum::CATEGORY->value;
    }

    protected function resetExtraFormFields(): void
    {
        $this->parentId = '';
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedCategoryData(): array
    {
        $data = $this->validate();

        $data['parent_id'] = $this->normalizeParentId($data['parentId'] ?? null);
        unset($data['parentId']);

        return $data;
    }

    protected function parentOptions(): array
    {
        $categories = TaxonomyTerm::categories()
            ->when(
                $this->editingId !== null,
                fn(Builder $query) => $query->whereKeyNot($this->editingId)
            )
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'parent_id',
            ]);

        $grouped = $categories->groupBy(function (TaxonomyTerm $term) {
            return $term->parent_id ?? 0;
        });

        return $this->flattenParentOptions(
            grouped: $grouped,
            parentId: null,
            depth: 0,
        );
    }

    protected function normalizeParentId(mixed $value = null): ?int
    {
        $value = $value ?? $this->parentId;

        if ($value === null || $value === '') {
            return null;
        }

        return (int)$value;
    }

    protected function createsCircularHierarchy(int $categoryId, int $parentId): bool
    {
        $currentParentId = $parentId;

        while ($currentParentId !== null) {
            if ($currentParentId === $categoryId) {
                return true;
            }

            $currentParentId = TaxonomyTerm::categories()
                ->whereKey($currentParentId)
                ->value('parent_id');

            if ($currentParentId !== null) {
                $currentParentId = (int)$currentParentId;
            }
        }

        return false;
    }

    /**
     * @return array<int, int>
     */
    protected function getVisibleTermIds(): array
    {
        return collect($this->paginatedTreeItems()->items())
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }

    protected function paginatedTreeItems(): LengthAwarePaginator
    {
        $flatItems = $this->flattenCategoriesTree();

        $perPage = $this->taxonomyTermListing->perPage();
        $currentPage = $this->getPage();

        $pageItems = $flatItems
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        return new LengthAwarePaginator(
            items: $pageItems,
            total: $flatItems->count(),
            perPage: $perPage,
            currentPage: $currentPage,
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'page',
            ]
        );
    }

    protected function flattenCategoriesTree(): Collection
    {
        $categories = $this->getTermsQuery()
            ->orderBy('title')
            ->get();

        $grouped = $categories->groupBy(function (TaxonomyTerm $term) {
            return $term->parent_id ?? 0;
        });

        return $this->flattenTreeBranch(
            grouped: $grouped,
            parentId: null,
            depth: 0,
            trail: [],
        );
    }

    protected function flattenTreeBranch(
        Collection $grouped,
        ?int       $parentId,
        int        $depth,
        array      $trail
    ): Collection
    {
        $items = collect();

        /** @var Collection<int, TaxonomyTerm> $children */
        $children = $grouped->get($parentId ?? 0, collect());

        foreach ($children as $child) {
            $currentTrail = [...$trail, $child->title];

            $child->depth = $depth;
            $child->tree_label = implode(' / ', $currentTrail);

            $items->push($child);

            $items = $items->concat(
                $this->flattenTreeBranch(
                    grouped: $grouped,
                    parentId: (int)$child->id,
                    depth: $depth + 1,
                    trail: $currentTrail,
                )
            );
        }

        return $items;
    }

    /**
     * @param Collection<int|string, Collection<int, TaxonomyTerm>> $grouped
     * @return array<string, string>
     */
    protected function flattenParentOptions(Collection $grouped, ?int $parentId, int $depth): array
    {
        $options = [];

        /** @var Collection<int, TaxonomyTerm> $children */
        $children = $grouped->get($parentId ?? 0, collect());

        foreach ($children as $child) {
            $options[(string)$child->id] = $this->formatParentOptionLabel(
                title: $child->title,
                depth: $depth,
            );

            $options += $this->flattenParentOptions(
                grouped: $grouped,
                parentId: (int)$child->id,
                depth: $depth + 1,
            );
        }

        return $options;
    }

    protected function formatParentOptionLabel(string $title, int $depth): string
    {
        if ($depth === 0) {
            return $title;
        }

        return str_repeat('— ', $depth) . $title;
    }

    public function render(): View
    {
        $terms = $this->paginatedTreeItems();

        return view('livewire.admin.taxonomy.manage-categories', [
            'items' => $terms,
            'taxonomyLabel' => $this->taxonomyLabel(),
            'taxonomyLabelPlural' => $this->taxonomyLabelPlural(),
            'bulkActionOptions' => CategoryBulkActions::set()->toOptions(),
            'parentOptions' => ['' => 'بدون والد'] + $this->parentOptions(),
        ]);
    }
}
