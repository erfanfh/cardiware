<?php

namespace App\Livewire\Admin\Taxonomy\Base;

use App\Application\Taxonomy\Actions\CreateTaxonomyTermAction;
use App\Application\Taxonomy\Actions\DeleteTaxonomyTermAction;
use App\Application\Taxonomy\Actions\HandleTaxonomyBulkAction;
use App\Application\Taxonomy\Actions\UpdateTaxonomyTermAction;
use App\Application\Taxonomy\Support\CategoryBulkActions;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Throwable;

abstract class ManageHierarchicalTaxonomyCategories extends ManageTaxonomyTerms
{
    public string $parentId = '';

    abstract protected function taxonomyKey(): TaxonomyKeyEnum;

    abstract protected function permissionName(): string;

    abstract protected function countRelationName(): string;

    abstract protected function viewName(): string;

    abstract protected function successEntityName(): string;

    abstract protected function frontRouteName(): string;

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

                    $parent = $this->taxonomyTermsBaseQuery()->find($parentId);

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
        Gate::authorize($this->permissionName());

        $term = app(CreateTaxonomyTermAction::class)->execute(
            $this->taxonomyKey(),
            $this->validatedCategoryData()
        );

        $this->highlightedId = $term->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', $this->successEntityName() . ' با موفقیت ایجاد شد.');
    }

    public function startEditing(int $id): void
    {
        $term = $this->taxonomyTermsBaseQuery()->findOrFail($id);

        $this->editingId = $term->id;
        $this->title = $term->title;
        $this->slug = $term->slug ?? '';
        $this->description = $term->description ?? '';
        $this->parentId = $term->parent_id ? (string)$term->parent_id : '';

        $this->resetValidation();

        $this->dispatch('taxonomy-edit-started');
        $this->dispatch('admin-inline-edit-started');
    }

    public function updateTerm(): void
    {
        Gate::authorize($this->permissionName());

        abort_unless($this->editingId, 404);

        $term = $this->taxonomyTermsBaseQuery()->findOrFail($this->editingId);

        $term = app(UpdateTaxonomyTermAction::class)->execute(
            $term,
            $this->taxonomyKey(),
            $this->validatedCategoryData()
        );

        $updatedId = $term->id;

        $this->resetForm();
        $this->highlightedId = $updatedId;

        session()->flash('success', $this->successEntityName() . ' با موفقیت به‌روزرسانی شد.');
    }

    public function deleteTerm(): void
    {
        Gate::authorize($this->permissionName());

        abort_unless($this->deletingId, 404);

        $term = $this->taxonomyTermsBaseQuery()->findOrFail($this->deletingId);
        $termId = $term->id;

        app(DeleteTaxonomyTermAction::class)->execute($term);

        if ($this->editingId === $termId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$termId]));
        $this->deletingId = null;

        session()->flash('success', $this->successEntityName() . ' با موفقیت حذف شد.');

        $this->dispatch('taxonomy-list-updating-stop');
    }

    protected function getTermsQuery(): Builder
    {
        return $this->taxonomyTermListing->handle(
            $this->taxonomyTermsBaseQuery()
                ->with('parent')
                ->withCount($this->countRelationName()),
            [
                'search' => $this->search,
            ]
        );
    }

    protected function handleBulkAction(array $selectedIds): mixed
    {
        Gate::authorize($this->permissionName());

        return app(HandleTaxonomyBulkAction::class)->execute(
            taxonomy: $this->taxonomyKey(),
            action: $this->bulkAction,
            ids: $selectedIds,
        );
    }

    protected function bulkActionSuccessMessage(mixed $result): string
    {
        return app(HandleTaxonomyBulkAction::class)->successMessage(
            $this->taxonomyKey(),
            $result
        );
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
        $categories = $this->taxonomyTermsBaseQuery()
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

            $currentParentId = $this->taxonomyTermsBaseQuery()
                ->whereKey($currentParentId)
                ->value('parent_id');

            if ($currentParentId !== null) {
                $currentParentId = (int)$currentParentId;
            }
        }

        return false;
    }

    protected function isSearching(): bool
    {
        return filled(trim($this->search));
    }

    /**
     * @return array<int, int>
     */
    protected function getVisibleTermIds(): array
    {
        return collect($this->paginatedItems()->items())
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }

    protected function paginatedItems(): LengthAwarePaginator
    {
        if ($this->isSearching()) {
            return $this->paginatedSearchItems();
        }

        return $this->paginatedTreeItems();
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

    protected function paginatedSearchItems(): LengthAwarePaginator
    {
        $query = $this->getTermsQuery();

        $this->applyFlatSorting($query);

        $items = $query->paginate(
            perPage: $this->taxonomyTermListing->perPage(),
            pageName: 'page',
        );

        $pathMap = $this->categoryPathMap();

        $items->getCollection()->transform(function (TaxonomyTerm $term) use ($pathMap) {
            $term->depth = 0;
            $term->tree_label = $pathMap[(int)$term->id] ?? $term->title;

            return $term;
        });

        return $items;
    }

    protected function applyFlatSorting(Builder $query): void
    {
        $countColumn = $this->countColumnName();

        $allowedSorts = [
            'title',
            'slug',
            'created_at',
            $countColumn,
        ];

        $sortBy = in_array($this->sortBy, $allowedSorts, true)
            ? $this->sortBy
            : 'title';

        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $query->reorder()->orderBy($sortBy, $sortDirection);
    }

    protected function flattenCategoriesTree(): Collection
    {
        $categories = $this->getTermsQuery()
            ->reorder()
            ->get();

        $grouped = $categories->groupBy(function (TaxonomyTerm $term) {
            return $term->parent_id ?? 0;
        });

        $rootItems = $this->sortRootCategories(
            $grouped->get(0, collect())
        );

        return $this->flattenTreeBranch(
            grouped: $grouped,
            siblings: $rootItems,
            depth: 0,
            trail: [],
        );
    }

    protected function flattenTreeBranch(
        Collection $grouped,
        Collection $siblings,
        int        $depth,
        array      $trail
    ): Collection
    {
        $items = collect();

        /** @var TaxonomyTerm $child */
        foreach ($siblings as $child) {
            $currentTrail = [...$trail, $child->title];

            $child->depth = $depth;
            $child->tree_label = implode(' / ', $currentTrail);

            $items->push($child);

            $children = $this->sortChildCategories(
                $grouped->get((int)$child->id, collect())
            );

            $items = $items->concat(
                $this->flattenTreeBranch(
                    grouped: $grouped,
                    siblings: $children,
                    depth: $depth + 1,
                    trail: $currentTrail,
                )
            );
        }

        return $items;
    }

    protected function sortRootCategories(Collection $items): Collection
    {
        if ($items->isEmpty()) {
            return $items;
        }

        return $items->sortBy(
            fn(TaxonomyTerm $term) => $this->resolveRootSortValue($term),
            options: SORT_NATURAL | SORT_FLAG_CASE,
            descending: $this->sortDirection === 'desc',
        )->values();
    }

    protected function sortChildCategories(Collection $items): Collection
    {
        if ($items->isEmpty()) {
            return $items;
        }

        return $items->sortBy(
            fn(TaxonomyTerm $term) => mb_strtolower($term->title),
            options: SORT_NATURAL | SORT_FLAG_CASE,
        )->values();
    }

    protected function resolveRootSortValue(TaxonomyTerm $term): mixed
    {
        return match ($this->sortBy) {
            'title' => mb_strtolower($term->title),
            'slug' => mb_strtolower($term->slug ?? ''),
            'created_at' => $term->created_at?->timestamp ?? 0,
            $this->countColumnName() => (int)($term->{$this->countColumnName()} ?? 0),
            default => mb_strtolower($term->title),
        };
    }

    /**
     * @return array<int, string>
     */
    protected function categoryPathMap(): array
    {
        $categories = $this->taxonomyTermsBaseQuery()
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'parent_id',
            ]);

        /** @var array<int, TaxonomyTerm> $indexed */
        $indexed = $categories
            ->keyBy(fn(TaxonomyTerm $term) => (int)$term->id)
            ->all();

        $paths = [];

        foreach ($categories as $category) {
            $paths[(int)$category->id] = $this->buildCategoryPathLabel(
                category: $category,
                indexed: $indexed,
            );
        }

        return $paths;
    }

    /**
     * @param array<int, TaxonomyTerm> $indexed
     */
    protected function buildCategoryPathLabel(TaxonomyTerm $category, array $indexed): string
    {
        $titles = [$category->title];
        $parentId = $category->parent_id ? (int)$category->parent_id : null;

        while ($parentId !== null) {
            $parent = $indexed[$parentId] ?? null;

            if (!$parent) {
                break;
            }

            array_unshift($titles, $parent->title);

            $parentId = $parent->parent_id ? (int)$parent->parent_id : null;
        }

        return implode(' / ', $titles);
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

    protected function taxonomyTermsBaseQuery(): Builder
    {
        return TaxonomyTerm::query()->forTaxonomy($this->taxonomyKey());
    }

    protected function countColumnName(): string
    {
        return $this->countRelationName() . '_count';
    }

    public function render(): View
    {
        return view($this->viewName(), [
            'items' => $this->paginatedItems(),
            'taxonomyLabel' => $this->taxonomyLabel(),
            'taxonomyLabelPlural' => $this->taxonomyLabelPlural(),
            'bulkActionOptions' => CategoryBulkActions::set()->toOptions(),
            'parentOptions' => ['' => 'بدون والد'] + $this->parentOptions(),
            'countColumn' => $this->countColumnName(),
            'frontRouteName' => $this->frontRouteName(),
        ]);
    }
}
